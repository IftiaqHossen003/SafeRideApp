<?php

namespace App\Http\Controllers;

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
        ]);

        // Create trip with authenticated user
        $trip = Trip::create([
            'user_id' => Auth::id(),
            'origin_lat' => $validated['origin_lat'],
            'origin_lng' => $validated['origin_lng'],
            'destination_lat' => $validated['destination_lat'],
            'destination_lng' => $validated['destination_lng'],
            'current_lat' => $validated['origin_lat'], // Start at origin
            'current_lng' => $validated['origin_lng'],
            'share_uuid' => (string) Str::uuid(),
            'status' => 'ongoing',
            'started_at' => now(),
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

        // Update current location
        $trip->update([
            'current_lat' => $validated['lat'],
            'current_lng' => $validated['lng'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Location updated successfully',
            'trip' => $trip->fresh(),
        ]);
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
