<?php

namespace App\Http\Controllers;

use App\Events\TripLocationUpdated;
use App\Models\DeviceMapping;
use App\Models\Trip;
use App\Models\TripLocation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * TraccarWebhookController
 * 
 * Receives webhook notifications from Traccar server when device positions are updated.
 * Processes position data and broadcasts to connected clients via Laravel Echo.
 */
class TraccarWebhookController extends Controller
{
    /**
     * Handle incoming position update from Traccar webhook.
     * 
     * Traccar webhook URL: https://yourdomain.com/api/traccar/webhook
     * Configure in Traccar: Server Settings → Notifications → Webhook
     * 
     * Expected payload format:
     * {
     *   "event": {
     *     "type": "deviceMoving" or "deviceStopped"
     *   },
     *   "position": {
     *     "deviceId": 123,
     *     "latitude": 40.7128,
     *     "longitude": -74.0060,
     *     "accuracy": 5.0,
     *     "speed": 25.5,
     *     "altitude": 10.0,
     *     "course": 180.0,
     *     "fixTime": "2025-01-23T12:00:00.000Z"
     *   },
     *   "device": {
     *     "id": 123,
     *     "name": "Device Name"
     *   }
     * }
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function handlePositionUpdate(Request $request): JsonResponse
    {
        // Validate webhook token for security
        $webhookToken = config('traccar.webhook_token');
        $providedToken = $request->header('X-Webhook-Token') ?? $request->input('token');

        if ($webhookToken && $providedToken !== $webhookToken) {
            Log::warning('Traccar webhook: Invalid token', [
                'ip' => $request->ip(),
                'provided_token' => $providedToken,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        // Extract position data from webhook payload
        $position = $request->input('position');
        $device = $request->input('device');

        if (!$position || !$device) {
            Log::warning('Traccar webhook: Missing position or device data', [
                'payload' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Invalid payload: missing position or device data',
            ], 400);
        }

        $deviceId = $position['deviceId'] ?? $device['id'];

        // Find active trip for this device
        $trip = Trip::where('traccar_device_id', $deviceId)
            ->where('status', 'in_progress')
            ->orWhere('status', 'ongoing')
            ->first();

        if (!$trip) {
            // No active trip for this device, ignore silently
            Log::debug('Traccar webhook: No active trip for device', [
                'device_id' => $deviceId,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'No active trip for this device',
            ], 200);
        }

        // Create trip location record
        $tripLocation = $this->createTripLocation($trip, $position);

        // Update trip's current location
        $trip->update([
            'current_lat' => $position['latitude'],
            'current_lng' => $position['longitude'],
            'last_location_update_at' => now(),
        ]);

        // Broadcast position update via Laravel Echo
        broadcast(new TripLocationUpdated($trip))->toOthers();

        Log::info('Traccar webhook: Position update processed', [
            'trip_id' => $trip->id,
            'device_id' => $deviceId,
            'latitude' => $position['latitude'],
            'longitude' => $position['longitude'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Position update processed',
            'trip_id' => $trip->id,
            'location_id' => $tripLocation->id,
        ], 200);
    }

    /**
     * Create a TripLocation record from Traccar position data.
     * 
     * @param Trip $trip
     * @param array $position
     * @return TripLocation
     */
    protected function createTripLocation(Trip $trip, array $position): TripLocation
    {
        $recordedAt = isset($position['fixTime']) 
            ? \Carbon\Carbon::parse($position['fixTime'])
            : now();

        return TripLocation::create([
            'trip_id' => $trip->id,
            'latitude' => $position['latitude'],
            'longitude' => $position['longitude'],
            'accuracy' => $position['accuracy'] ?? null,
            'speed' => $position['speed'] ?? null,
            'altitude' => $position['altitude'] ?? null,
            'bearing' => $position['course'] ?? null,
            'recorded_at' => $recordedAt,
        ]);
    }

    /**
     * Health check endpoint for Traccar webhook.
     * 
     * @return JsonResponse
     */
    public function healthCheck(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'service' => 'SafeRide Traccar Webhook',
            'timestamp' => now()->toIso8601String(),
        ], 200);
    }
}
