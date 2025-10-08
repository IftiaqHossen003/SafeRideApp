<?php

namespace Tests\Feature;

use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature test for Trip API functionality.
 */
class TripTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that an authenticated user can start a trip.
     *
     * @return void
     */
    public function test_authenticated_user_can_start_trip(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/trips/start', [
            'origin_lat' => 40.7128,
            'origin_lng' => -74.0060,
            'destination_lat' => 34.0522,
            'destination_lng' => -118.2437,
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'message' => 'Trip started successfully',
        ]);
        $response->assertJsonStructure([
            'success',
            'message',
            'trip' => [
                'id',
                'user_id',
                'origin_lat',
                'origin_lng',
                'destination_lat',
                'destination_lng',
                'share_uuid',
                'status',
                'started_at',
            ],
        ]);

        // Verify trip was created in database
        $this->assertDatabaseHas('trips', [
            'user_id' => $user->id,
            'origin_lat' => 40.7128,
            'origin_lng' => -74.0060,
            'status' => 'ongoing',
        ]);
    }

    /**
     * Test that an authenticated user can update trip location.
     *
     * @return void
     */
    public function test_authenticated_user_can_update_trip_location(): void
    {
        $user = User::factory()->create();
        
        // Create a trip
        $trip = Trip::create([
            'user_id' => $user->id,
            'origin_lat' => 40.7128,
            'origin_lng' => -74.0060,
            'destination_lat' => 34.0522,
            'destination_lng' => -118.2437,
            'current_lat' => 40.7128,
            'current_lng' => -74.0060,
            'share_uuid' => \Illuminate\Support\Str::uuid(),
            'status' => 'ongoing',
            'started_at' => now(),
        ]);

        // Update location
        $response = $this->actingAs($user)->patchJson("/api/trips/{$trip->id}/location", [
            'lat' => 39.9526,
            'lng' => -75.1652,
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'message' => 'Location updated successfully',
        ]);

        // Verify location was updated
        $this->assertDatabaseHas('trips', [
            'id' => $trip->id,
            'current_lat' => 39.9526,
            'current_lng' => -75.1652,
        ]);
    }

    /**
     * Test that user cannot update another user's trip location.
     *
     * @return void
     */
    public function test_user_cannot_update_another_users_trip_location(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        // User1's trip
        $trip = Trip::create([
            'user_id' => $user1->id,
            'origin_lat' => 40.7128,
            'origin_lng' => -74.0060,
            'destination_lat' => 34.0522,
            'destination_lng' => -118.2437,
            'current_lat' => 40.7128,
            'current_lng' => -74.0060,
            'share_uuid' => \Illuminate\Support\Str::uuid(),
            'status' => 'ongoing',
            'started_at' => now(),
        ]);

        // User2 tries to update
        $response = $this->actingAs($user2)->patchJson("/api/trips/{$trip->id}/location", [
            'lat' => 39.9526,
            'lng' => -75.1652,
        ]);

        $response->assertForbidden();
        $response->assertJson([
            'success' => false,
            'message' => 'Unauthorized',
        ]);
    }

    /**
     * Test that an authenticated user can end a trip.
     *
     * @return void
     */
    public function test_authenticated_user_can_end_trip(): void
    {
        $user = User::factory()->create();
        
        // Create a trip
        $trip = Trip::create([
            'user_id' => $user->id,
            'origin_lat' => 40.7128,
            'origin_lng' => -74.0060,
            'destination_lat' => 34.0522,
            'destination_lng' => -118.2437,
            'share_uuid' => \Illuminate\Support\Str::uuid(),
            'status' => 'ongoing',
            'started_at' => now(),
        ]);

        // End the trip
        $response = $this->actingAs($user)->postJson("/api/trips/{$trip->id}/end");

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'message' => 'Trip ended successfully',
        ]);

        // Verify trip was ended
        $this->assertDatabaseHas('trips', [
            'id' => $trip->id,
            'status' => 'completed',
        ]);

        $trip->refresh();
        $this->assertNotNull($trip->ended_at);
    }

    /**
     * Test that validation fails with invalid coordinates.
     *
     * @return void
     */
    public function test_validation_fails_with_invalid_coordinates(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/trips/start', [
            'origin_lat' => 200, // Invalid: out of range
            'origin_lng' => -74.0060,
            'destination_lat' => 34.0522,
            'destination_lng' => -118.2437,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['origin_lat']);
    }

    /**
     * Test that unauthenticated user cannot start a trip.
     *
     * @return void
     */
    public function test_unauthenticated_user_cannot_start_trip(): void
    {
        $response = $this->postJson('/api/trips/start', [
            'origin_lat' => 40.7128,
            'origin_lng' => -74.0060,
            'destination_lat' => 34.0522,
            'destination_lng' => -118.2437,
        ]);

        $response->assertStatus(401);
    }
}
