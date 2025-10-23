<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Events\SosCreated;
use App\Models\SosAlert;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * Test suite for SOS Alert functionality.
 *
 * @package Tests\Feature
 */
class SosAlertTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that authenticated user can create an SOS alert.
     *
     * @return void
     */
    public function test_authenticated_user_can_create_sos_alert(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/sos', [
            'lat' => 12.345678,
            'lng' => 98.765432,
            'message' => 'Help! I need assistance.',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'SOS alert created successfully',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'alert' => [
                    'id',
                    'user_id',
                    'trip_id',
                    'latitude',
                    'longitude',
                    'message',
                    'created_at',
                ],
                'broadcast_channel',
            ]);

        // Verify database has the record
        $this->assertDatabaseHas('sos_alerts', [
            'user_id' => $user->id,
            'latitude' => 12.345678,
            'longitude' => 98.765432,
            'message' => 'Help! I need assistance.',
        ]);
    }

    /**
     * Test that SOS alert can be created with a trip_id.
     *
     * @return void
     */
    public function test_sos_alert_can_be_created_with_trip_id(): void
    {
        $user = User::factory()->create();
        $trip = Trip::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->postJson('/api/sos', [
            'lat' => 12.345678,
            'lng' => 98.765432,
            'trip_id' => $trip->id,
            'message' => 'Emergency during trip',
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('sos_alerts', [
            'user_id' => $user->id,
            'trip_id' => $trip->id,
            'latitude' => 12.345678,
            'longitude' => 98.765432,
        ]);
    }

    /**
     * Test that SOS alert can be created without message.
     *
     * @return void
     */
    public function test_sos_alert_can_be_created_without_message(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/sos', [
            'lat' => 12.345678,
            'lng' => 98.765432,
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('sos_alerts', [
            'user_id' => $user->id,
            'latitude' => 12.345678,
            'longitude' => 98.765432,
            'message' => null,
        ]);
    }

    /**
     * Test that SOS alert requires valid coordinates.
     *
     * @return void
     */
    public function test_sos_alert_requires_valid_coordinates(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/sos', [
            'lat' => 'invalid',
            'lng' => 98.765432,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['lat']);
    }

    /**
     * Test that latitude must be within valid range.
     *
     * @return void
     */
    public function test_latitude_must_be_within_valid_range(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/sos', [
            'lat' => 100, // Invalid: exceeds 90
            'lng' => 98.765432,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['lat']);
    }

    /**
     * Test that longitude must be within valid range.
     *
     * @return void
     */
    public function test_longitude_must_be_within_valid_range(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/sos', [
            'lat' => 12.345678,
            'lng' => 200, // Invalid: exceeds 180
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['lng']);
    }

    /**
     * Test that unauthenticated user cannot create SOS alert.
     *
     * @return void
     */
    public function test_unauthenticated_user_cannot_create_sos_alert(): void
    {
        $response = $this->postJson('/api/sos', [
            'lat' => 12.345678,
            'lng' => 98.765432,
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test that SosCreated event is dispatched when alert is created.
     *
     * @return void
     */
    public function test_sos_created_event_is_dispatched(): void
    {
        Event::fake([SosCreated::class]);

        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/sos', [
            'lat' => 12.345678,
            'lng' => 98.765432,
            'message' => 'Help!',
        ]);

        $response->assertStatus(201);

        Event::assertDispatched(SosCreated::class, function ($event) use ($user) {
            return $event->alert->user_id === $user->id
                && $event->alert->latitude == 12.345678
                && $event->alert->longitude == 98.765432;
        });
    }

    /**
     * Test that broadcast channel name is returned in response.
     *
     * @return void
     */
    public function test_broadcast_channel_name_is_returned(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/sos', [
            'lat' => 12.345678,
            'lng' => 98.765432,
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'broadcast_channel' => 'sos.' . $response->json('alert.id'),
            ]);
    }

    /**
     * Test that event broadcasts with correct data.
     *
     * @return void
     */
    public function test_event_broadcast_contains_correct_data(): void
    {
        $user = User::factory()->create();
        $alert = SosAlert::create([
            'user_id' => $user->id,
            'latitude' => 12.345678,
            'longitude' => 98.765432,
            'message' => 'Test message',
        ]);

        $event = new SosCreated($alert);
        $broadcastData = $event->broadcastWith();

        $this->assertArrayHasKey('alert_id', $broadcastData);
        $this->assertArrayHasKey('user_id', $broadcastData);
        $this->assertArrayHasKey('latitude', $broadcastData);
        $this->assertArrayHasKey('longitude', $broadcastData);
        $this->assertArrayHasKey('message', $broadcastData);
        $this->assertArrayHasKey('created_at', $broadcastData);

        $this->assertEquals($alert->id, $broadcastData['alert_id']);
        $this->assertEquals($user->id, $broadcastData['user_id']);
        $this->assertEquals(12.345678, $broadcastData['latitude']);
        $this->assertEquals(98.765432, $broadcastData['longitude']);
    }

    /**
     * Test that event broadcasts on public channel.
     *
     * @return void
     */
    public function test_event_broadcasts_on_public_channel(): void
    {
        $user = User::factory()->create();
        $alert = SosAlert::create([
            'user_id' => $user->id,
            'latitude' => 12.345678,
            'longitude' => 98.765432,
        ]);

        $event = new SosCreated($alert);
        $channel = $event->broadcastOn();

        $this->assertEquals('sos.' . $alert->id, $channel->name);
    }

    /**
     * Test that event broadcasts with custom event name.
     *
     * @return void
     */
    public function test_event_broadcasts_with_custom_event_name(): void
    {
        $user = User::factory()->create();
        $alert = SosAlert::create([
            'user_id' => $user->id,
            'latitude' => 12.345678,
            'longitude' => 98.765432,
        ]);

        $event = new SosCreated($alert);
        $eventName = $event->broadcastAs();

        $this->assertEquals('alert.created', $eventName);
    }
}
