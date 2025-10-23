<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use Illuminate\View\View;

/**
 * TripViewerController
 *
 * Handles public, read-only viewing of trips via share_uuid.
 */
class TripViewerController extends Controller
{
    /**
     * Display a trip by its share_uuid.
     *
     * This is a public endpoint - no authentication required.
     * Only shows pseudonym, no personal user data.
     *
     * @param  string  $shareUuid
     * @return \Illuminate\View\View
     */
    public function show(string $shareUuid): View
    {
        // Find trip by share_uuid or fail with 404
        $trip = Trip::where('share_uuid', $shareUuid)
            ->with([
                'user:id,pseudonym,name', // Load user for pseudonym
                'locations' => function($query) {
                    $query->orderBy('recorded_at', 'asc');
                }
            ])
            ->firstOrFail();

        // Prepare user display name (pseudonym or fallback to name)
        $userDisplayName = $trip->user->pseudonym ?? $trip->user->name ?? 'Anonymous User';

        return view('trip-viewer.show', [
            'trip' => $trip,
            'userDisplayName' => $userDisplayName,
        ]);
    }
}
