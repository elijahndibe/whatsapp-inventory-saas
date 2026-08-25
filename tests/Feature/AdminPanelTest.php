<?php

namespace Tests\Feature;

use App\Models\AdminAuditLog;
use App\Models\Business;
use App\Models\Feature;
use App\Models\Plan;
use App\Models\PlanFeature;
use App\Models\User;
use App\Services\PlatformSettingsService;
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
            'is_active' => '1',
            'sort_order' => 4,
        ]);

        $response->assertRedirect(route('admin.plans.index'));
        $plan = Plan::where('name', 'Enterprise')->firstOrFail();

        $this->actingAs($this->superAdmin)->put(route('admin.plans.update', $plan), [
            'name' => 'Enterprise Plus',
            'price' => 600,
            'currency' => 'NGN',
            'duration_days' => 30,
            'is_active' => '1',
            'sort_order' => 4,
        ]);

        $this->assertSame('Enterprise Plus', $plan->fresh()->name);
    }

    public function test_super_admin_can_edit_the_feature_matrix_and_it_is_audit_logged(): void
    {
        $feature = Feature::create(['key' => 'advanced_analytics', 'name' => 'Advanced analytics', 'type' => Feature::TYPE_BOOLEAN, 'is_enabled' => true]);
        $plan = Plan::create(['name' => 'Pro', 'price' => 0]);

        $response = $this->actingAs($this->superAdmin)->put(route('admin.features.update'), [
            'features' => [
                $feature->id => [
                    'global_enabled' => '1',
                    'plans' => [
                        $plan->id => ['enabled' => '1'],
                    ],
                ],
            ],
        ]);

        $response->assertRedirect(route('admin.features.index'));
        $planFeature = PlanFeature::where('plan_id', $plan->id)->where('feature_id', $feature->id)->firstOrFail();
        $this->assertTrue($planFeature->enabled);
        $this->assertTrue(AdminAuditLog::where('admin_user_id', $this->superAdmin->id)->exists());
    }

    public function test_super_admin_can_globally_disable_a_feature(): void
    {
        $feature = Feature::create(['key' => 'whatsapp_cloud_api', 'name' => 'WhatsApp Cloud API', 'type' => Feature::TYPE_BOOLEAN, 'is_enabled' => true]);

        $this->actingAs($this->superAdmin)->put(route('admin.features.update'), [
            'features' => [$feature->id => ['global_enabled' => '0', 'plans' => []]],
        ]);

        $this->assertFalse($feature->fresh()->is_enabled);
    }

    public function test_super_admin_can_change_commission_settings_and_it_is_audit_logged(): void
    {
        $response = $this->actingAs($this->superAdmin)->put(route('admin.monetization.commission.update'), [
            'commission_enabled' => '1',
            'commission_type' => 'percentage',
            'commission_rate' => 2.0,
        ]);

        $response->assertRedirect(route('admin.monetization.index'));
        $this->assertSame(2.0, app(PlatformSettingsService::class)->commissionRate());
        $this->assertTrue(AdminAuditLog::where('action', 'setting.commission.rate.changed')->exists());
    }

    public function test_super_admin_can_toggle_the_subscription_system(): void
    {
        $this->actingAs($this->superAdmin)->put(route('admin.monetization.subscription-system.update'), [
            'subscription_enabled' => '1',
        ]);

        $this->assertTrue(app(PlatformSettingsService::class)->subscriptionSystemEnabled());
    }

    public function test_super_admin_can_set_a_seller_specific_commission_override(): void
    {
        $business = Business::factory()->create();

        $response = $this->actingAs($this->superAdmin)->post(route('admin.businesses.commission.update', $business), [
            'commission_rate' => 0.75,
        ]);

        $response->assertRedirect();
        $this->assertSame(0.75, $business->fresh()->commission_rate);
        $this->assertTrue($business->fresh()->hasCustomCommissionRate());
    }

    public function test_super_admin_can_view_transactions(): void
    {
        $this->actingAs($this->superAdmin)->get(route('admin.transactions.index'))->assertOk();
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
