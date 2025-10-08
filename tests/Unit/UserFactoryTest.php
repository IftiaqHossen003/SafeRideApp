<?php

namespace Tests\Unit;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Unit test for User model pseudonym and is_volunteer attributes.
 */
class UserFactoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that user factory provides pseudonym attribute.
     *
     * @return void
     */
    public function test_user_factory_provides_pseudonym(): void
    {
        $user = User::factory()->make();

        $this->assertArrayHasKey('pseudonym', $user->getAttributes());
    }

    /**
     * Test that user factory provides is_volunteer attribute.
     *
     * @return void
     */
    public function test_user_factory_provides_is_volunteer(): void
    {
        $user = User::factory()->make();

        $this->assertArrayHasKey('is_volunteer', $user->getAttributes());
        $this->assertIsBool($user->is_volunteer);
    }

    /**
     * Test that is_volunteer defaults to false when not provided.
     *
     * @return void
     */
    public function test_is_volunteer_can_be_set(): void
    {
        $volunteer = User::factory()->make(['is_volunteer' => true]);
        $nonVolunteer = User::factory()->make(['is_volunteer' => false]);

        $this->assertTrue($volunteer->is_volunteer);
        $this->assertFalse($nonVolunteer->is_volunteer);
    }

    /**
     * Test that pseudonym can be nullable.
     *
     * @return void
     */
    public function test_pseudonym_can_be_nullable(): void
    {
        $user = User::factory()->make(['pseudonym' => null]);

        $this->assertNull($user->pseudonym);
    }
}
