<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontCouponTest extends TestCase
{
    use RefreshDatabase;

    public function test_applying_a_valid_coupon_shows_the_discount_on_the_cart_page(): void
    {
        $business = Business::factory()->create();
        $product = Product::factory()->create(['business_id' => $business->id, 'price' => 1000, 'stock_quantity' => 10]);
        Coupon::create(['business_id' => $business->id, 'code' => 'SAVE10', 'type' => Coupon::TYPE_PERCENTAGE, 'value' => 10, 'is_active' => true]);

        $this->post(route('storefront.cart.store', $business), ['product_id' => $product->id, 'quantity' => 1]);
        $this->post(route('storefront.cart.coupon.apply', $business), ['code' => 'save10']);

        $response = $this->get(route('storefront.cart.index', $business));

        $response->assertSeeText('SAVE10');
        $response->assertSeeText('100.00'); // 10% of ₦1,000
    }

    public function test_an_invalid_coupon_code_is_rejected_with_an_error(): void
    {
        $business = Business::factory()->create();
        $product = Product::factory()->create(['business_id' => $business->id, 'stock_quantity' => 10]);
        $this->post(route('storefront.cart.store', $business), ['product_id' => $product->id, 'quantity' => 1]);

        $response = $this->post(route('storefront.cart.coupon.apply', $business), ['code' => 'NOPE']);

        $response->assertSessionHas('error');
    }

    public function test_checkout_applies_the_coupon_discount_to_the_order_total(): void
    {
        $business = Business::factory()->create();
        $product = Product::factory()->create(['business_id' => $business->id, 'price' => 1000, 'stock_quantity' => 10]);
        $coupon = Coupon::create(['business_id' => $business->id, 'code' => 'SAVE10', 'type' => Coupon::TYPE_PERCENTAGE, 'value' => 10, 'is_active' => true]);

        $this->post(route('storefront.cart.store', $business), ['product_id' => $product->id, 'quantity' => 1]);
        $this->post(route('storefront.cart.coupon.apply', $business), ['code' => 'SAVE10']);

        $this->post(route('storefront.checkout.store', $business), [
            'name' => 'John Doe',
            'phone' => '08012345678',
            'address' => 'Lagos',
            'payment_method' => 'whatsapp',
        ]);

        $order = Order::where('business_id', $business->id)->firstOrFail();

        $this->assertEquals(1000, $order->subtotal);
        $this->assertEquals(100, $order->discount);
        $this->assertEquals(900, $order->total);
        $this->assertSame($coupon->id, $order->coupon_id);
        $this->assertSame('SAVE10', $order->coupon_code);
        $this->assertSame(1, $coupon->fresh()->times_used);
    }

    public function test_checkout_clears_the_applied_coupon_along_with_the_cart(): void
    {
        $business = Business::factory()->create();
        $product = Product::factory()->create(['business_id' => $business->id, 'stock_quantity' => 10]);
        Coupon::create(['business_id' => $business->id, 'code' => 'SAVE10', 'type' => Coupon::TYPE_PERCENTAGE, 'value' => 10, 'is_active' => true]);
        $this->post(route('storefront.cart.store', $business), ['product_id' => $product->id, 'quantity' => 1]);
        $this->post(route('storefront.cart.coupon.apply', $business), ['code' => 'SAVE10']);

        $this->post(route('storefront.checkout.store', $business), [
            'name' => 'John Doe', 'phone' => '08012345678', 'address' => 'Lagos', 'payment_method' => 'whatsapp',
        ]);

        $response = $this->get(route('storefront.cart.index', $business));
        $response->assertDontSeeText('SAVE10');
    }

    public function test_a_coupon_that_reaches_its_usage_limit_between_cart_and_checkout_blocks_the_order_and_flashes_an_error(): void
    {
        $business = Business::factory()->create();
        $product = Product::factory()->create(['business_id' => $business->id, 'stock_quantity' => 10]);
        Coupon::create(['business_id' => $business->id, 'code' => 'ONEUSE', 'type' => Coupon::TYPE_PERCENTAGE, 'value' => 10, 'usage_limit' => 1, 'times_used' => 1, 'is_active' => true]);
        // Applying it optimistically succeeds at cart time only if it were
        // still valid — force the session to hold a code that's already
        // maxed out, simulating a race between two customers.
        $this->post(route('storefront.cart.store', $business), ['product_id' => $product->id, 'quantity' => 1]);
        $this->withSession(["cart_coupon.{$business->id}" => 'ONEUSE']);

        $response = $this->post(route('storefront.checkout.store', $business), [
            'name' => 'John Doe', 'phone' => '08012345678', 'address' => 'Lagos', 'payment_method' => 'whatsapp',
        ]);

        $response->assertRedirect(route('storefront.cart.index', $business));
        $this->assertSame(0, Order::count());
    }

    public function test_a_customer_cannot_reuse_a_per_customer_limited_coupon_on_a_second_order(): void
    {
        $business = Business::factory()->create();
        $product = Product::factory()->create(['business_id' => $business->id, 'stock_quantity' => 10]);
        Coupon::create(['business_id' => $business->id, 'code' => 'ONCE', 'type' => Coupon::TYPE_FIXED, 'value' => 100, 'usage_limit_per_customer' => 1, 'is_active' => true]);

        // First order — succeeds and redeems the coupon.
        $this->post(route('storefront.cart.store', $business), ['product_id' => $product->id, 'quantity' => 1]);
        $this->post(route('storefront.cart.coupon.apply', $business), ['code' => 'ONCE']);
        $this->post(route('storefront.checkout.store', $business), [
            'name' => 'John Doe', 'phone' => '08012345678', 'address' => 'Lagos', 'payment_method' => 'whatsapp',
        ]);
        $this->assertSame(1, Order::count());

        // Same customer, second order, same coupon.
        $this->post(route('storefront.cart.store', $business), ['product_id' => $product->id, 'quantity' => 1]);
        $this->post(route('storefront.cart.coupon.apply', $business), ['code' => 'ONCE']);
        $response = $this->post(route('storefront.checkout.store', $business), [
            'name' => 'John Doe', 'phone' => '08012345678', 'address' => 'Lagos', 'payment_method' => 'whatsapp',
        ]);

        $response->assertRedirect(route('storefront.cart.index', $business));
        $this->assertSame(1, Order::count(), 'The second order must not be created once the coupon is exhausted for this customer.');
    }
}
