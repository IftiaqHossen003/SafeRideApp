<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Events\TripLocationUpdated;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * Test suite for trip location broadcasting functionality.
 *
 * @package Tests\Feature
 */
class TripLocationBroadcastTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that TripLocationUpdated event is dispatched when location is updated.
     *
     * @return void
     */
    public function test_trip_location_updated_event_is_dispatched_on_location_update(): void
    {
        Event::fake([TripLocationUpdated::class]);

        $user = User::factory()->create();
        $trip = Trip::factory()->create([
            'user_id' => $user->id,
            'status' => 'ongoing',
        ]);

        $response = $this->actingAs($user)->patchJson("/api/trips/{$trip->id}/location", [
            'lat' => 12.345678,
            'lng' => 98.765432,
        ]);

        $response->assertOk();

        Event::assertDispatched(TripLocationUpdated::class, function ($event) use ($trip) {
            return $event->trip->id === $trip->id
                && $event->trip->current_lat == 12.345678
                && $event->trip->current_lng == 98.765432;
        });
    }

    /**
     * Test that event is not dispatched when location update fails validation.
     *
     * @return void
     */
    public function test_event_is_not_dispatched_on_invalid_location_update(): void
    {
        Event::fake([TripLocationUpdated::class]);

        $user = User::factory()->create();
        $trip = Trip::factory()->create([
            'user_id' => $user->id,
            'status' => 'ongoing',
        ]);

        $response = $this->actingAs($user)->patchJson("/api/trips/{$trip->id}/location", [
            'lat' => 'invalid',
            'lng' => 98.765432,
        ]);

        $response->assertStatus(422);

        Event::assertNotDispatched(TripLocationUpdated::class);
    }

    /**
     * Test that event is not dispatched when unauthorized user tries to update location.
     *
     * @return void
     */
    public function test_event_is_not_dispatched_on_unauthorized_location_update(): void
    {
        Event::fake([TripLocationUpdated::class]);

        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $trip = Trip::factory()->create([
            'user_id' => $owner->id,
            'status' => 'ongoing',
        ]);

        $response = $this->actingAs($otherUser)->patchJson("/api/trips/{$trip->id}/location", [
            'lat' => 12.345678,
            'lng' => 98.765432,
        ]);

        $response->assertForbidden();

        Event::assertNotDispatched(TripLocationUpdated::class);
    }

    /**
     * Test that event contains correct data in broadcastWith method.
     *
     * @return void
     */
    public function test_event_broadcast_data_contains_correct_fields(): void
    {
        $user = User::factory()->create();
        $trip = Trip::factory()->create([
            'user_id' => $user->id,
            'status' => 'ongoing',
            'current_lat' => 12.345678,
            'current_lng' => 98.765432,
        ]);

        $event = new TripLocationUpdated($trip);
        $broadcastData = $event->broadcastWith();

        $this->assertArrayHasKey('trip_id', $broadcastData);
        $this->assertArrayHasKey('current_lat', $broadcastData);
        $this->assertArrayHasKey('current_lng', $broadcastData);
        $this->assertArrayHasKey('timestamp', $broadcastData);

        $this->assertEquals($trip->id, $broadcastData['trip_id']);
        $this->assertEquals(12.345678, $broadcastData['current_lat']);
        $this->assertEquals(98.765432, $broadcastData['current_lng']);
    }

    /**
     * Test that event broadcasts on correct private channel.
     *
     * @return void
     */
    public function test_event_broadcasts_on_correct_private_channel(): void
    {
        $user = User::factory()->create();
        $trip = Trip::factory()->create([
            'user_id' => $user->id,
        ]);

        $event = new TripLocationUpdated($trip);
        $channels = $event->broadcastOn();

        $this->assertIsArray($channels);
        $this->assertCount(1, $channels);
        $this->assertEquals('private-trip.' . $trip->id, $channels[0]->name);
    }

    /**
     * Test that event broadcasts with custom event name.
     *
     * @return void
     */
    public function test_event_broadcasts_with_custom_event_name(): void
    {
        $user = User::factory()->create();
        $trip = Trip::factory()->create([
            'user_id' => $user->id,
        ]);

        $event = new TripLocationUpdated($trip);
        $eventName = $event->broadcastAs();

        $this->assertEquals('location.updated', $eventName);
    }
}
