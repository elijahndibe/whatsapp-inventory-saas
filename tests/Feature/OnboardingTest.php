<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnboardingTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->business = Business::factory()->create();
        $this->owner = User::factory()->create(['business_id' => $this->business->id]);
        $this->owner->assignRole('Owner');
    }

    public function test_a_fresh_business_sees_the_add_products_and_connect_paystack_steps_as_not_done(): void
    {
        $response = $this->actingAs($this->owner)->get(route('onboarding.show'));

        $response->assertOk();
        $response->assertSee(__('Add your first products'), false);
        $response->assertSee(__('Add product'), false);
        $response->assertViewHas('hasProducts', false);
        $response->assertViewHas('hasPaystack', false);
    }

    public function test_having_a_product_and_a_connected_paystack_account_marks_those_steps_done(): void
    {
        Product::factory()->for($this->business)->create();
        $this->business->update(['paystack_subaccount_code' => 'ACCT_test123']);

        $response = $this->actingAs($this->owner)->get(route('onboarding.show'));

        $response->assertOk();
        $response->assertViewHas('hasProducts', true);
        $response->assertViewHas('hasPaystack', true);
        $response->assertSee(__('Add more'), false);
    }

    public function test_finishing_onboarding_marks_it_complete_and_redirects_to_the_dashboard(): void
    {
        $this->assertNull($this->business->onboarding_completed_at);

        $response = $this->actingAs($this->owner)->post(route('onboarding.finish'));

        $response->assertRedirect(route('dashboard'));
        $this->assertNotNull($this->business->fresh()->onboarding_completed_at);
    }

    public function test_finishing_onboarding_is_not_a_gate_it_can_be_skipped_at_any_progress(): void
    {
        // No products, no Paystack — "finish" still succeeds, because the
        // checklist is a helpful default landing spot, never a mandatory gate.
        $response = $this->actingAs($this->owner)->post(route('onboarding.finish'));

        $response->assertRedirect(route('dashboard'));
    }

    public function test_a_registered_user_is_redirected_to_onboarding_not_straight_to_the_dashboard(): void
    {
        $response = $this->post('/register', [
            'business_name' => "Chidi's Electronics",
            'name' => 'Chidi Nwosu',
            'email' => 'chidi@example.com',
            'phone' => '+2348011112222',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('onboarding.show', absolute: false));
    }
}
