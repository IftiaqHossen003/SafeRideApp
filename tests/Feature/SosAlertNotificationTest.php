<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Events\SosCreated;
use App\Models\SosAlert;
use App\Models\Trip;
use App\Models\TrustedContact;
use App\Models\User;
use App\Notifications\SosAlertNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Test suite for SOS Alert Notification functionality.
 *
 * Tests that trusted contacts and volunteers receive proper notifications
 * when an SOS alert is created.
 *
 * @package Tests\Feature
 */
class SosAlertNotificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that registered trusted contact receives database notification.
     *
     * @return void
     */
    public function test_registered_trusted_contact_receives_notification(): void
    {
        Notification::fake();

        // Create users
        $user = User::factory()->create();
        $trustedContactUser = User::factory()->create();

        // Create trusted contact that is a registered user
        TrustedContact::create([
            'user_id' => $user->id,
            'contact_name' => $trustedContactUser->name,
            'contact_phone' => '1234567890',
            'contact_email' => $trustedContactUser->email,
            'contact_user_id' => $trustedContactUser->id,
        ]);

        // Create SOS alert
        $response = $this->actingAs($user)->postJson('/api/sos', [
            'lat' => 23.8103,
            'lng' => 90.4125,
            'message' => 'Emergency! Need help.',
        ]);

        $response->assertStatus(201);

        // Assert notification was sent to trusted contact
        Notification::assertSentTo(
            $trustedContactUser,
            SosAlertNotification::class,
            function ($notification, $channels) use ($trustedContactUser) {
                return in_array('database', $channels) && in_array('mail', $channels);
            }
        );
    }

    /**
     * Test that multiple registered trusted contacts receive notifications.
     *
     * @return void
     */
    public function test_multiple_registered_contacts_receive_notifications(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $contact1 = User::factory()->create();
        $contact2 = User::factory()->create();

        // Create two trusted contacts that are registered users
        TrustedContact::create([
            'user_id' => $user->id,
            'contact_name' => $contact1->name,
            'contact_phone' => '1111111111',
            'contact_email' => $contact1->email,
            'contact_user_id' => $contact1->id,
        ]);

        TrustedContact::create([
            'user_id' => $user->id,
            'contact_name' => $contact2->name,
            'contact_phone' => '2222222222',
            'contact_email' => $contact2->email,
            'contact_user_id' => $contact2->id,
        ]);

        // Create SOS alert
        $this->actingAs($user)->postJson('/api/sos', [
            'lat' => 23.8103,
            'lng' => 90.4125,
            'message' => 'Emergency!',
        ]);

        // Assert notifications were sent to both contacts
        Notification::assertSentTo([$contact1, $contact2], SosAlertNotification::class);
    }

    /**
     * Test that non-registered trusted contacts do not receive notifications.
     *
     * @return void
     */
    public function test_non_registered_contacts_do_not_receive_notifications(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        // Create trusted contact that is NOT a registered user
        TrustedContact::create([
            'user_id' => $user->id,
            'contact_name' => 'John Doe',
            'contact_phone' => '5555555555',
            'contact_email' => 'john@example.com',
            'contact_user_id' => null, // Not a registered user
        ]);

        // Create SOS alert
        $this->actingAs($user)->postJson('/api/sos', [
            'lat' => 23.8103,
            'lng' => 90.4125,
            'message' => 'Emergency!',
        ]);

        // Assert no notifications were sent (since contact is not registered)
        Notification::assertNothingSent();
    }

    /**
     * Test notification database payload contains required fields.
     *
     * @return void
     */
    public function test_notification_database_payload_contains_required_fields(): void
    {
        $user = User::factory()->create();
        $trustedContactUser = User::factory()->create();

        TrustedContact::create([
            'user_id' => $user->id,
            'contact_name' => $trustedContactUser->name,
            'contact_phone' => '1234567890',
            'contact_email' => $trustedContactUser->email,
            'contact_user_id' => $trustedContactUser->id,
        ]);

        // Create SOS alert without trip
        $alert = SosAlert::create([
            'user_id' => $user->id,
            'latitude' => 23.8103,
            'longitude' => 90.4125,
            'message' => 'Emergency!',
        ]);

        // Fire event manually to test notification content
        event(new SosCreated($alert));

        // Check database notification
        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $trustedContactUser->id,
            'type' => SosAlertNotification::class,
        ]);

        $notification = $trustedContactUser->notifications()->first();
        $this->assertNotNull($notification);

        $data = $notification->data;
        $this->assertArrayHasKey('alert_id', $data);
        $this->assertArrayHasKey('latitude', $data);
        $this->assertArrayHasKey('longitude', $data);
        $this->assertArrayHasKey('message', $data);
        $this->assertArrayHasKey('created_at', $data);
        $this->assertEquals($alert->id, $data['alert_id']);
        $this->assertEquals('23.8103000', $data['latitude']);
        $this->assertEquals('90.4125000', $data['longitude']);
        $this->assertEquals('Emergency!', $data['message']);
    }

    /**
     * Test notification includes trip share_uuid when alert is associated with trip.
     *
     * @return void
     */
    public function test_notification_includes_trip_share_uuid_when_trip_exists(): void
    {
        $user = User::factory()->create();
        $trustedContactUser = User::factory()->create();

        TrustedContact::create([
            'user_id' => $user->id,
            'contact_name' => $trustedContactUser->name,
            'contact_phone' => '1234567890',
            'contact_email' => $trustedContactUser->email,
            'contact_user_id' => $trustedContactUser->id,
        ]);

        // Create trip
        $trip = Trip::factory()->create([
            'user_id' => $user->id,
        ]);

        // Create SOS alert with trip
        $alert = SosAlert::create([
            'user_id' => $user->id,
            'trip_id' => $trip->id,
            'latitude' => 23.8103,
            'longitude' => 90.4125,
            'message' => 'Emergency during trip!',
        ]);

        // Fire event
        event(new SosCreated($alert));

        // Check notification includes trip_share_uuid
        $notification = $trustedContactUser->notifications()->first();
        $this->assertNotNull($notification);

        $data = $notification->data;
        $this->assertArrayHasKey('trip_share_uuid', $data);
        $this->assertEquals($trip->share_uuid, $data['trip_share_uuid']);
    }

    /**
     * Test notification does not include trip_share_uuid when no trip.
     *
     * @return void
     */
    public function test_notification_excludes_trip_share_uuid_when_no_trip(): void
    {
        $user = User::factory()->create();
        $trustedContactUser = User::factory()->create();

        TrustedContact::create([
            'user_id' => $user->id,
            'contact_name' => $trustedContactUser->name,
            'contact_phone' => '1234567890',
            'contact_email' => $trustedContactUser->email,
            'contact_user_id' => $trustedContactUser->id,
        ]);

        // Create SOS alert without trip
        $alert = SosAlert::create([
            'user_id' => $user->id,
            'latitude' => 23.8103,
            'longitude' => 90.4125,
            'message' => 'Emergency!',
        ]);

        // Fire event
        event(new SosCreated($alert));

        // Check notification does not include trip_share_uuid
        $notification = $trustedContactUser->notifications()->first();
        $this->assertNotNull($notification);

        $data = $notification->data;
        $this->assertArrayNotHasKey('trip_share_uuid', $data);
    }

    /**
     * Test that user with no trusted contacts receives no notifications.
     *
     * @return void
     */
    public function test_user_with_no_trusted_contacts_receives_no_notifications(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        // Create SOS alert (user has no trusted contacts)
        $this->actingAs($user)->postJson('/api/sos', [
            'lat' => 23.8103,
            'lng' => 90.4125,
            'message' => 'Emergency!',
        ]);

        // Assert no notifications were sent
        Notification::assertNothingSent();
    }

    /**
     * Test SosCreated event is dispatched when alert is created.
     *
     * @return void
     */
    public function test_sos_created_event_is_dispatched(): void
    {
        Event::fake([SosCreated::class]);

        $user = User::factory()->create();

        $this->actingAs($user)->postJson('/api/sos', [
            'lat' => 23.8103,
            'lng' => 90.4125,
            'message' => 'Emergency!',
        ]);

        Event::assertDispatched(SosCreated::class);
    }
}
