<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\SosAlert;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Volunteer Dashboard Controller
 *
 * Handles the volunteer dashboard for viewing and responding to SOS alerts.
 *
 * @package App\Http\Controllers
 */
class VolunteerDashboardController extends Controller
{
    /**
     * Display nearby unresolved SOS alerts for volunteers.
     *
     * Uses the Haversine formula to calculate distance between volunteer's
     * coordinates and alert locations, filtering by specified radius.
     *
     * The Haversine formula calculates the great-circle distance between
     * two points on a sphere given their longitudes and latitudes:
     * 
     * d = 2r * arcsin(sqrt(sin²((lat2-lat1)/2) + cos(lat1)*cos(lat2)*sin²((lng2-lng1)/2)))
     * 
     * Where:
     * - r = Earth's radius in kilometers (6371 km)
     * - lat1, lng1 = Volunteer's coordinates
     * - lat2, lng2 = Alert's coordinates
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request): View
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Ensure user is a volunteer
        if (!$user->is_volunteer) {
            abort(403, 'Access denied. Only volunteers can view this dashboard.');
        }

        // Validate and get parameters
        $validated = $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'radius_km' => 'nullable|numeric|min:1|max:100',
        ]);

        $volunteerLat = $validated['lat'];
        $volunteerLng = $validated['lng'];
        $radiusKm = $validated['radius_km'] ?? 5; // Default 5 km

        // Query unresolved alerts within radius using Haversine formula
        // Earth's radius in kilometers
        $earthRadiusKm = 6371;

        // Get all unresolved alerts and calculate distance in PHP
        // Note: For production with MySQL, this can be done in SQL for better performance
        // For SQLite (used in testing), we calculate in PHP since SQLite lacks trig functions
        $allAlerts = SosAlert::whereNull('resolved_at')
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate distance using Haversine formula in PHP
        $alertsWithDistance = $allAlerts->map(function ($alert) use ($volunteerLat, $volunteerLng, $earthRadiusKm) {
            // Haversine formula
            $latFrom = deg2rad((float) $volunteerLat);
            $lonFrom = deg2rad((float) $volunteerLng);
            $latTo = deg2rad((float) $alert->latitude);
            $lonTo = deg2rad((float) $alert->longitude);

            $latDelta = $latTo - $latFrom;
            $lonDelta = $lonTo - $lonFrom;

            $a = sin($latDelta / 2) ** 2 + cos($latFrom) * cos($latTo) * sin($lonDelta / 2) ** 2;
            $c = 2 * asin(sqrt($a));

            $distance = $earthRadiusKm * $c;

            $alert->distance_km = round($distance, 2);
            return $alert;
        })
        ->filter(function ($alert) use ($radiusKm) {
            // Filter by radius
            return $alert->distance_km <= $radiusKm;
        })
        ->sortBy('distance_km') // Sort by distance (closest first)
        ->values(); // Reset array keys

        // Manually paginate the collection
        $currentPage = request()->get('page', 1);
        $perPage = 15;
        $offset = ($currentPage - 1) * $perPage;

        $alerts = new \Illuminate\Pagination\LengthAwarePaginator(
            $alertsWithDistance->slice($offset, $perPage),
            $alertsWithDistance->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('volunteer.dashboard', [
            'alerts' => $alerts,
            'volunteerLat' => $volunteerLat,
            'volunteerLng' => $volunteerLng,
            'radiusKm' => $radiusKm,
        ]);
    }
}
