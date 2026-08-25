<?php

namespace Tests\Unit;

use App\Models\Business;
use App\Models\Feature;
use App\Models\Plan;
use App\Models\PlanFeature;
use App\Services\FeatureService;
use App\Services\PlatformSettingsService;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeatureServiceTest extends TestCase
{
    use RefreshDatabase;

    private FeatureService $features;
    private PlatformSettingsService $settings;

    protected function setUp(): void
    {
        parent::setUp();

        $this->features = app(FeatureService::class);
        $this->settings = app(PlatformSettingsService::class);
    }

    public function test_a_globally_disabled_feature_is_blocked_even_when_a_plan_grants_it(): void
    {
        $this->settings->set('subscription.enabled', true);

        $feature = Feature::create(['key' => 'paystack', 'name' => 'Paystack', 'type' => Feature::TYPE_BOOLEAN, 'is_enabled' => false]);
        $plan = Plan::create(['name' => 'Business', 'price' => 0, 'is_default' => true]);
        PlanFeature::create(['plan_id' => $plan->id, 'feature_id' => $feature->id, 'enabled' => true]);

        $business = Business::factory()->create();

        $this->assertFalse($this->features->enabled($business, 'paystack'));
    }

    public function test_while_subscription_system_is_off_every_globally_enabled_feature_is_unrestricted(): void
    {
        $this->settings->set('subscription.enabled', false);

        Feature::create(['key' => 'advanced_analytics', 'name' => 'Advanced analytics', 'type' => Feature::TYPE_BOOLEAN, 'is_enabled' => true]);

        $business = Business::factory()->create(); // no subscription at all

        $this->assertTrue($this->features->enabled($business, 'advanced_analytics'));
        $this->assertNull($this->features->limit($business, 'products'));
    }

    public function test_while_subscription_system_is_on_access_resolves_against_the_businesss_active_plan(): void
    {
        $this->settings->set('subscription.enabled', true);

        $feature = Feature::create(['key' => 'advanced_analytics', 'name' => 'Advanced analytics', 'type' => Feature::TYPE_BOOLEAN, 'is_enabled' => true]);
        $plan = Plan::create(['name' => 'Business', 'price' => 0]);
        PlanFeature::create(['plan_id' => $plan->id, 'feature_id' => $feature->id, 'enabled' => true]);

        $business = Business::factory()->create();
        app(SubscriptionService::class)->subscribeToPlan($business, $plan);

        $this->assertTrue($this->features->enabled($business, 'advanced_analytics'));
    }

    public function test_while_subscription_system_is_on_a_business_with_no_subscription_falls_back_to_the_default_plan(): void
    {
        $this->settings->set('subscription.enabled', true);

        $feature = Feature::create(['key' => 'advanced_analytics', 'name' => 'Advanced analytics', 'type' => Feature::TYPE_BOOLEAN, 'is_enabled' => true]);
        $defaultPlan = Plan::create(['name' => 'Free', 'price' => 0, 'is_default' => true]);
        PlanFeature::create(['plan_id' => $defaultPlan->id, 'feature_id' => $feature->id, 'enabled' => false]);

        $business = Business::factory()->create(); // no subscription at all

        $this->assertFalse($this->features->enabled($business, 'advanced_analytics'));
    }

    public function test_limit_resolves_the_plans_numeric_value_including_unlimited(): void
    {
        $this->settings->set('subscription.enabled', true);

        $feature = Feature::create(['key' => 'products', 'name' => 'Products', 'type' => Feature::TYPE_LIMIT, 'is_enabled' => true]);
        $capped = Plan::create(['name' => 'Free', 'price' => 0]);
        $unlimited = Plan::create(['name' => 'Business', 'price' => 0]);
        PlanFeature::create(['plan_id' => $capped->id, 'feature_id' => $feature->id, 'enabled' => true, 'value' => 50]);
        PlanFeature::create(['plan_id' => $unlimited->id, 'feature_id' => $feature->id, 'enabled' => true, 'value' => null]);

        $cappedBusiness = Business::factory()->create();
        $unlimitedBusiness = Business::factory()->create();
        app(SubscriptionService::class)->subscribeToPlan($cappedBusiness, $capped);
        app(SubscriptionService::class)->subscribeToPlan($unlimitedBusiness, $unlimited);

        $this->assertSame(50, $this->features->limit($cappedBusiness, 'products'));
        $this->assertNull($this->features->limit($unlimitedBusiness, 'products'));
        $this->assertTrue($this->features->withinLimit($cappedBusiness, 'products', 49));
        $this->assertFalse($this->features->withinLimit($cappedBusiness, 'products', 50));
        $this->assertTrue($this->features->withinLimit($unlimitedBusiness, 'products', 999999));
    }
}
