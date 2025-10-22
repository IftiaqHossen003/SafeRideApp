<?php

namespace Database\Factories;

use App\Models\Trip;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Trip>
 */
class TripFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Trip::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Generate realistic coordinates within a city area (example: San Francisco Bay Area)
        $originLat = fake()->latitude(37.7000, 37.8000);
        $originLng = fake()->longitude(-122.5000, -122.3000);
        
        // Destination within reasonable distance (5-50km away)
        $destinationLat = $originLat + fake()->randomFloat(4, -0.05, 0.05);
        $destinationLng = $originLng + fake()->randomFloat(4, -0.05, 0.05);
        
        // Determine trip status and timing
        $isOngoing = fake()->boolean(30); // 30% chance of ongoing trip
        $startedAt = fake()->dateTimeBetween('-1 week', 'now');
        
        $currentLat = null;
        $currentLng = null;
        $endedAt = null;
        $status = $isOngoing ? 'ongoing' : fake()->randomElement(['completed', 'cancelled']);
        
        if ($isOngoing) {
            // Current location is somewhere between origin and destination
            $progress = fake()->randomFloat(2, 0.1, 0.9);
            $currentLat = $originLat + ($destinationLat - $originLat) * $progress;
            $currentLng = $originLng + ($destinationLng - $originLng) * $progress;
        } else {
            // Trip is completed or cancelled
            $endedAt = fake()->dateTimeBetween($startedAt, 'now');
            if ($status === 'completed') {
                $currentLat = $destinationLat;
                $currentLng = $destinationLng;
            } else {
                // Cancelled somewhere along the way
                $progress = fake()->randomFloat(2, 0.1, 0.7);
                $currentLat = $originLat + ($destinationLat - $originLat) * $progress;
                $currentLng = $originLng + ($destinationLng - $originLng) * $progress;
            }
        }

        return [
            'user_id' => User::factory(),
            'origin_lat' => $originLat,
            'origin_lng' => $originLng,
            'destination_lat' => $destinationLat,
            'destination_lng' => $destinationLng,
            'current_lat' => $currentLat,
            'current_lng' => $currentLng,
            'share_uuid' => (string) Str::uuid(),
            'status' => $status,
            'started_at' => $startedAt,
            'ended_at' => $endedAt,
            'last_location_update_at' => $isOngoing ? fake()->dateTimeBetween($startedAt, 'now') : $endedAt,
        ];
    }

    /**
     * Create an ongoing trip.
     *
     * @return static
     */
    public function ongoing(): static
    {
        return $this->state(function (array $attributes) {
            $startedAt = fake()->dateTimeBetween('-3 hours', 'now');
            
            // Current location between origin and destination
            $progress = fake()->randomFloat(2, 0.1, 0.8);
            $currentLat = $attributes['origin_lat'] + ($attributes['destination_lat'] - $attributes['origin_lat']) * $progress;
            $currentLng = $attributes['origin_lng'] + ($attributes['destination_lng'] - $attributes['origin_lng']) * $progress;
            
            return [
                'status' => 'ongoing',
                'started_at' => $startedAt,
                'ended_at' => null,
                'current_lat' => $currentLat,
                'current_lng' => $currentLng,
                'last_location_update_at' => fake()->dateTimeBetween($startedAt, 'now'),
            ];
        });
    }

    /**
     * Indicate that the trip is completed.
     *
     * @return static
     */
    public function completed(): static
    {
        return $this->state(function (array $attributes) {
            $startedAt = fake()->dateTimeBetween('-1 week', '-1 hour');
            $endedAt = fake()->dateTimeBetween($startedAt, 'now');
            
            return [
                'status' => 'completed',
                'started_at' => $startedAt,
                'ended_at' => $endedAt,
                'current_lat' => $attributes['destination_lat'],
                'current_lng' => $attributes['destination_lng'],
                'last_location_update_at' => $endedAt,
            ];
        });
    }

    /**
     * Indicate that the trip is cancelled.
     *
     * @return static
     */
    public function cancelled(): static
    {
        return $this->state(function (array $attributes) {
            $startedAt = fake()->dateTimeBetween('-1 week', '-1 hour');
            $endedAt = fake()->dateTimeBetween($startedAt, 'now');
            
            // For cancelled trips, current location is somewhere random between origin and destination
            $progress = fake()->randomFloat(2, 0.1, 0.7);
            $currentLat = $attributes['origin_lat'] + ($attributes['destination_lat'] - $attributes['origin_lat']) * $progress;
            $currentLng = $attributes['origin_lng'] + ($attributes['destination_lng'] - $attributes['origin_lng']) * $progress;
            
            return [
                'status' => 'cancelled',
                'started_at' => $startedAt,
                'ended_at' => $endedAt,
                'current_lat' => $currentLat,
                'current_lng' => $currentLng,
                'last_location_update_at' => $endedAt,
            ];
        });
    }

    /**
     * Create a trip for a specific user.
     *
     * @param \App\Models\User $user
     * @return static
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }
}
