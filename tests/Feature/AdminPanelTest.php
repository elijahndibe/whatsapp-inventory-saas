<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::factory()->create(['business_id' => null, 'is_super_admin' => true]);
    }

    public function test_a_regular_business_owner_cannot_access_the_admin_panel(): void
    {
        $business = Business::factory()->create();
        $owner = User::factory()->create(['business_id' => $business->id]);

        $this->actingAs($owner)->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_a_guest_is_redirected_to_login(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }

    public function test_super_admin_can_view_the_dashboard(): void
    {
        Business::factory()->count(2)->create();

        $response = $this->actingAs($this->superAdmin)->get(route('admin.dashboard'));

        $response->assertOk();
    }

    public function test_super_admin_can_view_all_businesses_across_tenants(): void
    {
        $businessA = Business::factory()->create(['name' => 'Alpha Store']);
        $businessB = Business::factory()->create(['name' => 'Beta Store']);

        $response = $this->actingAs($this->superAdmin)->get(route('admin.businesses.index'));

        $response->assertSeeText('Alpha Store');
        $response->assertSeeText('Beta Store');
    }

    public function test_super_admin_can_suspend_and_activate_a_business(): void
    {
        $business = Business::factory()->create(['status' => 'active']);

        $this->actingAs($this->superAdmin)->post(route('admin.businesses.suspend', $business));
        $this->assertSame('suspended', $business->fresh()->status);

        $this->actingAs($this->superAdmin)->post(route('admin.businesses.activate', $business));
        $this->assertSame('active', $business->fresh()->status);
    }

    public function test_super_admin_can_create_and_update_a_plan(): void
    {
        $response = $this->actingAs($this->superAdmin)->post(route('admin.plans.store'), [
            'name' => 'Enterprise',
            'price' => 500,
            'currency' => 'NGN',
            'duration_days' => 30,
            'features' => ['paystack' => '1', 'invoices' => '1'],
            'is_active' => '1',
            'sort_order' => 4,
        ]);

        $response->assertRedirect(route('admin.plans.index'));
        $plan = Plan::where('name', 'Enterprise')->firstOrFail();
        $this->assertTrue($plan->hasFeature('paystack'));
        $this->assertFalse($plan->hasFeature('whatsapp_cloud_api'));

        $this->actingAs($this->superAdmin)->put(route('admin.plans.update', $plan), [
            'name' => 'Enterprise Plus',
            'price' => 600,
            'currency' => 'NGN',
            'duration_days' => 30,
            'is_active' => '1',
            'sort_order' => 4,
        ]);

        $this->assertSame('Enterprise Plus', $plan->fresh()->name);
        $this->assertFalse($plan->fresh()->hasFeature('paystack'), 'Unchecked features must be explicitly turned off, not left as-is.');
    }

    public function test_super_admin_can_view_subscriptions_and_users(): void
    {
        $this->actingAs($this->superAdmin)->get(route('admin.subscriptions.index'))->assertOk();
        $this->actingAs($this->superAdmin)->get(route('admin.users.index'))->assertOk();
    }

    public function test_super_admin_can_view_failed_jobs_and_logs(): void
    {
        $this->actingAs($this->superAdmin)->get(route('admin.failed-jobs.index'))->assertOk();
        $this->actingAs($this->superAdmin)->get(route('admin.logs.index'))->assertOk();
    }
}
