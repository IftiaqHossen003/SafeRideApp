<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Events\SosCreated;
use App\Models\RouteAlert;
use App\Models\SosAlert;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * Test suite for Route Anomaly Detection functionality.
 *
 * Tests stoppage and deviation detection in TripController.
 *
 * @package Tests\Feature
 */
class RouteAnomalyDetectionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that stoppage is detected after 10 minutes without movement.
     *
     * @return void
     */
    public function test_stoppage_detected_after_threshold_time(): void
    {
        Event::fake([SosCreated::class]);
        Carbon::setTestNow('2025-10-09 12:00:00');

        $user = User::factory()->create();
        $trip = Trip::factory()->create([
            'user_id' => $user->id,
            'origin_lat' => 23.8103,
            'origin_lng' => 90.4125,
            'destination_lat' => 23.8203,
            'destination_lng' => 90.4225,
            'current_lat' => 23.8103,
            'current_lng' => 90.4125,
            'last_location_update_at' => Carbon::now(),
        ]);

        // Move time forward 11 minutes
        Carbon::setTestNow('2025-10-09 12:11:00');

        // Update location with minimal movement (less than 20m)
        $response = $this->actingAs($user)->patchJson("/api/trips/{$trip->id}/location", [
            'lat' => 23.81031, // Moved ~11 meters north
            'lng' => 90.4125,
        ]);

        $response->assertOk();

        // Assert stoppage alert was created
        $this->assertDatabaseHas('route_alerts', [
            'trip_id' => $trip->id,
            'alert_type' => RouteAlert::TYPE_STOPPAGE,
        ]);

        $alert = RouteAlert::where('trip_id', $trip->id)->first();
        $this->assertNotNull($alert);
        $this->assertEquals(RouteAlert::TYPE_STOPPAGE, $alert->alert_type);
        $this->assertArrayHasKey('distance_moved_m', $alert->details);
        $this->assertArrayHasKey('time_stopped_minutes', $alert->details);
        $this->assertLessThanOrEqual(20, $alert->details['distance_moved_m']);
        $this->assertGreaterThanOrEqual(10, $alert->details['time_stopped_minutes']);

        Carbon::setTestNow();
    }

    /**
     * Test that stoppage is not detected when time threshold is not met.
     *
     * @return void
     */
    public function test_stoppage_not_detected_before_threshold_time(): void
    {
        Carbon::setTestNow('2025-10-09 12:00:00');

        $user = User::factory()->create();
        $trip = Trip::factory()->create([
            'user_id' => $user->id,
            'origin_lat' => 23.8103,
            'origin_lng' => 90.4125,
            'destination_lat' => 23.8203,
            'destination_lng' => 90.4225,
            'current_lat' => 23.8103,
            'current_lng' => 90.4125,
            'last_location_update_at' => Carbon::now(),
        ]);

        // Move time forward only 5 minutes (below threshold)
        Carbon::setTestNow('2025-10-09 12:05:00');

        // Update location with minimal movement
        $this->actingAs($user)->patchJson("/api/trips/{$trip->id}/location", [
            'lat' => 23.81031,
            'lng' => 90.4125,
        ]);

        // Assert no stoppage alert was created
        $this->assertDatabaseMissing('route_alerts', [
            'trip_id' => $trip->id,
            'alert_type' => RouteAlert::TYPE_STOPPAGE,
        ]);

        Carbon::setTestNow();
    }

    /**
     * Test that stoppage is not detected when distance moved exceeds threshold.
     *
     * @return void
     */
    public function test_stoppage_not_detected_when_distance_exceeds_threshold(): void
    {
        Carbon::setTestNow('2025-10-09 12:00:00');

        $user = User::factory()->create();
        $trip = Trip::factory()->create([
            'user_id' => $user->id,
            'origin_lat' => 23.8103,
            'origin_lng' => 90.4125,
            'destination_lat' => 23.8203,
            'destination_lng' => 90.4225,
            'current_lat' => 23.8103,
            'current_lng' => 90.4125,
            'last_location_update_at' => Carbon::now(),
        ]);

        // Move time forward 11 minutes
        Carbon::setTestNow('2025-10-09 12:11:00');

        // Update location with significant movement (more than 20m)
        $this->actingAs($user)->patchJson("/api/trips/{$trip->id}/location", [
            'lat' => 23.8105, // Moved ~220 meters north
            'lng' => 90.4125,
        ]);

        // Assert no stoppage alert was created
        $this->assertDatabaseMissing('route_alerts', [
            'trip_id' => $trip->id,
            'alert_type' => RouteAlert::TYPE_STOPPAGE,
        ]);

        Carbon::setTestNow();
    }

    /**
     * Test that route deviation is detected when too far from path.
     *
     * @return void
     */
    public function test_deviation_detected_when_exceeds_threshold(): void
    {
        $user = User::factory()->create();
        $trip = Trip::factory()->create([
            'user_id' => $user->id,
            'origin_lat' => 23.8103, // Origin: Dhaka
            'origin_lng' => 90.4125,
            'destination_lat' => 23.8203, // Destination: ~1.1 km north
            'destination_lng' => 90.4125,
            'current_lat' => 23.8103,
            'current_lng' => 90.4125,
        ]);

        // Update location to a point far from the straight path (east deviation)
        $this->actingAs($user)->patchJson("/api/trips/{$trip->id}/location", [
            'lat' => 23.8153, // Midpoint latitude
            'lng' => 90.4175, // ~0.55 km east of path (exceeds 0.5 km threshold)
        ]);

        // Assert deviation alert was created
        $this->assertDatabaseHas('route_alerts', [
            'trip_id' => $trip->id,
            'alert_type' => RouteAlert::TYPE_DEVIATION,
        ]);

        $alert = RouteAlert::where('trip_id', $trip->id)
            ->where('alert_type', RouteAlert::TYPE_DEVIATION)
            ->first();

        $this->assertNotNull($alert);
        $this->assertArrayHasKey('deviation_distance_km', $alert->details);
        $this->assertArrayHasKey('threshold_km', $alert->details);
        $this->assertGreaterThan(0.5, $alert->details['deviation_distance_km']);
    }

    /**
     * Test that deviation is not detected when within threshold.
     *
     * @return void
     */
    public function test_deviation_not_detected_when_within_threshold(): void
    {
        $user = User::factory()->create();
        $trip = Trip::factory()->create([
            'user_id' => $user->id,
            'origin_lat' => 23.8103,
            'origin_lng' => 90.4125,
            'destination_lat' => 23.8203,
            'destination_lng' => 90.4125,
            'current_lat' => 23.8103,
            'current_lng' => 90.4125,
        ]);

        // Update location to a point close to the straight path
        $this->actingAs($user)->patchJson("/api/trips/{$trip->id}/location", [
            'lat' => 23.8153, // Midpoint on the path
            'lng' => 90.4126, // Slight deviation, ~0.11 km (within 0.5 km threshold)
        ]);

        // Assert no deviation alert was created
        $this->assertDatabaseMissing('route_alerts', [
            'trip_id' => $trip->id,
            'alert_type' => RouteAlert::TYPE_DEVIATION,
        ]);
    }

    /**
     * Test that duplicate stoppage alerts are not created within 30 minutes.
     *
     * @return void
     */
    public function test_duplicate_stoppage_alerts_prevented(): void
    {
        Carbon::setTestNow('2025-10-09 12:00:00');

        $user = User::factory()->create();
        $trip = Trip::factory()->create([
            'user_id' => $user->id,
            'origin_lat' => 23.8103,
            'origin_lng' => 90.4125,
            'destination_lat' => 23.8203,
            'destination_lng' => 90.4225,
            'current_lat' => 23.8103,
            'current_lng' => 90.4125,
            'last_location_update_at' => Carbon::now(),
        ]);

        // Move time forward 11 minutes and trigger first stoppage
        Carbon::setTestNow('2025-10-09 12:11:00');
        $this->actingAs($user)->patchJson("/api/trips/{$trip->id}/location", [
            'lat' => 23.81031,
            'lng' => 90.4125,
        ]);

        // Update last_location_update_at for next test
        $trip->refresh();
        $trip->update(['last_location_update_at' => Carbon::now()]);

        // Move time forward another 11 minutes and try to trigger second stoppage
        Carbon::setTestNow('2025-10-09 12:22:00');
        $this->actingAs($user)->patchJson("/api/trips/{$trip->id}/location", [
            'lat' => 23.81032,
            'lng' => 90.4125,
        ]);

        // Assert only one stoppage alert exists (duplicate prevented)
        $this->assertEquals(1, RouteAlert::where('trip_id', $trip->id)
            ->where('alert_type', RouteAlert::TYPE_STOPPAGE)
            ->count());

        Carbon::setTestNow();
    }

    /**
     * Test that duplicate deviation alerts are not created within 5 minutes.
     *
     * @return void
     */
    public function test_duplicate_deviation_alerts_prevented(): void
    {
        Carbon::setTestNow('2025-10-09 12:00:00');

        $user = User::factory()->create();
        $trip = Trip::factory()->create([
            'user_id' => $user->id,
            'origin_lat' => 23.8103,
            'origin_lng' => 90.4125,
            'destination_lat' => 23.8203,
            'destination_lng' => 90.4125,
            'current_lat' => 23.8103,
            'current_lng' => 90.4125,
        ]);

        // Trigger first deviation
        $this->actingAs($user)->patchJson("/api/trips/{$trip->id}/location", [
            'lat' => 23.8153,
            'lng' => 90.4175,
        ]);

        // Move time forward 3 minutes (within 5-minute window)
        Carbon::setTestNow('2025-10-09 12:03:00');

        // Try to trigger second deviation
        $this->actingAs($user)->patchJson("/api/trips/{$trip->id}/location", [
            'lat' => 23.8154,
            'lng' => 90.4176,
        ]);

        // Assert only one deviation alert exists (duplicate prevented)
        $this->assertEquals(1, RouteAlert::where('trip_id', $trip->id)
            ->where('alert_type', RouteAlert::TYPE_DEVIATION)
            ->count());

        Carbon::setTestNow();
    }

    /**
     * Test that SOS alert is NOT auto-created by default.
     *
     * @return void
     */
    public function test_sos_not_auto_created_when_config_disabled(): void
    {
        Config::set('saferide.auto_create_sos_on_anomaly', false);

        Carbon::setTestNow('2025-10-09 12:00:00');

        $user = User::factory()->create();
        $trip = Trip::factory()->create([
            'user_id' => $user->id,
            'origin_lat' => 23.8103,
            'origin_lng' => 90.4125,
            'destination_lat' => 23.8203,
            'destination_lng' => 90.4225,
            'current_lat' => 23.8103,
            'current_lng' => 90.4125,
            'last_location_update_at' => Carbon::now(),
        ]);

        Carbon::setTestNow('2025-10-09 12:11:00');

        $this->actingAs($user)->patchJson("/api/trips/{$trip->id}/location", [
            'lat' => 23.81031,
            'lng' => 90.4125,
        ]);

        // Assert route alert was created
        $this->assertDatabaseHas('route_alerts', [
            'trip_id' => $trip->id,
            'alert_type' => RouteAlert::TYPE_STOPPAGE,
        ]);

        // Assert SOS alert was NOT created
        $this->assertDatabaseMissing('sos_alerts', [
            'trip_id' => $trip->id,
        ]);

        Carbon::setTestNow();
    }

    /**
     * Test that SOS alert IS auto-created when config enabled.
     *
     * @return void
     */
    public function test_sos_auto_created_when_config_enabled_for_stoppage(): void
    {
        Config::set('saferide.auto_create_sos_on_anomaly', true);

        Carbon::setTestNow('2025-10-09 12:00:00');

        $user = User::factory()->create();
        $trip = Trip::factory()->create([
            'user_id' => $user->id,
            'origin_lat' => 23.8103,
            'origin_lng' => 90.4125,
            'destination_lat' => 23.8203,
            'destination_lng' => 90.4225,
            'current_lat' => 23.8103,
            'current_lng' => 90.4125,
            'last_location_update_at' => Carbon::now(),
        ]);

        Carbon::setTestNow('2025-10-09 12:11:00');

        $this->actingAs($user)->patchJson("/api/trips/{$trip->id}/location", [
            'lat' => 23.81031,
            'lng' => 90.4125,
        ]);

        // Assert route alert was created
        $this->assertDatabaseHas('route_alerts', [
            'trip_id' => $trip->id,
            'alert_type' => RouteAlert::TYPE_STOPPAGE,
        ]);

        // Assert SOS alert WAS created
        $this->assertDatabaseHas('sos_alerts', [
            'trip_id' => $trip->id,
            'user_id' => $user->id,
        ]);

        $sosAlert = SosAlert::where('trip_id', $trip->id)->first();
        $this->assertStringContainsString('stopped', $sosAlert->message);

        Carbon::setTestNow();
    }

    /**
     * Test that SOS alert IS auto-created when config enabled for deviation.
     *
     * @return void
     */
    public function test_sos_auto_created_when_config_enabled_for_deviation(): void
    {
        Config::set('saferide.auto_create_sos_on_anomaly', true);

        $user = User::factory()->create();
        $trip = Trip::factory()->create([
            'user_id' => $user->id,
            'origin_lat' => 23.8103,
            'origin_lng' => 90.4125,
            'destination_lat' => 23.8203,
            'destination_lng' => 90.4125,
            'current_lat' => 23.8103,
            'current_lng' => 90.4125,
        ]);

        $this->actingAs($user)->patchJson("/api/trips/{$trip->id}/location", [
            'lat' => 23.8153,
            'lng' => 90.4175,
        ]);

        // Assert route alert was created
        $this->assertDatabaseHas('route_alerts', [
            'trip_id' => $trip->id,
            'alert_type' => RouteAlert::TYPE_DEVIATION,
        ]);

        // Assert SOS alert WAS created
        $this->assertDatabaseHas('sos_alerts', [
            'trip_id' => $trip->id,
            'user_id' => $user->id,
        ]);

        $sosAlert = SosAlert::where('trip_id', $trip->id)->first();
        $this->assertStringContainsString('deviation', $sosAlert->message);
    }

    /**
     * Test that first location update doesn't trigger stoppage detection.
     *
     * @return void
     */
    public function test_first_location_update_skips_stoppage_detection(): void
    {
        Carbon::setTestNow('2025-10-09 12:00:00');

        $user = User::factory()->create();
        $trip = Trip::factory()->create([
            'user_id' => $user->id,
            'origin_lat' => 23.8103,
            'origin_lng' => 90.4125,
            'destination_lat' => 23.8203,
            'destination_lng' => 90.4225,
            'current_lat' => 23.8103,
            'current_lng' => 90.4125,
            'last_location_update_at' => null, // No previous update
        ]);

        // Even though time passes, first update shouldn't trigger stoppage
        Carbon::setTestNow('2025-10-09 12:15:00');

        $this->actingAs($user)->patchJson("/api/trips/{$trip->id}/location", [
            'lat' => 23.81031,
            'lng' => 90.4125,
        ]);

        // Assert no stoppage alert was created
        $this->assertDatabaseMissing('route_alerts', [
            'trip_id' => $trip->id,
            'alert_type' => RouteAlert::TYPE_STOPPAGE,
        ]);

        Carbon::setTestNow();
    }
}
