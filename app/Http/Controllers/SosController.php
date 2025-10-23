<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Events\SosCreated;
use App\Models\SosAlert;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * SOS Alert Controller
 *
 * Handles emergency SOS alert creation and management.
 *
 * @package App\Http\Controllers
 */
class SosController extends Controller
{
    /**
     * Display a listing of SOS alerts.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $alerts = SosAlert::with(['user', 'trip'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('sos-alerts.index', compact('alerts'));
    }

    /**
     * Store a new SOS alert.
     *
     * Creates an emergency alert with location coordinates and optional message.
     * Broadcasts the alert to volunteers via public channel.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        // Validate incoming data
        $validated = $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'trip_id' => 'nullable|integer|exists:trips,id',
            'message' => 'nullable|string|max:1000',
        ]);

        // Create SOS alert
        $alert = SosAlert::create([
            'user_id' => Auth::id(),
            'trip_id' => $validated['trip_id'] ?? null,
            'latitude' => $validated['lat'],
            'longitude' => $validated['lng'],
            'message' => $validated['message'] ?? null,
        ]);

        // Broadcast the SOS alert to volunteers
        broadcast(new SosCreated($alert))->toOthers();

        // Return success response
        return response()->json([
            'success' => true,
            'message' => 'SOS alert created successfully',
            'alert' => [
                'id' => $alert->id,
                'user_id' => $alert->user_id,
                'trip_id' => $alert->trip_id,
                'latitude' => (float) $alert->latitude,
                'longitude' => (float) $alert->longitude,
                'message' => $alert->message,
                'created_at' => $alert->created_at->toIso8601String(),
            ],
            'broadcast_channel' => 'sos.' . $alert->id,
        ], 201);
    }
}
