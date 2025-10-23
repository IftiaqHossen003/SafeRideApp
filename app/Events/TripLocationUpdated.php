<?php

namespace App\Events;

use App\Models\Trip;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * TripLocationUpdated Event
 *
 * Broadcasts when a trip's location is updated.
 * Notifies trip owner and trusted contacts in real-time.
 */
class TripLocationUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The trip instance.
     *
     * @var \App\Models\Trip
     */
    public Trip $trip;

    /**
     * Create a new event instance.
     *
     * @param  \App\Models\Trip  $trip
     * @return void
     */
    public function __construct(Trip $trip)
    {
        $this->trip = $trip;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('trip.' . $this->trip->id),
        ];
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs(): string
    {
        return 'location.updated';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        // Get the latest trip location from database
        $latestLocation = $this->trip->locations()
            ->latest('recorded_at')
            ->first();

        return [
            'trip_id' => $this->trip->id,
            'current_lat' => $this->trip->current_lat,
            'current_lng' => $this->trip->current_lng,
            'latest_position' => $latestLocation ? [
                'latitude' => (float) $latestLocation->latitude,
                'longitude' => (float) $latestLocation->longitude,
                'accuracy' => $latestLocation->accuracy,
                'speed' => $latestLocation->speed,
                'altitude' => $latestLocation->altitude,
                'bearing' => $latestLocation->bearing,
                'recorded_at' => $latestLocation->recorded_at->toIso8601String(),
            ] : null,
            'status' => $this->trip->status,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
