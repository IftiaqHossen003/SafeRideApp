<?php

namespace Tests\Feature;

use App\Models\TrustedContact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature test for Trusted Contacts functionality.
 */
class TrustedContactTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that a logged-in user can create a trusted contact.
     *
     * @return void
     */
    public function test_logged_in_user_can_create_trusted_contact(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('trusted-contacts.store'), [
            'contact_name' => 'John Doe',
            'contact_phone' => '+1234567890',
            'contact_email' => 'john@example.com',
        ]);

        $response->assertRedirect(route('trusted-contacts.index'));
        $response->assertSessionHas('success', 'Trusted contact added successfully.');

        $this->assertDatabaseHas('trusted_contacts', [
            'user_id' => $user->id,
            'contact_name' => 'John Doe',
            'contact_phone' => '+1234567890',
            'contact_email' => 'john@example.com',
        ]);
    }

    /**
     * Test that another user cannot delete someone else's trusted contact.
     *
     * @return void
     */
    public function test_user_cannot_delete_another_users_trusted_contact(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $contact = TrustedContact::factory()->create([
            'user_id' => $owner->id,
            'contact_name' => 'Jane Doe',
        ]);

        $response = $this->actingAs($otherUser)
            ->delete(route('trusted-contacts.destroy', $contact));

        $response->assertForbidden();

        $this->assertDatabaseHas('trusted_contacts', [
            'id' => $contact->id,
            'user_id' => $owner->id,
        ]);
    }

    /**
     * Test that user can view their own trusted contacts.
     *
     * @return void
     */
    public function test_user_can_view_their_own_trusted_contacts(): void
    {
        $this->withoutVite();

        $user = User::factory()->create();
        $contact = TrustedContact::factory()->create([
            'user_id' => $user->id,
            'contact_name' => 'Test Contact',
        ]);

        $response = $this->actingAs($user)->get(route('trusted-contacts.index'));

        $response->assertOk();
        $response->assertSee('Test Contact');
    }

    /**
     * Test that user can update their own trusted contact.
     *
     * @return void
     */
    public function test_user_can_update_their_own_trusted_contact(): void
    {
        $user = User::factory()->create();
        $contact = TrustedContact::factory()->create([
            'user_id' => $user->id,
            'contact_name' => 'Original Name',
        ]);

        $response = $this->actingAs($user)->put(route('trusted-contacts.update', $contact), [
            'contact_name' => 'Updated Name',
            'contact_phone' => '+9876543210',
            'contact_email' => 'updated@example.com',
        ]);

        $response->assertRedirect(route('trusted-contacts.index'));
        $response->assertSessionHas('success', 'Trusted contact updated successfully.');

        $this->assertDatabaseHas('trusted_contacts', [
            'id' => $contact->id,
            'contact_name' => 'Updated Name',
            'contact_phone' => '+9876543210',
        ]);
    }

    /**
     * Test that user cannot update another user's trusted contact.
     *
     * @return void
     */
    public function test_user_cannot_update_another_users_trusted_contact(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $contact = TrustedContact::factory()->create([
            'user_id' => $owner->id,
            'contact_name' => 'Original Name',
        ]);

        $response = $this->actingAs($otherUser)->put(route('trusted-contacts.update', $contact), [
            'contact_name' => 'Hacked Name',
        ]);

        $response->assertForbidden();

        $this->assertDatabaseHas('trusted_contacts', [
            'id' => $contact->id,
            'contact_name' => 'Original Name',
        ]);
    }

    /**
     * Test that contact_name is required for creating trusted contact.
     *
     * @return void
     */
    public function test_contact_name_is_required(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('trusted-contacts.store'), [
            'contact_name' => '',
            'contact_phone' => '+1234567890',
        ]);

        $response->assertSessionHasErrors('contact_name');
    }

    /**
     * Test that user can delete their own trusted contact.
     *
     * @return void
     */
    public function test_user_can_delete_their_own_trusted_contact(): void
    {
        $user = User::factory()->create();
        $contact = TrustedContact::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->delete(route('trusted-contacts.destroy', $contact));

        $response->assertRedirect(route('trusted-contacts.index'));
        $response->assertSessionHas('success', 'Trusted contact deleted successfully.');

        $this->assertDatabaseMissing('trusted_contacts', [
            'id' => $contact->id,
        ]);
    }
}
