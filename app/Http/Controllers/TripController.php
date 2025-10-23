<?php

namespace App\Http\Controllers;

use App\Events\TripLocationUpdated;
use App\Models\DeviceMapping;
use App\Models\RouteAlert;
use App\Models\SosAlert;
use App\Models\Trip;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * TripController
 *
 * Handles API operations for trip management and location tracking.
 */
class TripController extends Controller
{
    /**
     * Display trips index page
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('trips.index');
    }

    /**
     * Display a specific trip (active trip tracking)
     *
     * @param  \App\Models\Trip  $trip
     * @return \Illuminate\View\View
     */
    public function show(Trip $trip)
    {
        // Ensure user owns the trip or is a trusted contact
        if ($trip->user_id !== Auth::id()) {
            abort(403, 'Unauthorized to view this trip');
        }

        // Load relationships including locations for map route
        $trip->load([
            'sosAlerts', 
            'routeAlerts',
            'locations' => function($query) {
                $query->orderBy('recorded_at', 'asc');
            }
        ]);

        return view('trips.show', compact('trip'));
    }

    /**
     * Display trip history
     *
     * @return \Illuminate\View\View
     */
    public function history()
    {
        $trips = Trip::where('user_id', Auth::id())
            ->with(['sosAlerts', 'routeAlerts'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('trips.history', compact('trips'));
    }

    /**
     * Start a new trip.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function startTrip(Request $request): JsonResponse
    {
        // Validate origin and destination coordinates
        $validated = $request->validate([
            'origin_lat' => 'required|numeric|between:-90,90',
            'origin_lng' => 'required|numeric|between:-180,180',
            'destination_lat' => 'required|numeric|between:-90,90',
            'destination_lng' => 'required|numeric|between:-180,180',
            'destination_address' => 'nullable|string|max:255',
        ]);

        // Get user's active device mapping
        $activeDevice = DeviceMapping::getActiveForUser(Auth::id());

        // Create trip with authenticated user
        $trip = Trip::create([
            'user_id' => Auth::id(),
            'origin_lat' => $validated['origin_lat'],
            'origin_lng' => $validated['origin_lng'],
            'destination_lat' => $validated['destination_lat'],
            'destination_lng' => $validated['destination_lng'],
            'destination_address' => $validated['destination_address'] ?? null,
            'current_lat' => $validated['origin_lat'], // Start at origin
            'current_lng' => $validated['origin_lng'],
            'share_uuid' => (string) Str::uuid(),
            'status' => 'ongoing',
            'started_at' => now(),
            'traccar_device_id' => $activeDevice?->traccar_device_id, // Auto-assign device
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Trip started successfully',
            'trip' => $trip,
        ], 201);
    }

    /**
     * Update current location of a trip.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Trip  $trip
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateLocation(Request $request, Trip $trip): JsonResponse
    {
        // Ensure user owns the trip
        if ($trip->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // Validate location coordinates
        $validated = $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
        ]);

        $newLat = (float) $validated['lat'];
        $newLng = (float) $validated['lng'];
        $previousLat = (float) $trip->current_lat;
        $previousLng = (float) $trip->current_lng;

        // Detect stoppage anomaly
        $this->detectStoppage($trip, $newLat, $newLng, $previousLat, $previousLng);

        // Detect route deviation anomaly
        $this->detectDeviation($trip, $newLat, $newLng);

        // Update current location and timestamp
        $trip->update([
            'current_lat' => $newLat,
            'current_lng' => $newLng,
            'last_location_update_at' => now(),
        ]);

        // Broadcast location update event
        broadcast(new TripLocationUpdated($trip))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Location updated successfully',
            'trip' => $trip->fresh(),
        ]);
    }

    /**
     * Detect stoppage anomaly.
     *
     * Creates a RouteAlert if the trip hasn't moved more than the threshold
     * distance for more than the threshold time.
     *
     * @param  \App\Models\Trip  $trip
     * @param  float  $newLat
     * @param  float  $newLng
     * @param  float  $previousLat
     * @param  float  $previousLng
     * @return void
     */
    protected function detectStoppage(
        Trip $trip,
        float $newLat,
        float $newLng,
        float $previousLat,
        float $previousLng
    ): void {
        // Skip if this is the first location update
        if (!$trip->last_location_update_at) {
            return;
        }

        // Calculate distance moved from previous location (in meters)
        $distanceMoved = $this->calculateDistance(
            $previousLat,
            $previousLng,
            $newLat,
            $newLng
        ) * 1000; // Convert km to meters

        // Calculate time since last update (in minutes)
        $minutesSinceUpdate = $trip->last_location_update_at->diffInMinutes(now());

        $distanceThreshold = config('saferide.stoppage_distance_threshold_m', 20);
        $timeThreshold = config('saferide.stoppage_time_threshold_minutes', 10);

        // Check if stoppage conditions are met
        if ($distanceMoved <= $distanceThreshold && $minutesSinceUpdate >= $timeThreshold) {
            // Check if we already created a stoppage alert recently (within last 30 minutes)
            $recentStoppageAlert = $trip->routeAlerts()
                ->where('alert_type', RouteAlert::TYPE_STOPPAGE)
                ->where('created_at', '>=', now()->subMinutes(30))
                ->exists();

            if (!$recentStoppageAlert) {
                $this->createRouteAlert($trip, RouteAlert::TYPE_STOPPAGE, [
                    'distance_moved_m' => round($distanceMoved, 2),
                    'time_stopped_minutes' => $minutesSinceUpdate,
                    'location' => [
                        'lat' => $newLat,
                        'lng' => $newLng,
                    ],
                ]);
            }
        }
    }

    /**
     * Detect route deviation anomaly.
     *
     * Creates a RouteAlert if the current location is too far from the
     * straight-line path between origin and destination.
     *
     * @param  \App\Models\Trip  $trip
     * @param  float  $currentLat
     * @param  float  $currentLng
     * @return void
     */
    protected function detectDeviation(Trip $trip, float $currentLat, float $currentLng): void
    {
        $originLat = (float) $trip->origin_lat;
        $originLng = (float) $trip->origin_lng;
        $destLat = (float) $trip->destination_lat;
        $destLng = (float) $trip->destination_lng;

        // Calculate perpendicular distance from current location to the line
        // between origin and destination
        $deviationDistance = $this->calculatePerpendicularDistance(
            $currentLat,
            $currentLng,
            $originLat,
            $originLng,
            $destLat,
            $destLng
        );

        $deviationThreshold = config('saferide.deviation_threshold_km', 0.5);

        // Check if deviation exceeds threshold
        if ($deviationDistance > $deviationThreshold) {
            // Check if we already created a deviation alert recently (within last 5 minutes)
            $recentDeviationAlert = $trip->routeAlerts()
                ->where('alert_type', RouteAlert::TYPE_DEVIATION)
                ->where('created_at', '>=', now()->subMinutes(5))
                ->exists();

            if (!$recentDeviationAlert) {
                $this->createRouteAlert($trip, RouteAlert::TYPE_DEVIATION, [
                    'deviation_distance_km' => round($deviationDistance, 2),
                    'threshold_km' => $deviationThreshold,
                    'location' => [
                        'lat' => $currentLat,
                        'lng' => $currentLng,
                    ],
                ]);
            }
        }
    }

    /**
     * Create a route alert and optionally an SOS alert.
     *
     * @param  \App\Models\Trip  $trip
     * @param  string  $alertType
     * @param  array  $details
     * @return void
     */
    protected function createRouteAlert(Trip $trip, string $alertType, array $details): void
    {
        // Create the route alert
        $routeAlert = RouteAlert::create([
            'trip_id' => $trip->id,
            'alert_type' => $alertType,
            'details' => $details,
        ]);

        // Auto-create SOS alert if configured
        if (config('saferide.auto_create_sos_on_anomaly', false)) {
            $message = $alertType === RouteAlert::TYPE_STOPPAGE
                ? "Automatic alert: Trip stopped for {$details['time_stopped_minutes']} minutes"
                : "Automatic alert: Route deviation of {$details['deviation_distance_km']} km detected";

            SosAlert::create([
                'user_id' => $trip->user_id,
                'trip_id' => $trip->id,
                'latitude' => $details['location']['lat'],
                'longitude' => $details['location']['lng'],
                'message' => $message,
            ]);
        }
    }

    /**
     * Calculate distance between two coordinates using Haversine formula.
     *
     * @param  float  $lat1
     * @param  float  $lng1
     * @param  float  $lat2
     * @param  float  $lng2
     * @return float Distance in kilometers
     */
    protected function calculateDistance(
        float $lat1,
        float $lng1,
        float $lat2,
        float $lng2
    ): float {
        $earthRadius = 6371; // Earth's radius in kilometers

        $latFrom = deg2rad($lat1);
        $lngFrom = deg2rad($lng1);
        $latTo = deg2rad($lat2);
        $lngTo = deg2rad($lng2);

        $latDelta = $latTo - $latFrom;
        $lngDelta = $lngTo - $lngFrom;

        $a = sin($latDelta / 2) ** 2 +
            cos($latFrom) * cos($latTo) * sin($lngDelta / 2) ** 2;
        $c = 2 * asin(sqrt($a));

        return $earthRadius * $c;
    }

    /**
     * Calculate perpendicular distance from a point to a line segment.
     *
     * Uses the cross-track distance formula for great circles.
     *
     * @param  float  $pointLat
     * @param  float  $pointLng
     * @param  float  $line1Lat
     * @param  float  $line1Lng
     * @param  float  $line2Lat
     * @param  float  $line2Lng
     * @return float Distance in kilometers
     */
    protected function calculatePerpendicularDistance(
        float $pointLat,
        float $pointLng,
        float $line1Lat,
        float $line1Lng,
        float $line2Lat,
        float $line2Lng
    ): float {
        $earthRadius = 6371; // Earth's radius in kilometers

        // Convert to radians
        $pointLatRad = deg2rad($pointLat);
        $pointLngRad = deg2rad($pointLng);
        $line1LatRad = deg2rad($line1Lat);
        $line1LngRad = deg2rad($line1Lng);
        $line2LatRad = deg2rad($line2Lat);
        $line2LngRad = deg2rad($line2Lng);

        // Calculate distance from point to line1
        $d13 = acos(
            sin($line1LatRad) * sin($pointLatRad) +
            cos($line1LatRad) * cos($pointLatRad) * cos($pointLngRad - $line1LngRad)
        );

        // Calculate bearing from line1 to point
        $brng13 = atan2(
            sin($pointLngRad - $line1LngRad) * cos($pointLatRad),
            cos($line1LatRad) * sin($pointLatRad) -
            sin($line1LatRad) * cos($pointLatRad) * cos($pointLngRad - $line1LngRad)
        );

        // Calculate bearing from line1 to line2
        $brng12 = atan2(
            sin($line2LngRad - $line1LngRad) * cos($line2LatRad),
            cos($line1LatRad) * sin($line2LatRad) -
            sin($line1LatRad) * cos($line2LatRad) * cos($line2LngRad - $line1LngRad)
        );

        // Calculate cross-track distance
        $dxt = asin(sin($d13) * sin($brng13 - $brng12));

        return abs($earthRadius * $dxt);
    }

    /**
     * End a trip.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Trip  $trip
     * @return \Illuminate\Http\JsonResponse
     */
    public function endTrip(Request $request, Trip $trip): JsonResponse
    {
        // Ensure user owns the trip
        if ($trip->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // End the trip
        $trip->update([
            'status' => 'completed',
            'ended_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Trip ended successfully',
            'trip' => $trip->fresh(),
        ]);
    }
}
