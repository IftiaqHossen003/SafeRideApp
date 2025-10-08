<?php

use App\Models\Trip;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

/**
 * Trip location update channel authorization.
 * 
 * Allow access if:
 * - User is the trip owner, OR
 * - User is in the trip owner's trusted contacts list
 */
Broadcast::channel('trip.{tripId}', function ($user, $tripId) {
    $trip = Trip::find($tripId);
    
    if (!$trip) {
        return false;
    }
    
    // Allow if user owns the trip
    if ($user->id === $trip->user_id) {
        return true;
    }
    
    // Allow if user is in trip owner's trusted contacts
    $isTrustedContact = $trip->user->trustedContacts()
        ->where('contact_user_id', $user->id)
        ->exists();
    
    return $isTrustedContact;
});
