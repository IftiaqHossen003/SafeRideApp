<?php

namespace Tests\Feature;

use App\Events\TripLocationUpdated;
use App\Models\Trip;
use App\Models\TripLocation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * TraccarWebhookTest
 *
 * Feature tests for Traccar webhook endpoint and realtime broadcasting.
 */
class TraccarWebhookTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test webhook health check endpoint.
     */
    public function test_webhook_health_check_returns_ok(): void
    {
        $response = $this->getJson('/api/traccar/webhook/health');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'ok',
                'service' => 'SafeRide Traccar Webhook',
            ]);
    }

    /**
     * Test webhook rejects requests without valid token.
     */
    public function test_webhook_rejects_invalid_token(): void
    {
        config(['traccar.webhook_token' => 'secret123']);

        $response = $this->postJson('/api/traccar/webhook', [
            'position' => [
                'deviceId' => 123,
                'latitude' => 40.7128,
                'longitude' => -74.0060,
                'fixTime' => '2025-01-23T12:00:00.000Z',
            ],
            'device' => [
                'id' => 123,
                'name' => 'Test Device',
            ],
        ], [
            'X-Webhook-Token' => 'wrong_token',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized',
            ]);
    }

    /**
     * Test webhook accepts requests with valid token.
     */
    public function test_webhook_accepts_valid_token(): void
    {
        config(['traccar.webhook_token' => 'secret123']);

        $user = User::factory()->create();
        $trip = Trip::factory()->create([
            'user_id' => $user->id,
            'status' => 'ongoing',
            'traccar_device_id' => 123,
        ]);

        $response = $this->postJson('/api/traccar/webhook', [
            'position' => [
                'deviceId' => 123,
                'latitude' => 40.7128,
                'longitude' => -74.0060,
                'accuracy' => 5.0,
                'speed' => 25.5,
                'altitude' => 10.0,
                'course' => 180.0,
                'fixTime' => '2025-01-23T12:00:00.000Z',
            ],
            'device' => [
                'id' => 123,
                'name' => 'Test Device',
            ],
        ], [
            'X-Webhook-Token' => 'secret123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Position update processed',
                'trip_id' => $trip->id,
            ]);
    }

    /**
     * Test webhook creates trip location record.
     */
    public function test_webhook_creates_trip_location(): void
    {
        Event::fake();

        $user = User::factory()->create();
        $trip = Trip::factory()->create([
            'user_id' => $user->id,
            'status' => 'ongoing',
            'traccar_device_id' => 456,
        ]);

        $this->postJson('/api/traccar/webhook', [
            'position' => [
                'deviceId' => 456,
                'latitude' => 51.5074,
                'longitude' => -0.1278,
                'accuracy' => 10.0,
                'speed' => 30.0,
                'altitude' => 50.0,
                'course' => 270.0,
                'fixTime' => '2025-01-23T14:30:00.000Z',
            ],
            'device' => [
                'id' => 456,
                'name' => 'London Device',
            ],
        ]);

        // Assert trip location was created
        $this->assertDatabaseHas('trip_locations', [
            'trip_id' => $trip->id,
            'latitude' => 51.5074,
            'longitude' => -0.1278,
            'accuracy' => 10.0,
            'speed' => 30.0,
        ]);
    }

    /**
     * Test webhook updates trip's current location.
     */
    public function test_webhook_updates_trip_current_location(): void
    {
        $user = User::factory()->create();
        $trip = Trip::factory()->create([
            'user_id' => $user->id,
            'status' => 'ongoing',
            'traccar_device_id' => 789,
            'current_lat' => 0.0,
            'current_lng' => 0.0,
        ]);

        $this->postJson('/api/traccar/webhook', [
            'position' => [
                'deviceId' => 789,
                'latitude' => 34.0522,
                'longitude' => -118.2437,
                'fixTime' => '2025-01-23T16:00:00.000Z',
            ],
            'device' => [
                'id' => 789,
            ],
        ]);

        $trip->refresh();

        $this->assertEquals(34.0522, $trip->current_lat);
        $this->assertEquals(-118.2437, $trip->current_lng);
        $this->assertNotNull($trip->last_location_update_at);
    }

    /**
     * Test webhook broadcasts location update event.
     */
    public function test_webhook_broadcasts_location_update_event(): void
    {
        Event::fake([TripLocationUpdated::class]);

        $user = User::factory()->create();
        $trip = Trip::factory()->create([
            'user_id' => $user->id,
            'status' => 'ongoing',
            'traccar_device_id' => 101112,
        ]);

        $this->postJson('/api/traccar/webhook', [
            'position' => [
                'deviceId' => 111,
                'latitude' => 48.8566,
                'longitude' => 2.3522,
                'fixTime' => '2025-01-23T18:00:00.000Z',
            ],
            'device' => [
                'id' => 111,
            ],
        ]);

        Event::assertDispatched(TripLocationUpdated::class, function ($event) use ($trip) {
            return $event->trip->id === $trip->id;
        });
    }

    /**
     * Test webhook ignores position updates for trips without active status.
     */
    public function test_webhook_ignores_completed_trips(): void
    {
        $user = User::factory()->create();
        $trip = Trip::factory()->create([
            'user_id' => $user->id,
            'status' => 'completed',
            'traccar_device_id' => 222,
        ]);

        $response = $this->postJson('/api/traccar/webhook', [
            'position' => [
                'deviceId' => 222,
                'latitude' => 40.7128,
                'longitude' => -74.0060,
                'fixTime' => '2025-01-23T19:00:00.000Z',
            ],
            'device' => [
                'id' => 222,
            ],
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'No active trip for this device',
            ]);

        // Assert no trip location was created
        $this->assertEquals(0, TripLocation::where('trip_id', $trip->id)->count());
    }

    /**
     * Test webhook handles missing position data gracefully.
     */
    public function test_webhook_handles_missing_position_data(): void
    {
        $response = $this->postJson('/api/traccar/webhook', [
            'device' => [
                'id' => 999,
            ],
            // Missing 'position' key
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid payload: missing position or device data',
            ]);
    }

    /**
     * Test webhook handles device without active trip.
     */
    public function test_webhook_handles_device_without_active_trip(): void
    {
        $response = $this->postJson('/api/traccar/webhook', [
            'position' => [
                'deviceId' => 999,
                'latitude' => 40.7128,
                'longitude' => -74.0060,
                'fixTime' => '2025-01-23T20:00:00.000Z',
            ],
            'device' => [
                'id' => 999,
            ],
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'No active trip for this device',
            ]);
    }

    /**
     * Test TripLocationUpdated event includes latest position data.
     */
    public function test_event_includes_latest_position_data(): void
    {
        $user = User::factory()->create();
        $trip = Trip::factory()->create([
            'user_id' => $user->id,
            'status' => 'ongoing',
        ]);

        // Create a trip location
        TripLocation::create([
            'trip_id' => $trip->id,
            'latitude' => 40.7128,
            'longitude' => -74.0060,
            'accuracy' => 5.0,
            'speed' => 25.5,
            'altitude' => 10.0,
            'bearing' => 180.0,
            'recorded_at' => now(),
        ]);

        $event = new TripLocationUpdated($trip);
        $broadcastData = $event->broadcastWith();

        $this->assertArrayHasKey('latest_position', $broadcastData);
        $this->assertNotNull($broadcastData['latest_position']);
        $this->assertEquals(40.7128, $broadcastData['latest_position']['latitude']);
        $this->assertEquals(-74.0060, $broadcastData['latest_position']['longitude']);
        $this->assertEquals(25.5, $broadcastData['latest_position']['speed']);
    }

    /**
     * Test TraccarFetch command dispatches broadcast event.
     */
    public function test_traccar_fetch_command_dispatches_broadcast_event(): void
    {
        Event::fake();

        $user = User::factory()->create();
        $trip = Trip::factory()->create([
            'user_id' => $user->id,
            'status' => 'ongoing',
            'traccar_device_id' => 333,
        ]);

        // Mock Traccar API
        \Illuminate\Support\Facades\Http::fake([
            '*/api/positions*' => \Illuminate\Support\Facades\Http::response([
                [
                    'id' => 1,
                    'deviceId' => 333,
                    'fixTime' => '2025-01-23T21:00:00.000Z',
                    'latitude' => 35.6762,
                    'longitude' => 139.6503,
                    'accuracy' => 8.0,
                    'speed' => 40.0,
                    'altitude' => 100.0,
                    'course' => 90.0,
                ],
            ], 200),
        ]);

        $this->artisan('traccar:fetch', ['--trip' => $trip->id])
            ->assertSuccessful();

        Event::assertDispatched(TripLocationUpdated::class, function ($event) use ($trip) {
            return $event->trip->id === $trip->id;
        });
    }
}
