<?php

namespace Database\Factories;

use App\Models\SosAlert;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory for creating SosAlert instances
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SosAlert>
 */
class SosAlertFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SosAlert::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Generate realistic coordinates within a city area (example: San Francisco Bay Area)
        $latitude = fake()->latitude(37.7000, 37.8000);
        $longitude = fake()->longitude(-122.5000, -122.3000);
        
        // Generate realistic emergency messages
        $emergencyMessages = [
            'Car broke down, need assistance',
            'Feeling unsafe, please help',
            'Accident occurred, need immediate help',
            'Lost and need directions',
            'Suspicious person following me',
            'Vehicle won\'t start',
            'Medical emergency',
            'Flat tire, stranded',
            'Phone battery dying, need help',
            'Wrong neighborhood, feel unsafe',
        ];
        
        $hasMessage = fake()->boolean(70); // 70% chance of having a message
        $message = $hasMessage ? fake()->randomElement($emergencyMessages) : null;
        
        // Some alerts might be resolved
        $isResolved = fake()->boolean(40); // 40% chance of being resolved
        $createdAt = fake()->dateTimeBetween('-1 week', 'now');
        $resolvedAt = $isResolved ? fake()->dateTimeBetween($createdAt, 'now') : null;
        
        return [
            'user_id' => User::factory(),
            'trip_id' => fake()->boolean(80) ? Trip::factory() : null, // 80% chance of being linked to a trip
            'latitude' => $latitude,
            'longitude' => $longitude,
            'message' => $message,
            'created_at' => $createdAt,
            'resolved_at' => $resolvedAt,
            'responder_id' => $isResolved ? User::factory()->state(['is_volunteer' => true]) : null,
        ];
    }

    /**
     * Create an unresolved SOS alert.
     *
     * @return static
     */
    public function unresolved(): static
    {
        return $this->state(fn (array $attributes) => [
            'resolved_at' => null,
            'responder_id' => null,
        ]);
    }

    /**
     * Create a resolved SOS alert.
     *
     * @return static
     */
    public function resolved(): static
    {
        return $this->state(function (array $attributes) {
            $createdAt = $attributes['created_at'] ?? fake()->dateTimeBetween('-1 week', '-1 hour');
            
            return [
                'resolved_at' => fake()->dateTimeBetween($createdAt, 'now'),
                'responder_id' => User::factory()->state(['is_volunteer' => true]),
            ];
        });
    }

    /**
     * Create an SOS alert for a specific trip.
     *
     * @param \App\Models\Trip $trip
     * @return static
     */
    public function forTrip(Trip $trip): static
    {
        return $this->state(function (array $attributes) use ($trip) {
            // Use trip's current location or destination if trip is completed
            $latitude = $trip->current_lat ?? $trip->destination_lat;
            $longitude = $trip->current_lng ?? $trip->destination_lng;
            
            return [
                'user_id' => $trip->user_id,
                'trip_id' => $trip->id,
                'latitude' => $latitude,
                'longitude' => $longitude,
            ];
        });
    }

    /**
     * Create an SOS alert for a specific user.
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

    /**
     * Create an SOS alert without a trip (standalone emergency).
     *
     * @return static
     */
    public function withoutTrip(): static
    {
        return $this->state(fn (array $attributes) => [
            'trip_id' => null,
        ]);
    }

    /**
     * Create an SOS alert with a specific message.
     *
     * @param string $message
     * @return static
     */
    public function withMessage(string $message): static
    {
        return $this->state(fn (array $attributes) => [
            'message' => $message,
        ]);
    }

    /**
     * Create an SOS alert at specific coordinates.
     *
     * @param float $latitude
     * @param float $longitude
     * @return static
     */
    public function atLocation(float $latitude, float $longitude): static
    {
        return $this->state(fn (array $attributes) => [
            'latitude' => $latitude,
            'longitude' => $longitude,
        ]);
    }
}