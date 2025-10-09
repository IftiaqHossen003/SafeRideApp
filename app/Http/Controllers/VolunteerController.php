<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Volunteer Controller
 *
 * Handles volunteer registration and status management.
 *
 * @package App\Http\Controllers
 */
class VolunteerController extends Controller
{
    /**
     * Toggle volunteer status for the authenticated user.
     *
     * Allows users to opt-in or opt-out of volunteer mode.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggle(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'is_volunteer' => 'required|boolean',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->update([
            'is_volunteer' => $validated['is_volunteer'],
        ]);

        return response()->json([
            'success' => true,
            'message' => $validated['is_volunteer'] 
                ? 'You are now registered as a volunteer' 
                : 'Volunteer mode disabled',
            'is_volunteer' => $user->is_volunteer,
        ]);
    }
}
