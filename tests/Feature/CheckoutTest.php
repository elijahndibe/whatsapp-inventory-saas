<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_creates_a_customer_and_a_pending_order(): void
    {
        $business = Business::factory()->create();
        $product = Product::factory()->create(['business_id' => $business->id, 'name' => 'Ankara Dress', 'price' => 15000, 'stock_quantity' => 10]);
        $this->post(route('storefront.cart.store', $business), ['product_id' => $product->id, 'quantity' => 2]);

        $response = $this->post(route('storefront.checkout.store', $business), [
            'name' => 'John Doe',
            'phone' => '08012345678',
            'address' => '12 Lagos Street',
            'city' => 'Lagos',
        ]);

        $order = Order::where('business_id', $business->id)->firstOrFail();
        $response->assertRedirect(route('storefront.orders.confirmation', [$business, $order->public_token]));

        $this->assertSame('John Doe', $order->customer->name);
        $this->assertSame('08012345678', $order->customer->phone);
        $this->assertSame('pending', $order->order_status);
        $this->assertSame('pending', $order->payment_status);
        $this->assertSame('whatsapp', $order->payment_method);
        $this->assertEquals(30000, $order->subtotal); // 15000 * 2
        $this->assertSame(1, $order->items()->count());
        $this->assertSame(2, $order->items()->first()->quantity);
        $this->assertSame('Ankara Dress', $order->items()->first()->product_name);
    }

    public function test_checkout_clears_the_cart(): void
    {
        $business = Business::factory()->create();
        $product = Product::factory()->create(['business_id' => $business->id, 'stock_quantity' => 10]);
        $this->post(route('storefront.cart.store', $business), ['product_id' => $product->id, 'quantity' => 1]);

        $this->post(route('storefront.checkout.store', $business), [
            'name' => 'Jane Doe',
            'phone' => '08099999999',
            'address' => 'Somewhere',
        ]);

        $cartResponse = $this->get(route('storefront.cart.index', $business));
        $cartResponse->assertSeeText('Your cart is empty');
    }

    public function test_checkout_with_an_empty_cart_redirects_back_to_the_cart(): void
    {
        $business = Business::factory()->create();

        $response = $this->post(route('storefront.checkout.store', $business), [
            'name' => 'Jane Doe',
            'phone' => '08099999999',
            'address' => 'Somewhere',
        ]);

        $response->assertRedirect(route('storefront.cart.index', $business));
        $this->assertSame(0, Order::count());
    }

    public function test_repeat_customer_by_phone_number_is_reused_not_duplicated(): void
    {
        $business = Business::factory()->create();
        $existing = Customer::factory()->create(['business_id' => $business->id, 'phone' => '08011112222', 'name' => 'Old Name']);
        $product = Product::factory()->create(['business_id' => $business->id, 'stock_quantity' => 10]);
        $this->post(route('storefront.cart.store', $business), ['product_id' => $product->id, 'quantity' => 1]);

        $this->post(route('storefront.checkout.store', $business), [
            'name' => 'New Name',
            'phone' => '08011112222',
            'address' => 'New Address',
        ]);

        $this->assertSame(1, Customer::where('business_id', $business->id)->count());
        $this->assertSame('New Name', $existing->fresh()->name);
    }

    public function test_checkout_is_rejected_when_stock_is_insufficient(): void
    {
        $business = Business::factory()->create(['allow_overselling' => false]);
        $product = Product::factory()->create(['business_id' => $business->id, 'stock_quantity' => 1]);
        $this->post(route('storefront.cart.store', $business), ['product_id' => $product->id, 'quantity' => 1]);

        // Someone else buys the last unit between add-to-cart and checkout.
        $product->update(['stock_quantity' => 0]);

        $response = $this->post(route('storefront.checkout.store', $business), [
            'name' => 'Jane Doe',
            'phone' => '08099999999',
            'address' => 'Somewhere',
        ]);

        $response->assertRedirect(route('storefront.cart.index', $business));
        $this->assertSame(0, Order::count());
    }

    public function test_name_phone_and_address_are_required(): void
    {
        $business = Business::factory()->create();
        $product = Product::factory()->create(['business_id' => $business->id, 'stock_quantity' => 10]);
        $this->post(route('storefront.cart.store', $business), ['product_id' => $product->id, 'quantity' => 1]);

        $response = $this->post(route('storefront.checkout.store', $business), []);

        $response->assertSessionHasErrors(['name', 'phone', 'address']);
    }

    public function test_the_confirmation_page_shows_a_whatsapp_link_when_the_business_has_a_number(): void
    {
        $business = Business::factory()->create(['whatsapp_number' => '2348012345678']);
        $product = Product::factory()->create(['business_id' => $business->id, 'name' => 'Ankara Dress', 'stock_quantity' => 10]);
        $this->post(route('storefront.cart.store', $business), ['product_id' => $product->id, 'quantity' => 1]);
        $this->post(route('storefront.checkout.store', $business), [
            'name' => 'John Doe',
            'phone' => '08012345678',
            'address' => 'Lagos',
        ]);
        $order = Order::firstOrFail();

        $response = $this->get(route('storefront.orders.confirmation', [$business, $order->public_token]));

        $response->assertOk();
        $response->assertSeeText('Order Received');
        $response->assertSeeText($order->order_number);
        $response->assertSee('https://wa.me/2348012345678', false);
    }

    public function test_an_order_confirmation_cannot_be_viewed_via_another_businesss_storefront(): void
    {
        $businessA = Business::factory()->create();
        $businessB = Business::factory()->create();
        $product = Product::factory()->create(['business_id' => $businessA->id, 'stock_quantity' => 10]);
        $this->post(route('storefront.cart.store', $businessA), ['product_id' => $product->id, 'quantity' => 1]);
        $this->post(route('storefront.checkout.store', $businessA), [
            'name' => 'John Doe',
            'phone' => '08012345678',
            'address' => 'Lagos',
        ]);
        $order = Order::firstOrFail();

        $response = $this->get(route('storefront.orders.confirmation', [$businessB, $order->public_token]));

        $response->assertNotFound();
    }
}
