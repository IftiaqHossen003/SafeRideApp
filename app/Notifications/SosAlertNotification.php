<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\SosAlert;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * SosAlertNotification
 *
 * Notification sent when a new SOS alert is created.
 * Notifies trusted contacts and volunteers via database and email.
 *
 * @package App\Notifications
 */
class SosAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The SOS alert instance.
     *
     * @var \App\Models\SosAlert
     */
    protected SosAlert $alert;

    /**
     * Create a new notification instance.
     *
     * @param  \App\Models\SosAlert  $alert
     * @return void
     */
    public function __construct(SosAlert $alert)
    {
        // Load the trip relation if not already loaded
        $this->alert = $alert->load('trip', 'user');
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array<int, string>
     */
    public function via(mixed $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail(mixed $notifiable): MailMessage
    {
        $user = $this->alert->user;
        $userName = $user ? $user->name : 'A user';
        
        $mailMessage = (new MailMessage)
            ->subject('⚠️ Emergency SOS Alert')
            ->greeting('Emergency Alert!')
            ->line($userName . ' has triggered an SOS alert and may need assistance.')
            ->line('Location: ' . $this->alert->latitude . ', ' . $this->alert->longitude);

        if ($this->alert->message) {
            $mailMessage->line('Message: ' . $this->alert->message);
        }

        if ($this->alert->trip) {
            $shareUrl = route('trip.view', ['share_uuid' => $this->alert->trip->share_uuid]);
            $mailMessage->action('View Trip Location', $shareUrl);
        } else {
            $mapsUrl = "https://www.google.com/maps?q={$this->alert->latitude},{$this->alert->longitude}";
            $mailMessage->action('View on Google Maps', $mapsUrl);
        }

        $mailMessage->line('This alert was sent at ' . $this->alert->created_at->format('M d, Y g:i A'));

        return $mailMessage;
    }

    /**
     * Get the array representation of the notification for database storage.
     *
     * @param  mixed  $notifiable
     * @return array<string, mixed>
     */
    public function toDatabase(mixed $notifiable): array
    {
        $data = [
            'alert_id' => $this->alert->id,
            'latitude' => $this->alert->latitude,
            'longitude' => $this->alert->longitude,
            'message' => $this->alert->message,
            'created_at' => $this->alert->created_at->toIso8601String(),
        ];

        // Include trip share_uuid if alert is associated with a trip
        if ($this->alert->trip) {
            $data['trip_share_uuid'] = $this->alert->trip->share_uuid;
        }

        return $data;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array<string, mixed>
     */
    public function toArray(mixed $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
