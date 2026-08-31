<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SetupReminderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_owner_sees_both_reminders_when_nothing_is_set_up(): void
    {
        $business = Business::factory()->create(['paystack_subaccount_code' => null]);
        $owner = User::factory()->create(['business_id' => $business->id]);
        $owner->assignRole('Owner');

        $response = $this->actingAs($owner)->get(route('dashboard'));

        $response->assertSeeText('Finish setting up your store');
        $response->assertSeeText('Connect bank account');
        $response->assertSeeText('Add a product');
    }

    public function test_the_banner_does_not_appear_once_everything_is_set_up(): void
    {
        $business = Business::factory()->create(['paystack_subaccount_code' => 'ACCT_test123']);
        Product::factory()->create(['business_id' => $business->id]);
        $owner = User::factory()->create(['business_id' => $business->id]);
        $owner->assignRole('Owner');

        $response = $this->actingAs($owner)->get(route('dashboard'));

        $response->assertDontSeeText('Finish setting up your store');
    }

    public function test_only_the_bank_reminder_shows_once_a_product_exists(): void
    {
        $business = Business::factory()->create(['paystack_subaccount_code' => null]);
        Product::factory()->create(['business_id' => $business->id]);
        $owner = User::factory()->create(['business_id' => $business->id]);
        $owner->assignRole('Owner');

        $response = $this->actingAs($owner)->get(route('dashboard'));

        $response->assertSeeText('Connect bank account');
        $response->assertDontSeeText('Add a product');
    }

    public function test_an_admin_never_sees_the_bank_reminder_since_they_cannot_access_settings(): void
    {
        $business = Business::factory()->create(['paystack_subaccount_code' => null]);
        $admin = User::factory()->create(['business_id' => $business->id]);
        $admin->assignRole('Admin');

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertDontSeeText('Connect bank account');
        $response->assertSeeText('Add a product'); // Admin can still add products
    }

    public function test_a_staff_member_with_no_relevant_permissions_sees_no_banner_at_all(): void
    {
        $business = Business::factory()->create(['paystack_subaccount_code' => null]);
        $staff = User::factory()->create(['business_id' => $business->id]);
        $staff->assignRole('Staff');

        $response = $this->actingAs($staff)->get(route('dashboard'));

        $response->assertDontSeeText('Finish setting up your store');
    }
}
