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
        return [
            'user_id' => User::factory(),
            'origin_lat' => fake()->latitude(),
            'origin_lng' => fake()->longitude(),
            'destination_lat' => fake()->latitude(),
            'destination_lng' => fake()->longitude(),
            'current_lat' => fake()->latitude(),
            'current_lng' => fake()->longitude(),
            'share_uuid' => (string) Str::uuid(),
            'status' => 'ongoing',
            'started_at' => now(),
            'ended_at' => null,
        ];
    }

    /**
     * Indicate that the trip is completed.
     *
     * @return static
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'ended_at' => now(),
        ]);
    }

    /**
     * Indicate that the trip is cancelled.
     *
     * @return static
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
            'ended_at' => now(),
        ]);
    }
}
