<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Feature;
use App\Models\Order;
use App\Models\Plan;
use App\Models\PlanFeature;
use App\Models\Product;
use App\Models\User;
use App\Services\PlatformSettingsService;
use App\Services\SubscriptionService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportsTest extends TestCase
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

    private function makePaidOrder(Product $product, int $quantity, string $paymentMethod = 'paystack'): Order
    {
        $customer = Customer::factory()->create(['business_id' => $this->business->id]);
        $order = Order::create([
            'business_id' => $this->business->id, 'customer_id' => $customer->id,
            'subtotal' => $product->price * $quantity, 'total' => $product->price * $quantity,
            'currency' => 'NGN', 'payment_status' => 'paid', 'payment_method' => $paymentMethod,
        ]);
        $order->items()->create([
            'product_id' => $product->id, 'product_name' => $product->name,
            'quantity' => $quantity, 'price' => $product->price, 'subtotal' => $product->price * $quantity,
        ]);

        return $order;
    }

    public function test_owner_can_view_the_reports_page(): void
    {
        $this->actingAs($this->owner)->get(route('reports.index'))->assertOk();
    }

    public function test_best_sellers_reflects_units_sold_across_orders(): void
    {
        $popular = Product::factory()->create(['business_id' => $this->business->id, 'name' => 'Popular Item', 'price' => 100]);
        $rare = Product::factory()->create(['business_id' => $this->business->id, 'name' => 'Rare Item', 'price' => 100]);
        $this->makePaidOrder($popular, 5);
        $this->makePaidOrder($popular, 3);
        $this->makePaidOrder($rare, 1);

        $response = $this->actingAs($this->owner)->get(route('reports.index'));

        $response->assertSeeTextInOrder(['Popular Item', 'Rare Item']);
    }

    private function planWithAdvancedAnalytics(bool $enabled): Plan
    {
        $plan = Plan::create(['name' => 'Test Plan', 'price' => 0]);
        $feature = Feature::firstOrCreate(['key' => 'advanced_analytics'], ['name' => 'Advanced analytics', 'type' => Feature::TYPE_BOOLEAN, 'is_enabled' => true]);
        PlanFeature::create(['plan_id' => $plan->id, 'feature_id' => $feature->id, 'enabled' => $enabled]);

        return $plan;
    }

    public function test_advanced_analytics_are_hidden_without_the_feature(): void
    {
        app(PlatformSettingsService::class)->set('subscription.enabled', true);
        $plan = $this->planWithAdvancedAnalytics(false);
        app(SubscriptionService::class)->subscribeToPlan($this->business, $plan);

        $response = $this->actingAs($this->owner)->get(route('reports.index'));

        $response->assertSeeText('available on the Business plan');
        $response->assertDontSee('id="categoryChart"', false);
    }

    public function test_advanced_analytics_are_shown_with_the_feature(): void
    {
        app(PlatformSettingsService::class)->set('subscription.enabled', true);
        $plan = $this->planWithAdvancedAnalytics(true);
        app(SubscriptionService::class)->subscribeToPlan($this->business, $plan);
        $product = Product::factory()->create(['business_id' => $this->business->id, 'price' => 100]);
        $this->makePaidOrder($product, 1);

        $response = $this->actingAs($this->owner)->get(route('reports.index'));

        $response->assertSee('id="categoryChart"', false);
        $response->assertSee('id="paymentChart"', false);
    }

    public function test_a_staff_member_without_permission_cannot_view_reports(): void
    {
        $staff = User::factory()->create(['business_id' => $this->business->id]);
        $staff->assignRole('Staff');

        $this->actingAs($staff)->get(route('reports.index'))->assertForbidden();
    }

    public function test_reports_are_scoped_to_the_current_business_only(): void
    {
        $otherBusiness = Business::factory()->create();
        $otherProduct = Product::factory()->create(['business_id' => $otherBusiness->id, 'name' => 'Someone Elses Product', 'price' => 100]);
        $otherCustomer = Customer::factory()->create(['business_id' => $otherBusiness->id]);
        $otherOrder = Order::create([
            'business_id' => $otherBusiness->id, 'customer_id' => $otherCustomer->id,
            'subtotal' => 100, 'total' => 100, 'currency' => 'NGN', 'payment_status' => 'paid',
        ]);
        $otherOrder->items()->create(['product_id' => $otherProduct->id, 'product_name' => $otherProduct->name, 'quantity' => 1, 'price' => 100, 'subtotal' => 100]);

        $response = $this->actingAs($this->owner)->get(route('reports.index'));

        $response->assertDontSeeText('Someone Elses Product');
    }
}
