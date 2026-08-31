<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }

    public function test_a_sole_owner_can_delete_their_account_and_the_business_is_closed_not_deleted(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $business = Business::factory()->create();
        $owner = User::factory()->create(['business_id' => $business->id]);
        $owner->assignRole('Owner');

        $response = $this->actingAs($owner)->delete('/profile', ['password' => 'password']);

        $response->assertSessionHasNoErrors()->assertRedirect('/');
        $this->assertGuest();
        $this->assertNull($owner->fresh());

        // The business itself, and everything under it, survives — only
        // closed, so historical orders/payments stay intact for reporting.
        $business->refresh();
        $this->assertSame('closed', $business->status);
        $this->assertNotNull($business->closed_at);
    }

    public function test_an_owner_cannot_delete_their_account_while_other_staff_remain(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $business = Business::factory()->create();
        $owner = User::factory()->create(['business_id' => $business->id]);
        $owner->assignRole('Owner');
        $staff = User::factory()->create(['business_id' => $business->id]);
        $staff->assignRole('Staff');

        $response = $this->actingAs($owner)->delete('/profile', ['password' => 'password']);

        $response->assertSessionHasErrorsIn('userDeletion', 'business');
        $this->assertNotNull($owner->fresh());
        $this->assertSame('active', $business->fresh()->status);
    }

    public function test_a_staff_member_deleting_their_own_account_does_not_affect_the_business(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $business = Business::factory()->create();
        $owner = User::factory()->create(['business_id' => $business->id]);
        $owner->assignRole('Owner');
        $staff = User::factory()->create(['business_id' => $business->id]);
        $staff->assignRole('Staff');

        $response = $this->actingAs($staff)->delete('/profile', ['password' => 'password']);

        $response->assertSessionHasNoErrors()->assertRedirect('/');
        $this->assertNull($staff->fresh());
        $this->assertSame('active', $business->fresh()->status);
        $this->assertNotNull($owner->fresh());
    }
}
