<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminReportsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that admin users can access the reports page.
     */
    public function test_admin_can_access_reports_page(): void
    {
        $this->withoutVite();
        
        // Create an admin user
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        // Create some test trips
        $trips = Trip::factory()->count(5)->create();

        // Access the reports page as admin
        $response = $this->actingAs($admin)
            ->get(route('admin.reports.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.reports.index');
        $response->assertViewHas('trips');
    }

    /**
     * Test that non-admin users cannot access the reports page.
     */
    public function test_non_admin_cannot_access_reports_page(): void
    {
        // Create a regular user (not admin)
        $user = User::factory()->create([
            'is_admin' => false,
        ]);

        // Attempt to access the reports page
        $response = $this->actingAs($user)
            ->get(route('admin.reports.index'));

        $response->assertStatus(403); // Forbidden
    }

    /**
     * Test that unauthenticated users cannot access the reports page.
     */
    public function test_guest_cannot_access_reports_page(): void
    {
        $response = $this->get(route('admin.reports.index'));

        $response->assertRedirect(route('login'));
    }

    /**
     * Test that admin can export CSV.
     */
    public function test_admin_can_export_csv(): void
    {
        // Create an admin user
        $admin = User::factory()->create([
            'is_admin' => true,
            'pseudonym' => 'TestAdmin',
        ]);

        // Create a trip with known data
        $trip = Trip::factory()->create([
            'user_id' => $admin->id,
            'origin_lat' => 10.123456,
            'origin_lng' => 20.654321,
            'destination_lat' => 30.111111,
            'destination_lng' => 40.222222,
            'status' => 'completed',
            'started_at' => now()->subHour(),
            'ended_at' => now(),
        ]);

        // Export CSV as admin
        $response = $this->actingAs($admin)
            ->get(route('admin.reports.export'));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        
        // Check content disposition header contains attachment and filename
        $disposition = $response->headers->get('content-disposition');
        $this->assertStringContainsString('attachment', $disposition);
        $this->assertStringContainsString('trip-report-', $disposition);

        // Check CSV content structure
        $content = $response->streamedContent();
        $this->assertStringContainsString('"Trip ID"', $content);
        $this->assertStringContainsString('"User Pseudonym"', $content);
        $this->assertStringContainsString((string) $trip->id, $content);
        $this->assertStringContainsString('TestAdmin', $content);
        $this->assertStringContainsString('completed', $content);
    }

    /**
     * Test that non-admin users cannot export CSV.
     */
    public function test_non_admin_cannot_export_csv(): void
    {
        // Create a regular user (not admin)
        $user = User::factory()->create([
            'is_admin' => false,
        ]);

        // Attempt to export CSV
        $response = $this->actingAs($user)
            ->get(route('admin.reports.export'));

        $response->assertStatus(403); // Forbidden
    }

    /**
     * Test that CSV export handles empty trip data correctly.
     */
    public function test_csv_export_with_no_trips(): void
    {
        // Create an admin user
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        // Export CSV with no trips
        $response = $this->actingAs($admin)
            ->get(route('admin.reports.export'));

        $response->assertStatus(200);

        // Check CSV only contains headers
        $content = $response->streamedContent();
        $this->assertStringContainsString('"Trip ID"', $content);
        $this->assertStringContainsString('"User Pseudonym"', $content);
        
        // Should only have header row (plus newline)
        $lines = explode("\n", trim($content));
        $this->assertCount(1, $lines);
    }

    /**
     * Test that CSV export formats coordinates correctly.
     */
    public function test_csv_export_formats_coordinates(): void
    {
        // Create an admin user
        $admin = User::factory()->create([
            'is_admin' => true,
            'pseudonym' => 'AdminUser',
        ]);

        // Create a trip with specific coordinates
        Trip::factory()->create([
            'user_id' => $admin->id,
            'origin_lat' => 10.1234567,
            'origin_lng' => 20.7654321,
            'destination_lat' => 30.111111,
            'destination_lng' => 40.999999,
            'status' => 'ongoing',
            'started_at' => now(),
        ]);

        // Export CSV
        $response = $this->actingAs($admin)
            ->get(route('admin.reports.export'));

        $content = $response->streamedContent();
        
        // Check that coordinates are formatted as "lat, lng"
        $this->assertStringContainsString('"10.1234567, 20.7654321"', $content);
        $this->assertStringContainsString('"30.1111110, 40.9999990"', $content);
    }

    /**
     * Test that admin user identified by email can access reports.
     */
    public function test_admin_email_user_can_access_reports(): void
    {
        $this->withoutVite();
        
        // Set ADMIN_EMAIL in config
        config(['app.env' => 'testing']);
        putenv('ADMIN_EMAIL=admin@example.com');

        // Create a user with the admin email (but is_admin = false)
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'is_admin' => false,
        ]);

        // Create some trips
        Trip::factory()->count(3)->create();

        // Access the reports page
        $response = $this->actingAs($user)
            ->get(route('admin.reports.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.reports.index');
    }

    /**
     * Test that reports page displays paginated trips.
     */
    public function test_reports_page_displays_paginated_trips(): void
    {
        $this->withoutVite();
        
        // Create an admin user
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        // Create 25 trips (should span 2 pages with 20 per page)
        Trip::factory()->count(25)->create();

        // Access first page
        $response = $this->actingAs($admin)
            ->get(route('admin.reports.index'));

        $response->assertStatus(200);
        
        // Check that we have pagination data
        $trips = $response->viewData('trips');
        $this->assertEquals(20, $trips->count());
        $this->assertEquals(25, $trips->total());
        $this->assertEquals(2, $trips->lastPage());
    }
}
