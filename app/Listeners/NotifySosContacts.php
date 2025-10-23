<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\SosCreated;
use App\Notifications\SosAlertNotification;
use Illuminate\Support\Facades\Notification;

/**
 * NotifySosContacts Listener
 *
 * Handles the SosCreated event by sending notifications to:
 * 1. Trip owner's trusted contacts who are registered users
 * 2. Volunteers (to be implemented later)
 *
 * @package App\Listeners
 */
class NotifySosContacts
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\SosCreated  $event
     * @return void
     */
    public function handle(SosCreated $event): void
    {
        $alert = $event->alert;

        // Load necessary relations
        $alert->load('user', 'trip');

        // Get the user who triggered the alert
        $user = $alert->user;

        if (!$user) {
            return;
        }

        // Get trusted contacts who are registered users
        $registeredContacts = $user->trustedContacts()
            ->whereNotNull('contact_user_id')
            ->with('contactUser')
            ->get()
            ->map(function ($contact) {
                return $contact->contactUser;
            })
            ->filter(); // Remove any null values

        // Send notifications to all registered trusted contacts
        if ($registeredContacts->isNotEmpty()) {
            Notification::send($registeredContacts, new SosAlertNotification($alert));
        }

        // TODO: Notify volunteers within a certain radius
        // This will be implemented in a future update
    }
}
