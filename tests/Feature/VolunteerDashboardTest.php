<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\SosAlert;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test suite for volunteer dashboard functionality.
 *
 * @package Tests\Feature
 */
class VolunteerDashboardTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Disable Vite for testing
        $this->withoutVite();
    }

    /**
     * Test that users can toggle volunteer status.
     *
     * @return void
     */
    public function test_user_can_toggle_volunteer_status(): void
    {
        $user = User::factory()->create([
            'is_volunteer' => false,
        ]);

        // Enable volunteer mode
        $response = $this->actingAs($user)->postJson('/volunteer/toggle', [
            'is_volunteer' => true,
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'is_volunteer' => true,
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'is_volunteer' => true,
        ]);

        // Disable volunteer mode
        $response = $this->actingAs($user)->postJson('/volunteer/toggle', [
            'is_volunteer' => false,
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'is_volunteer' => false,
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'is_volunteer' => false,
        ]);
    }

    /**
     * Test that volunteer toggle requires authentication.
     *
     * @return void
     */
    public function test_volunteer_toggle_requires_authentication(): void
    {
        $response = $this->postJson('/volunteer/toggle', [
            'is_volunteer' => true,
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test that volunteer toggle validates boolean input.
     *
     * @return void
     */
    public function test_volunteer_toggle_validates_boolean(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/volunteer/toggle', [
            'is_volunteer' => 'invalid',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['is_volunteer']);
    }

    /**
     * Test that volunteer sees nearby unresolved alerts.
     *
     * @return void
     */
    public function test_volunteer_sees_nearby_unresolved_alerts(): void
    {
        $volunteer = User::factory()->create([
            'is_volunteer' => true,
        ]);

        // Create nearby alert (within 5km)
        // Volunteer at: 23.8103, 90.4125 (Dhaka, Bangladesh)
        // Alert at: 23.8200, 90.4200 (~1.3 km away)
        $nearbyAlert = SosAlert::create([
            'user_id' => User::factory()->create()->id,
            'latitude' => 23.8200,
            'longitude' => 90.4200,
            'message' => 'Help! Nearby emergency.',
        ]);

        // Create far alert (outside 5km)
        // Alert at: 24.0000, 90.5000 (~22 km away)
        $farAlert = SosAlert::create([
            'user_id' => User::factory()->create()->id,
            'latitude' => 24.0000,
            'longitude' => 90.5000,
            'message' => 'Help! Far emergency.',
        ]);

        // Create resolved alert (should not appear)
        $resolvedAlert = SosAlert::create([
            'user_id' => User::factory()->create()->id,
            'latitude' => 23.8150,
            'longitude' => 90.4150,
            'message' => 'Help! Already resolved.',
            'resolved_at' => now(),
        ]);

        $response = $this->actingAs($volunteer)->get('/volunteer/dashboard?' . http_build_query([
            'lat' => 23.8103,
            'lng' => 90.4125,
            'radius_km' => 5,
        ]));

        $response->assertOk()
            ->assertViewIs('volunteer.dashboard')
            ->assertViewHas('alerts')
            ->assertSee('Alert #' . $nearbyAlert->id)
            ->assertSee(number_format((float) $nearbyAlert->latitude, 6))
            ->assertSee(number_format((float) $nearbyAlert->longitude, 6))
            ->assertSee('Help! Nearby emergency.')
            ->assertDontSee('Alert #' . $farAlert->id)
            ->assertDontSee('Help! Far emergency.')
            ->assertDontSee('Alert #' . $resolvedAlert->id)
            ->assertDontSee('Already resolved.');
    }

    /**
     * Test that non-volunteer cannot access dashboard.
     *
     * @return void
     */
    public function test_non_volunteer_cannot_access_dashboard(): void
    {
        $user = User::factory()->create([
            'is_volunteer' => false,
        ]);

        $response = $this->actingAs($user)->get('/volunteer/dashboard?' . http_build_query([
            'lat' => 23.8103,
            'lng' => 90.4125,
        ]));

        $response->assertStatus(403);
    }

    /**
     * Test that dashboard requires authentication.
     *
     * @return void
     */
    public function test_dashboard_requires_authentication(): void
    {
        $response = $this->get('/volunteer/dashboard?' . http_build_query([
            'lat' => 23.8103,
            'lng' => 90.4125,
        ]));

        $response->assertRedirect('/login');
    }

    /**
     * Test that dashboard validates required coordinates.
     *
     * @return void
     */
    public function test_dashboard_validates_coordinates(): void
    {
        $volunteer = User::factory()->create([
            'is_volunteer' => true,
        ]);

        // Missing coordinates
        $response = $this->actingAs($volunteer)->get('/volunteer/dashboard');

        $response->assertStatus(302); // Validation redirect

        // Invalid latitude
        $response = $this->actingAs($volunteer)->get('/volunteer/dashboard?' . http_build_query([
            'lat' => 100, // Invalid: exceeds 90
            'lng' => 90.4125,
        ]));

        $response->assertStatus(302);

        // Invalid longitude
        $response = $this->actingAs($volunteer)->get('/volunteer/dashboard?' . http_build_query([
            'lat' => 23.8103,
            'lng' => 200, // Invalid: exceeds 180
        ]));

        $response->assertStatus(302);
    }

    /**
     * Test that dashboard uses custom radius parameter.
     *
     * @return void
     */
    public function test_dashboard_uses_custom_radius(): void
    {
        $volunteer = User::factory()->create([
            'is_volunteer' => true,
        ]);

        // Create alert at ~10 km distance
        // Volunteer at: 23.8103, 90.4125
        // Alert at: 23.9000, 90.4125 (~10 km away)
        $alert = SosAlert::create([
            'user_id' => User::factory()->create()->id,
            'latitude' => 23.9000,
            'longitude' => 90.4125,
            'message' => '10km away alert.',
        ]);

        // Search with 5km radius - should NOT find alert
        $response = $this->actingAs($volunteer)->get('/volunteer/dashboard?' . http_build_query([
            'lat' => 23.8103,
            'lng' => 90.4125,
            'radius_km' => 5,
        ]));

        $response->assertOk()
            ->assertDontSee('Alert #' . $alert->id);

        // Search with 15km radius - should find alert
        $response = $this->actingAs($volunteer)->get('/volunteer/dashboard?' . http_build_query([
            'lat' => 23.8103,
            'lng' => 90.4125,
            'radius_km' => 15,
        ]));

        $response->assertOk()
            ->assertSee('Alert #' . $alert->id)
            ->assertSee('10km away alert.');
    }

    /**
     * Test that dashboard shows distance for each alert.
     *
     * @return void
     */
    public function test_dashboard_shows_distance_for_alerts(): void
    {
        $volunteer = User::factory()->create([
            'is_volunteer' => true,
        ]);

        $alert = SosAlert::create([
            'user_id' => User::factory()->create()->id,
            'latitude' => 23.8200,
            'longitude' => 90.4200,
            'message' => 'Test alert.',
        ]);

        $response = $this->actingAs($volunteer)->get('/volunteer/dashboard?' . http_build_query([
            'lat' => 23.8103,
            'lng' => 90.4125,
            'radius_km' => 5,
        ]));

        $response->assertOk()
            ->assertSee('km away'); // Distance indicator
    }

    /**
     * Test that dashboard orders alerts by distance (closest first).
     *
     * @return void
     */
    public function test_dashboard_orders_alerts_by_distance(): void
    {
        $volunteer = User::factory()->create([
            'is_volunteer' => true,
        ]);

        // Create alerts at different distances
        $closeAlert = SosAlert::create([
            'user_id' => User::factory()->create()->id,
            'latitude' => 23.8120,
            'longitude' => 90.4130,
            'message' => 'Close alert.',
        ]);

        $farAlert = SosAlert::create([
            'user_id' => User::factory()->create()->id,
            'latitude' => 23.8250,
            'longitude' => 90.4300,
            'message' => 'Far alert.',
        ]);

        $response = $this->actingAs($volunteer)->get('/volunteer/dashboard?' . http_build_query([
            'lat' => 23.8103,
            'lng' => 90.4125,
            'radius_km' => 10,
        ]));

        $response->assertOk();

        // Check that close alert appears before far alert in HTML
        $content = $response->getContent();
        $closePosition = strpos($content, 'Close alert.');
        $farPosition = strpos($content, 'Far alert.');

        $this->assertNotFalse($closePosition);
        $this->assertNotFalse($farPosition);
        $this->assertLessThan($farPosition, $closePosition, 'Close alert should appear before far alert');
    }

    /**
     * Test that dashboard shows claim button for each alert.
     *
     * @return void
     */
    public function test_dashboard_shows_claim_button(): void
    {
        $volunteer = User::factory()->create([
            'is_volunteer' => true,
        ]);

        $alert = SosAlert::create([
            'user_id' => User::factory()->create()->id,
            'latitude' => 23.8200,
            'longitude' => 90.4200,
            'message' => 'Test alert.',
        ]);

        $response = $this->actingAs($volunteer)->get('/volunteer/dashboard?' . http_build_query([
            'lat' => 23.8103,
            'lng' => 90.4125,
            'radius_km' => 5,
        ]));

        $response->assertOk()
            ->assertSee('Claim')
            ->assertSee('claimAlert(' . $alert->id . ')');
    }
}
