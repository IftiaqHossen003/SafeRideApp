<?php

namespace Tests\Feature;

use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Feature test for Trip Viewer functionality.
 */
class TripViewerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that visiting the trip viewer URL returns 200 and shows trip details.
     *
     * @return void
     */
    public function test_trip_viewer_returns_200_and_displays_trip_details(): void
    {
        $user = User::factory()->create([
            'pseudonym' => 'SafeRider123',
        ]);

        $trip = Trip::create([
            'user_id' => $user->id,
            'origin_lat' => 40.7128,
            'origin_lng' => -74.0060,
            'destination_lat' => 34.0522,
            'destination_lng' => -118.2437,
            'current_lat' => 40.7128,
            'current_lng' => -74.0060,
            'share_uuid' => (string) Str::uuid(),
            'status' => 'ongoing',
            'started_at' => now(),
        ]);

        $response = $this->get(route('trip.view', ['share_uuid' => $trip->share_uuid]));

        $response->assertOk();
        $response->assertSee($trip->share_uuid);
        $response->assertSee('Ongoing'); // Capitalized in view
        $response->assertSee('SafeRider123');
    }

    /**
     * Test that trip viewer shows correct status for completed trips.
     *
     * @return void
     */
    public function test_trip_viewer_shows_completed_status(): void
    {
        $user = User::factory()->create();

        $trip = Trip::create([
            'user_id' => $user->id,
            'origin_lat' => 40.7128,
            'origin_lng' => -74.0060,
            'destination_lat' => 34.0522,
            'destination_lng' => -118.2437,
            'share_uuid' => (string) Str::uuid(),
            'status' => 'completed',
            'started_at' => now()->subHour(),
            'ended_at' => now(),
        ]);

        $response = $this->get(route('trip.view', ['share_uuid' => $trip->share_uuid]));

        $response->assertOk();
        $response->assertSee('Completed'); // Capitalized in view
        $response->assertSee('Trip Ended');
    }

    /**
     * Test that trip viewer shows coordinates.
     *
     * @return void
     */
    public function test_trip_viewer_displays_coordinates(): void
    {
        $user = User::factory()->create();

        $trip = Trip::create([
            'user_id' => $user->id,
            'origin_lat' => 40.7128,
            'origin_lng' => -74.0060,
            'destination_lat' => 34.0522,
            'destination_lng' => -118.2437,
            'current_lat' => 39.9526,
            'current_lng' => -75.1652,
            'share_uuid' => (string) Str::uuid(),
            'status' => 'ongoing',
            'started_at' => now(),
        ]);

        $response = $this->get(route('trip.view', ['share_uuid' => $trip->share_uuid]));

        $response->assertOk();
        // Check origin
        $response->assertSee('40.7128');
        $response->assertSee('-74.0060');
        // Check destination
        $response->assertSee('34.0522');
        $response->assertSee('-118.2437');
        // Check current location
        $response->assertSee('39.9526');
        $response->assertSee('-75.1652');
    }

    /**
     * Test that trip viewer does not require authentication.
     *
     * @return void
     */
    public function test_trip_viewer_does_not_require_authentication(): void
    {
        $user = User::factory()->create();

        $trip = Trip::create([
            'user_id' => $user->id,
            'origin_lat' => 40.7128,
            'origin_lng' => -74.0060,
            'destination_lat' => 34.0522,
            'destination_lng' => -118.2437,
            'share_uuid' => (string) Str::uuid(),
            'status' => 'ongoing',
            'started_at' => now(),
        ]);

        // Access without authentication
        $response = $this->get(route('trip.view', ['share_uuid' => $trip->share_uuid]));

        $response->assertOk();
        $response->assertSee($trip->share_uuid);
    }

    /**
     * Test that trip viewer returns 404 for invalid share_uuid.
     *
     * @return void
     */
    public function test_trip_viewer_returns_404_for_invalid_share_uuid(): void
    {
        $invalidUuid = (string) Str::uuid();

        $response = $this->get(route('trip.view', ['share_uuid' => $invalidUuid]));

        $response->assertNotFound();
    }

    /**
     * Test that trip viewer shows pseudonym when available.
     *
     * @return void
     */
    public function test_trip_viewer_shows_pseudonym_when_available(): void
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'pseudonym' => 'JohnnyRider',
        ]);

        $trip = Trip::create([
            'user_id' => $user->id,
            'origin_lat' => 40.7128,
            'origin_lng' => -74.0060,
            'destination_lat' => 34.0522,
            'destination_lng' => -118.2437,
            'share_uuid' => (string) Str::uuid(),
            'status' => 'ongoing',
            'started_at' => now(),
        ]);

        $response = $this->get(route('trip.view', ['share_uuid' => $trip->share_uuid]));

        $response->assertOk();
        $response->assertSee('JohnnyRider');
        $response->assertDontSee('John Doe'); // Should not expose real name
    }

    /**
     * Test that trip viewer shows name fallback when pseudonym is null.
     *
     * @return void
     */
    public function test_trip_viewer_shows_name_fallback_when_no_pseudonym(): void
    {
        $user = User::factory()->create([
            'name' => 'Jane Smith',
            'pseudonym' => null,
        ]);

        $trip = Trip::create([
            'user_id' => $user->id,
            'origin_lat' => 40.7128,
            'origin_lng' => -74.0060,
            'destination_lat' => 34.0522,
            'destination_lng' => -118.2437,
            'share_uuid' => (string) Str::uuid(),
            'status' => 'ongoing',
            'started_at' => now(),
        ]);

        $response = $this->get(route('trip.view', ['share_uuid' => $trip->share_uuid]));

        $response->assertOk();
        $response->assertSee('Jane Smith'); // Falls back to name when no pseudonym
    }
}
