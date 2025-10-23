<?php

namespace Tests\Feature;

use App\Models\DeviceMapping;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * DeviceMappingTest
 *
 * Feature tests for device mapping CRUD operations and business logic.
 */
class DeviceMappingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that a device mapping can be created successfully.
     */
    public function test_device_mapping_can_be_created(): void
    {
        $user = User::factory()->create();

        $deviceMapping = DeviceMapping::create([
            'user_id' => $user->id,
            'traccar_device_id' => 123,
            'device_name' => 'iPhone 13',
            'unique_id' => '123456789',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('device_mappings', [
            'user_id' => $user->id,
            'traccar_device_id' => 123,
            'device_name' => 'iPhone 13',
            'is_active' => true,
        ]);
    }

    /**
     * Test that only one device can be active per user.
     */
    public function test_only_one_active_device_per_user(): void
    {
        $user = User::factory()->create();

        // Create first device (active)
        $device1 = DeviceMapping::create([
            'user_id' => $user->id,
            'traccar_device_id' => 123,
            'device_name' => 'iPhone 13',
            'unique_id' => '123456789',
            'is_active' => true,
        ]);

        $this->assertTrue($device1->fresh()->is_active);

        // Create second device (active) - should deactivate first
        $device2 = DeviceMapping::create([
            'user_id' => $user->id,
            'traccar_device_id' => 456,
            'device_name' => 'Galaxy S21',
            'unique_id' => '987654321',
            'is_active' => true,
        ]);

        // Refresh from database
        $device1 = $device1->fresh();
        $device2 = $device2->fresh();

        // Only device2 should be active
        $this->assertFalse($device1->is_active);
        $this->assertTrue($device2->is_active);
    }

    /**
     * Test that activating a device deactivates others for the same user.
     */
    public function test_activating_device_deactivates_others(): void
    {
        $user = User::factory()->create();

        // Create first device (active)
        $device1 = DeviceMapping::create([
            'user_id' => $user->id,
            'traccar_device_id' => 123,
            'device_name' => 'iPhone 13',
            'unique_id' => '123456789',
            'is_active' => true,
        ]);

        $this->assertTrue($device1->fresh()->is_active);

        // Create second device (active) - should deactivate first
        $device2 = DeviceMapping::create([
            'user_id' => $user->id,
            'traccar_device_id' => 456,
            'device_name' => 'Galaxy S21',
            'unique_id' => '987654321',
            'is_active' => true,
        ]);

        // Refresh from database
        $device1 = $device1->fresh();
        $device2 = $device2->fresh();

        // Only device2 should be active
        $this->assertFalse($device1->is_active);
        $this->assertTrue($device2->is_active);
    }

    /**
     * Test that different users can have active devices simultaneously.
     */
    public function test_different_users_can_have_active_devices(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $device1 = DeviceMapping::create([
            'user_id' => $user1->id,
            'traccar_device_id' => 123,
            'device_name' => 'iPhone 13',
            'unique_id' => '123456789',
            'is_active' => true,
        ]);

        $device2 = DeviceMapping::create([
            'user_id' => $user2->id,
            'traccar_device_id' => 456,
            'device_name' => 'Galaxy S21',
            'unique_id' => '987654321',
            'is_active' => true,
        ]);

        // Both should remain active
        $this->assertTrue($device1->fresh()->is_active);
        $this->assertTrue($device2->fresh()->is_active);
    }

    /**
     * Test getActiveForUser() static method.
     */
    public function test_get_active_for_user(): void
    {
        $user = User::factory()->create();

        // No active device initially
        $this->assertNull(DeviceMapping::getActiveForUser($user->id));

        // Create active device
        $activeDevice = DeviceMapping::create([
            'user_id' => $user->id,
            'traccar_device_id' => 123,
            'device_name' => 'iPhone 13',
            'unique_id' => '123456789',
            'is_active' => true,
        ]);

        // Should return the active device
        $retrieved = DeviceMapping::getActiveForUser($user->id);
        $this->assertNotNull($retrieved);
        $this->assertEquals($activeDevice->id, $retrieved->id);
    }

    /**
     * Test device mapping relationships.
     */
    public function test_device_mapping_belongs_to_user(): void
    {
        $user = User::factory()->create();

        $device = DeviceMapping::create([
            'user_id' => $user->id,
            'traccar_device_id' => 123,
            'device_name' => 'iPhone 13',
            'unique_id' => '123456789',
            'is_active' => true,
        ]);

        $this->assertInstanceOf(User::class, $device->user);
        $this->assertEquals($user->id, $device->user->id);
    }

    /**
     * Test user can have multiple device mappings.
     */
    public function test_user_can_have_multiple_device_mappings(): void
    {
        $user = User::factory()->create();

        DeviceMapping::create([
            'user_id' => $user->id,
            'traccar_device_id' => 123,
            'device_name' => 'iPhone 13',
            'unique_id' => '123456789',
            'is_active' => false,
        ]);

        DeviceMapping::create([
            'user_id' => $user->id,
            'traccar_device_id' => 456,
            'device_name' => 'Galaxy S21',
            'unique_id' => '987654321',
            'is_active' => true,
        ]);

        $this->assertEquals(2, $user->deviceMappings()->count());
    }

    /**
     * Test active scope.
     */
    public function test_active_scope(): void
    {
        $user = User::factory()->create();

        DeviceMapping::create([
            'user_id' => $user->id,
            'traccar_device_id' => 123,
            'device_name' => 'iPhone 13',
            'unique_id' => '123456789',
            'is_active' => true,
        ]);

        DeviceMapping::create([
            'user_id' => $user->id,
            'traccar_device_id' => 456,
            'device_name' => 'Galaxy S21',
            'unique_id' => '987654321',
            'is_active' => false,
        ]);

        $activeDevices = DeviceMapping::active()->get();
        $this->assertEquals(1, $activeDevices->count());
        $this->assertEquals('iPhone 13', $activeDevices->first()->device_name);
    }

    /**
     * Test forUser scope.
     */
    public function test_for_user_scope(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        DeviceMapping::create([
            'user_id' => $user1->id,
            'traccar_device_id' => 123,
            'device_name' => 'iPhone 13',
            'unique_id' => '123456789',
            'is_active' => true,
        ]);

        DeviceMapping::create([
            'user_id' => $user2->id,
            'traccar_device_id' => 456,
            'device_name' => 'Galaxy S21',
            'unique_id' => '987654321',
            'is_active' => true,
        ]);

        $user1Devices = DeviceMapping::forUser($user1->id)->get();
        $this->assertEquals(1, $user1Devices->count());
        $this->assertEquals('iPhone 13', $user1Devices->first()->device_name);
    }

    /**
     * Test admin can create device mapping via controller.
     */
    public function test_admin_can_create_device_mapping(): void
    {
        Http::fake([
            '*/api/devices*' => Http::response([
                ['id' => 123, 'name' => 'Test Device', 'uniqueId' => 'TEST123'],
            ], 200),
        ]);

        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create();

        $response = $this->actingAs($admin)->post(route('device-mappings.store'), [
            'user_id' => $user->id,
            'traccar_device_id' => 123,
            'device_name' => 'Test Device',
            'unique_id' => 'TEST123',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('device-mappings.index'));
        $this->assertDatabaseHas('device_mappings', [
            'user_id' => $user->id,
            'traccar_device_id' => 123,
        ]);
    }

    /**
     * Test admin can update device mapping.
     */
    public function test_admin_can_update_device_mapping(): void
    {
        Http::fake([
            '*/api/devices*' => Http::response([
                ['id' => 456, 'name' => 'Updated Device', 'uniqueId' => 'UPD456'],
            ], 200),
        ]);

        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create();

        $device = DeviceMapping::create([
            'user_id' => $user->id,
            'traccar_device_id' => 123,
            'device_name' => 'Old Device',
            'unique_id' => 'OLD123',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->put(route('device-mappings.update', $device), [
            'user_id' => $user->id,
            'traccar_device_id' => 456,
            'device_name' => 'Updated Device',
            'unique_id' => 'UPD456',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('device-mappings.index'));
        $this->assertDatabaseHas('device_mappings', [
            'id' => $device->id,
            'device_name' => 'Updated Device',
            'traccar_device_id' => 456,
        ]);
    }

    /**
     * Test admin can delete device mapping.
     */
    public function test_admin_can_delete_device_mapping(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create();

        $device = DeviceMapping::create([
            'user_id' => $user->id,
            'traccar_device_id' => 123,
            'device_name' => 'Test Device',
            'unique_id' => 'TEST123',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->delete(route('device-mappings.destroy', $device));

        $response->assertRedirect(route('device-mappings.index'));
        $this->assertDatabaseMissing('device_mappings', [
            'id' => $device->id,
        ]);
    }
}
