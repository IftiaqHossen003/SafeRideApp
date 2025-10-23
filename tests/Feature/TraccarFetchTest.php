<?php

namespace Tests\Feature;

use App\Models\Trip;
use App\Models\TripLocation;
use App\Models\User;
use App\Services\TraccarService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * TraccarFetchTest
 *
 * Feature tests for Traccar GPS position fetching and storage.
 * Uses HTTP mocking to simulate Traccar API responses.
 */
class TraccarFetchTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that TraccarService can fetch device positions successfully.
     *
     * @return void
     */
    public function test_traccar_service_can_fetch_device_positions(): void
    {
        // Mock Traccar API response
        Http::fake([
            '*/api/positions*' => Http::response([
                [
                    'id' => 1,
                    'deviceId' => 123,
                    'protocol' => 'osmand',
                    'serverTime' => '2025-01-23T12:00:00.000Z',
                    'deviceTime' => '2025-01-23T12:00:00.000Z',
                    'fixTime' => '2025-01-23T12:00:00.000Z',
                    'latitude' => 40.7128,
                    'longitude' => -74.0060,
                    'altitude' => 10.0,
                    'speed' => 25.5,
                    'course' => 180.0,
                    'accuracy' => 5.0,
                    'address' => 'New York, NY',
                    'attributes' => [],
                ],
                [
                    'id' => 2,
                    'deviceId' => 123,
                    'protocol' => 'osmand',
                    'serverTime' => '2025-01-23T12:05:00.000Z',
                    'deviceTime' => '2025-01-23T12:05:00.000Z',
                    'fixTime' => '2025-01-23T12:05:00.000Z',
                    'latitude' => 40.7589,
                    'longitude' => -73.9851,
                    'altitude' => 15.0,
                    'speed' => 30.0,
                    'course' => 90.0,
                    'accuracy' => 8.0,
                    'address' => 'Times Square, NY',
                    'attributes' => [],
                ],
            ], 200),
        ]);

        // Create TraccarService instance
        $traccar = new TraccarService();

        // Fetch positions
        $from = new \DateTime('2025-01-23T11:00:00Z');
        $to = new \DateTime('2025-01-23T13:00:00Z');
        $positions = $traccar->fetchDevicePositionsForTimeRange($from, $to, 123);

        // Assertions
        $this->assertIsArray($positions);
        $this->assertCount(2, $positions);
        $this->assertEquals(123, $positions[0]['deviceId']);
        $this->assertEquals(40.7128, $positions[0]['latitude']);
        $this->assertEquals(-74.0060, $positions[0]['longitude']);
    }

    /**
     * Test that TraccarService can get last device position.
     *
     * @return void
     */
    public function test_traccar_service_can_get_last_device_position(): void
    {
        // Mock Traccar API response
        Http::fake([
            '*/api/positions*' => Http::response([
                [
                    'id' => 99,
                    'deviceId' => 456,
                    'fixTime' => '2025-01-23T14:30:00.000Z',
                    'latitude' => 34.0522,
                    'longitude' => -118.2437,
                    'accuracy' => 10.0,
                ],
            ], 200),
        ]);

        $traccar = new TraccarService();
        $lastPosition = $traccar->getDeviceLastPosition(456);

        $this->assertIsArray($lastPosition);
        $this->assertEquals(456, $lastPosition['deviceId']);
        $this->assertEquals(34.0522, $lastPosition['latitude']);
    }

    /**
     * Test that traccar:fetch command inserts positions into database.
     *
     * @return void
     */
    public function test_traccar_fetch_command_inserts_positions(): void
    {
        // Mock Traccar API
        Http::fake([
            '*/api/positions*' => Http::response([
                [
                    'id' => 1,
                    'deviceId' => 789,
                    'fixTime' => '2025-01-23T10:00:00.000Z',
                    'latitude' => 51.5074,
                    'longitude' => -0.1278,
                    'accuracy' => 5.0,
                    'speed' => 20.0,
                    'altitude' => 50.0,
                    'course' => 270.0,
                ],
            ], 200),
        ]);

        // Create test user and trip
        $user = User::factory()->create();
        $trip = Trip::factory()->create([
            'user_id' => $user->id,
            'status' => 'in_progress',
            'traccar_device_id' => 789,
        ]);

        // Run command
        $this->artisan('traccar:fetch', ['--trip' => $trip->id])
            ->assertSuccessful();

        // Assert position was stored
        $this->assertDatabaseHas('trip_locations', [
            'trip_id' => $trip->id,
            'latitude' => 51.5074,
            'longitude' => -0.1278,
        ]);

        $this->assertEquals(1, TripLocation::where('trip_id', $trip->id)->count());
    }

    /**
     * Test that duplicate positions are not inserted.
     *
     * @return void
     */
    public function test_traccar_fetch_avoids_duplicate_positions(): void
    {
        // Mock Traccar API (same position twice)
        Http::fake([
            '*/api/positions*' => Http::response([
                [
                    'id' => 1,
                    'deviceId' => 111,
                    'fixTime' => '2025-01-23T09:00:00.000Z',
                    'latitude' => 48.8566,
                    'longitude' => 2.3522,
                    'accuracy' => 10.0,
                ],
            ], 200),
        ]);

        $user = User::factory()->create();
        $trip = Trip::factory()->create([
            'user_id' => $user->id,
            'status' => 'in_progress',
            'traccar_device_id' => 111,
        ]);

        // Run command first time
        $this->artisan('traccar:fetch', ['--trip' => $trip->id])
            ->assertSuccessful();

        $firstCount = TripLocation::where('trip_id', $trip->id)->count();

        // Run command second time (should not duplicate)
        $this->artisan('traccar:fetch', ['--trip' => $trip->id])
            ->assertSuccessful();

        $secondCount = TripLocation::where('trip_id', $trip->id)->count();

        // Assert no duplicates
        $this->assertEquals($firstCount, $secondCount);
        $this->assertEquals(1, $secondCount);
    }

    /**
     * Test that command handles trips without device mapping gracefully.
     *
     * @return void
     */
    public function test_traccar_fetch_skips_trips_without_device_mapping(): void
    {
        $user = User::factory()->create();
        $trip = Trip::factory()->create([
            'user_id' => $user->id,
            'status' => 'in_progress',
            'traccar_device_id' => null, // No device mapping
        ]);

        $this->artisan('traccar:fetch', ['--trip' => $trip->id])
            ->expectsOutput('⚠️  Trip #' . $trip->id . ' has no Traccar device mapping. Skipping.')
            ->assertSuccessful();

        // No positions should be inserted
        $this->assertEquals(0, TripLocation::where('trip_id', $trip->id)->count());
    }

    /**
     * Test that TraccarService handles API errors gracefully.
     *
     * @return void
     */
    public function test_traccar_service_handles_api_errors(): void
    {
        // Mock API failure
        Http::fake([
            '*/api/positions*' => Http::response(null, 500),
        ]);

        $traccar = new TraccarService();

        $this->expectException(\Illuminate\Http\Client\RequestException::class);

        $from = new \DateTime('2025-01-23T11:00:00Z');
        $to = new \DateTime('2025-01-23T13:00:00Z');
        $traccar->fetchDevicePositionsForTimeRange($from, $to);
    }

    /**
     * Test connection test method.
     *
     * @return void
     */
    public function test_traccar_service_can_test_connection(): void
    {
        // Mock successful devices endpoint
        Http::fake([
            '*/api/devices*' => Http::response([
                ['id' => 1, 'name' => 'Device 1', 'uniqueId' => 'abc123'],
                ['id' => 2, 'name' => 'Device 2', 'uniqueId' => 'def456'],
            ], 200),
        ]);

        $traccar = new TraccarService();
        $result = $traccar->testConnection();

        $this->assertTrue($result);
    }
}
