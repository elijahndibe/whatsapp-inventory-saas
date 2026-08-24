<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PaystackCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_choosing_paystack_at_checkout_redirects_to_the_paystack_checkout_url(): void
    {
        Http::fake([
            'api.paystack.co/transaction/initialize' => Http::response([
                'status' => true,
                'data' => ['authorization_url' => 'https://checkout.paystack.com/xyz', 'access_code' => 'code', 'reference' => 'ignored'],
            ]),
        ]);

        $business = Business::factory()->create();
        $product = Product::factory()->create(['business_id' => $business->id, 'stock_quantity' => 10]);
        $this->post(route('storefront.cart.store', $business), ['product_id' => $product->id, 'quantity' => 1]);

        $response = $this->post(route('storefront.checkout.store', $business), [
            'name' => 'John Doe',
            'phone' => '08012345678',
            'email' => 'john@example.com',
            'address' => 'Lagos',
            'payment_method' => 'paystack',
        ]);

        $response->assertRedirect('https://checkout.paystack.com/xyz');
        $this->assertSame('paystack', Order::first()->payment_method);
        $this->assertSame(1, Payment::count());
    }

    public function test_email_is_required_when_paying_online(): void
    {
        $business = Business::factory()->create();
        $product = Product::factory()->create(['business_id' => $business->id, 'stock_quantity' => 10]);
        $this->post(route('storefront.cart.store', $business), ['product_id' => $product->id, 'quantity' => 1]);

        $response = $this->post(route('storefront.checkout.store', $business), [
            'name' => 'John Doe',
            'phone' => '08012345678',
            'address' => 'Lagos',
            'payment_method' => 'paystack',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertSame(0, Order::count());
    }

    public function test_the_callback_verifies_and_confirms_a_successful_payment(): void
    {
        Http::fake([
            'api.paystack.co/transaction/initialize' => Http::response([
                'status' => true,
                'data' => ['authorization_url' => 'https://checkout.paystack.com/xyz', 'access_code' => 'code', 'reference' => 'ignored'],
            ]),
        ]);

        $business = Business::factory()->create();
        $product = Product::factory()->create(['business_id' => $business->id, 'stock_quantity' => 10, 'price' => 100]);
        $this->post(route('storefront.cart.store', $business), ['product_id' => $product->id, 'quantity' => 1]);
        $this->post(route('storefront.checkout.store', $business), [
            'name' => 'John Doe',
            'phone' => '08012345678',
            'email' => 'john@example.com',
            'address' => 'Lagos',
            'payment_method' => 'paystack',
        ]);

        $payment = Payment::firstOrFail();
        $order = Order::firstOrFail();

        Http::fake([
            'api.paystack.co/transaction/verify/*' => Http::response([
                'status' => true,
                'data' => ['reference' => $payment->reference, 'status' => 'success', 'amount' => 10000],
            ]),
        ]);

        $response = $this->get(route('storefront.payments.callback', $business).'?reference='.$payment->reference);

        $response->assertRedirect(route('storefront.orders.confirmation', [$business, $order->public_token]));
        $this->assertSame('paid', $order->fresh()->payment_status);
        $this->assertSame(9, $product->fresh()->stock_quantity);

        $confirmationResponse = $this->get(route('storefront.orders.confirmation', [$business, $order->public_token]));
        $confirmationResponse->assertSeeText('Payment Received');
    }

    public function test_retrying_payment_for_an_already_paid_order_does_not_create_a_new_attempt(): void
    {
        Http::fake([
            'api.paystack.co/*' => Http::response(['status' => true, 'data' => ['authorization_url' => 'https://checkout.paystack.com/new', 'access_code' => 'x', 'reference' => 'x']]),
        ]);

        $business = Business::factory()->create();
        $product = Product::factory()->create(['business_id' => $business->id, 'stock_quantity' => 10]);
        $this->post(route('storefront.cart.store', $business), ['product_id' => $product->id, 'quantity' => 1]);
        $this->post(route('storefront.checkout.store', $business), [
            'name' => 'John Doe', 'phone' => '08012345678', 'email' => 'john@example.com', 'address' => 'Lagos', 'payment_method' => 'paystack',
        ]);
        $order = Order::firstOrFail();
        $order->update(['payment_status' => 'paid']);

        $response = $this->post(route('storefront.payments.retry', [$business, $order->public_token]));

        $response->assertRedirect(route('storefront.orders.confirmation', [$business, $order->public_token]));
        $this->assertSame(1, Payment::count(), 'No new payment attempt should be created for an order already paid.');
    }

    public function test_retrying_payment_for_an_unpaid_order_creates_a_new_attempt(): void
    {
        Http::fake([
            'api.paystack.co/*' => Http::response(['status' => true, 'data' => ['authorization_url' => 'https://checkout.paystack.com/new', 'access_code' => 'x', 'reference' => 'x']]),
        ]);

        $business = Business::factory()->create();
        $product = Product::factory()->create(['business_id' => $business->id, 'stock_quantity' => 10]);
        $this->post(route('storefront.cart.store', $business), ['product_id' => $product->id, 'quantity' => 1]);
        $this->post(route('storefront.checkout.store', $business), [
            'name' => 'John Doe', 'phone' => '08012345678', 'email' => 'john@example.com', 'address' => 'Lagos', 'payment_method' => 'paystack',
        ]);
        $order = Order::firstOrFail();

        $response = $this->post(route('storefront.payments.retry', [$business, $order->public_token]));

        $response->assertRedirect('https://checkout.paystack.com/new');
        $this->assertSame(2, Payment::count());
    }
}
