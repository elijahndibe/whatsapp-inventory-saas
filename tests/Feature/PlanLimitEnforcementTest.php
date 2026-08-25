<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Feature;
use App\Models\Plan;
use App\Models\PlanFeature;
use App\Models\Product;
use App\Models\User;
use App\Services\PlatformSettingsService;
use App\Services\SubscriptionService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Plan-tier limits only take effect once the platform-wide Subscription
 * System setting is on — see FeatureService. Every test here turns it on
 * explicitly, since that's what's actually being exercised.
 */
class PlanLimitEnforcementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PlatformSettingsService::class)->set('subscription.enabled', true);
    }

    private function planWithLimit(string $featureKey, ?int $value): Plan
    {
        $plan = Plan::create(['name' => 'Tiny', 'price' => 0]);
        $feature = Feature::firstOrCreate(['key' => $featureKey], ['name' => $featureKey, 'type' => Feature::TYPE_LIMIT, 'is_enabled' => true]);
        PlanFeature::create(['plan_id' => $plan->id, 'feature_id' => $feature->id, 'enabled' => true, 'value' => $value]);

        return $plan;
    }

    public function test_a_business_at_its_product_limit_cannot_create_another(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $plan = $this->planWithLimit('products', 1);
        $business = Business::factory()->create();
        app(SubscriptionService::class)->subscribeToPlan($business, $plan);
        Product::factory()->create(['business_id' => $business->id]);

        $owner = User::factory()->create(['business_id' => $business->id]);
        $owner->assignRole('Owner');

        $response = $this->actingAs($owner)->post(route('products.store'), [
            'name' => 'One Too Many',
            'price' => 100,
            'stock_quantity' => 1,
            'low_stock_threshold' => 1,
            'status' => 'active',
        ]);

        $response->assertSessionHas('error');
        $this->assertSame(1, Product::where('business_id', $business->id)->count());
    }

    public function test_a_storefront_order_is_rejected_once_the_monthly_limit_is_reached(): void
    {
        $plan = $this->planWithLimit('orders_per_month', 1);
        $business = Business::factory()->create();
        app(SubscriptionService::class)->subscribeToPlan($business, $plan);
        $customer = \App\Models\Customer::factory()->create(['business_id' => $business->id]);
        \App\Models\Order::create([
            'business_id' => $business->id, 'customer_id' => $customer->id,
            'subtotal' => 100, 'total' => 100, 'currency' => 'NGN',
        ]);

        $product = Product::factory()->create(['business_id' => $business->id, 'stock_quantity' => 10]);
        $this->post(route('storefront.cart.store', $business), ['product_id' => $product->id, 'quantity' => 1]);

        $response = $this->post(route('storefront.checkout.store', $business), [
            'name' => 'Jane', 'phone' => '08011112222', 'address' => 'Lagos', 'payment_method' => 'whatsapp',
        ]);

        $response->assertSessionHas('error');
        $this->assertSame(1, \App\Models\Order::where('business_id', $business->id)->count());
    }

    public function test_paystack_checkout_is_rejected_when_the_plan_lacks_the_feature(): void
    {
        $plan = Plan::create(['name' => 'NoPay', 'price' => 0]);
        $feature = Feature::firstOrCreate(['key' => 'paystack'], ['name' => 'Paystack', 'type' => Feature::TYPE_BOOLEAN, 'is_enabled' => true]);
        PlanFeature::create(['plan_id' => $plan->id, 'feature_id' => $feature->id, 'enabled' => false]);

        $business = Business::factory()->create();
        app(SubscriptionService::class)->subscribeToPlan($business, $plan);
        $product = Product::factory()->create(['business_id' => $business->id, 'stock_quantity' => 10]);
        $this->post(route('storefront.cart.store', $business), ['product_id' => $product->id, 'quantity' => 1]);

        $response = $this->post(route('storefront.checkout.store', $business), [
            'name' => 'Jane', 'phone' => '08011112222', 'email' => 'jane@example.com', 'address' => 'Lagos', 'payment_method' => 'paystack',
        ]);

        $response->assertSessionHas('error');
        $this->assertSame(0, \App\Models\Order::where('business_id', $business->id)->count());
    }

    public function test_a_business_within_its_limits_is_unaffected(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $plan = $this->planWithLimit('products', 10);
        $business = Business::factory()->create();
        app(SubscriptionService::class)->subscribeToPlan($business, $plan);

        $owner = User::factory()->create(['business_id' => $business->id]);
        $owner->assignRole('Owner');

        $response = $this->actingAs($owner)->post(route('products.store'), [
            'name' => 'Fits Fine',
            'price' => 100,
            'stock_quantity' => 1,
            'low_stock_threshold' => 1,
            'status' => 'active',
        ]);

        $response->assertRedirect();
        $this->assertSame(1, Product::where('business_id', $business->id)->count());
    }
}
