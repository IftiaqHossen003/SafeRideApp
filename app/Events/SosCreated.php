<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\SosAlert;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * SosCreated Event
 *
 * Broadcast event when a new SOS alert is created.
 * Uses a public channel so volunteers can subscribe to all SOS alerts.
 *
 * @package App\Events
 */
class SosCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The SOS alert instance.
     *
     * @var \App\Models\SosAlert
     */
    public SosAlert $alert;

    /**
     * Create a new event instance.
     *
     * @param  \App\Models\SosAlert  $alert
     * @return void
     */
    public function __construct(SosAlert $alert)
    {
        $this->alert = $alert;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel
     */
    public function broadcastOn(): Channel
    {
        return new Channel('sos.' . $this->alert->id);
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs(): string
    {
        return 'alert.created';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'alert_id' => $this->alert->id,
            'user_id' => $this->alert->user_id,
            'trip_id' => $this->alert->trip_id,
            'latitude' => (float) $this->alert->latitude,
            'longitude' => (float) $this->alert->longitude,
            'message' => $this->alert->message,
            'created_at' => $this->alert->created_at->toIso8601String(),
        ];
    }
}
