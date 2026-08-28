<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Services\PlatformSettingsService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Covers the full "order via WhatsApp, seller requests payment, customer
 * pays" journey described in the product spec — see OrderController::
 * requestPayment(), PaymentService::initializeOrReuseForOrder(), and
 * OrderService's awaiting_payment handling.
 */
class WhatsAppOrderPaymentFlowTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $owner;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->business = Business::factory()->create(['currency' => 'NGN']);
        $this->owner = User::factory()->create(['business_id' => $this->business->id]);
        $this->owner->assignRole('Owner');
        $this->product = Product::factory()->create(['business_id' => $this->business->id, 'stock_quantity' => 10, 'price' => 5000]);
    }

    private function placeWhatsAppOrder(): Order
    {
        $this->post(route('storefront.cart.store', $this->business), ['product_id' => $this->product->id, 'quantity' => 1]);

        $this->post(route('storefront.checkout.store', $this->business), [
            'name' => 'Jane Doe',
            'phone' => '08011112222',
            'address' => 'Lagos',
            'payment_method' => 'whatsapp',
        ]);

        return Order::firstOrFail();
    }

    public function test_a_whatsapp_order_is_created_with_the_correct_customer_and_cart_data_and_source(): void
    {
        $order = $this->placeWhatsAppOrder();

        $this->assertSame('whatsapp', $order->source);
        $this->assertSame('pending', $order->order_status);
        $this->assertSame('pending', $order->payment_status);
        $this->assertSame('Jane Doe', $order->customer->name);
        $this->assertSame('08011112222', $order->customer->phone);
        $this->assertSame(1, $order->items()->count());
        $this->assertEquals(5000, $order->total);
    }

    public function test_seller_without_paystack_connected_gets_a_clear_prompt_not_a_fake_link(): void
    {
        $order = $this->placeWhatsAppOrder();

        $response = $this->actingAs($this->owner)->post(route('orders.request-payment', $order));

        $response->assertSessionHas('error');
        $this->assertSame('pending', $order->fresh()->order_status);
        $this->assertSame(0, Payment::where('order_id', $order->id)->count());
    }

    public function test_seller_can_confirm_the_order_and_a_payment_link_is_generated_for_the_exact_order(): void
    {
        $this->business->update(['paystack_subaccount_code' => 'ACCT_test123']);
        app(PlatformSettingsService::class)->set('commission.rate', 1.5);
        Http::fake(['api.paystack.co/*' => Http::response([
            'status' => true,
            'data' => ['authorization_url' => 'https://checkout.paystack.com/link123', 'access_code' => 'code', 'reference' => 'ignored'],
        ])]);

        $order = $this->placeWhatsAppOrder();

        $response = $this->actingAs($this->owner)->post(route('orders.request-payment', $order));

        $response->assertSessionHas('status');
        $fresh = $order->fresh();
        $this->assertSame('awaiting_payment', $fresh->order_status);
        $this->assertNull($fresh->inventory_deducted_at, 'Stock must not be deducted until payment succeeds.');

        $payment = Payment::where('order_id', $order->id)->firstOrFail();
        $this->assertSame($order->id, $payment->order_id);
        $this->assertSame($this->business->id, $payment->business_id);
        $this->assertSame('https://checkout.paystack.com/link123', $payment->authorization_url);
        $this->assertEquals(5000, $payment->amount);
    }

    public function test_the_generated_paystack_transaction_includes_the_correct_split_and_commission_snapshot(): void
    {
        $this->business->update(['paystack_subaccount_code' => 'ACCT_test123']);
        app(PlatformSettingsService::class)->set('commission.rate', 1.5);
        Http::fake(['api.paystack.co/*' => Http::response([
            'status' => true,
            'data' => ['authorization_url' => 'https://checkout.paystack.com/link123', 'access_code' => 'code', 'reference' => 'ignored'],
        ])]);

        $order = $this->placeWhatsAppOrder();
        $this->actingAs($this->owner)->post(route('orders.request-payment', $order));

        $payment = Payment::where('order_id', $order->id)->firstOrFail();
        $this->assertSame(1.5, $payment->commission_rate);
        // Order total ₦5000 = 500000 kobo; 1.5% = 7500 kobo.
        $this->assertSame(7500, $payment->commissionAmountInMinorUnits());
        $this->assertSame(492500, $payment->sellerAmountInMinorUnits());
        Http::assertSent(fn ($request) => ($request['subaccount'] ?? null) === 'ACCT_test123'
            && array_key_exists('transaction_charge', $request->data())
            && ($request['bearer'] ?? null) === 'account');
    }

    public function test_re_requesting_payment_reuses_the_existing_pending_link_instead_of_creating_a_new_transaction(): void
    {
        $this->business->update(['paystack_subaccount_code' => 'ACCT_test123']);
        Http::fake(['api.paystack.co/*' => Http::response([
            'status' => true,
            'data' => ['authorization_url' => 'https://checkout.paystack.com/link123', 'access_code' => 'code', 'reference' => 'ignored'],
        ])]);

        $order = $this->placeWhatsAppOrder();
        $this->actingAs($this->owner)->post(route('orders.request-payment', $order));
        $this->actingAs($this->owner)->post(route('orders.request-payment', $order));

        $this->assertSame(1, Payment::where('order_id', $order->id)->count());
    }

    public function test_a_confirmed_order_cannot_be_requested_for_payment_when_already_paid(): void
    {
        $this->business->update(['paystack_subaccount_code' => 'ACCT_test123']);

        $order = $this->placeWhatsAppOrder();
        $order->update(['payment_status' => 'paid']);

        $response = $this->actingAs($this->owner)->post(route('orders.request-payment', $order));

        $response->assertSessionHas('error');
        $this->assertSame(0, Payment::where('order_id', $order->id)->count());
    }

    public function test_no_frontend_supplied_commission_or_amount_values_are_ever_trusted(): void
    {
        $this->business->update(['paystack_subaccount_code' => 'ACCT_test123']);
        app(PlatformSettingsService::class)->set('commission.rate', 1.5);
        Http::fake(['api.paystack.co/*' => Http::response([
            'status' => true,
            'data' => ['authorization_url' => 'https://checkout.paystack.com/link123', 'access_code' => 'code', 'reference' => 'ignored'],
        ])]);

        $order = $this->placeWhatsAppOrder();

        // The route accepts no body at all — attempting to inject these is
        // structurally impossible to honor, which this test pins down.
        $this->actingAs($this->owner)->post(route('orders.request-payment', $order), [
            'commission_amount' => 1,
            'seller_amount' => 999999,
            'amount' => 1,
        ]);

        $payment = Payment::where('order_id', $order->id)->firstOrFail();
        $this->assertSame(1.5, $payment->commission_rate);
        $this->assertEquals(5000, $payment->amount);
    }

    public function test_successful_payment_marks_the_order_paid_and_deducts_inventory_exactly_once(): void
    {
        $this->business->update(['paystack_subaccount_code' => 'ACCT_test123']);
        Http::fake(['api.paystack.co/transaction/initialize' => Http::response([
            'status' => true,
            'data' => ['authorization_url' => 'https://checkout.paystack.com/link123', 'access_code' => 'code', 'reference' => 'ignored'],
        ])]);

        $order = $this->placeWhatsAppOrder();
        $this->actingAs($this->owner)->post(route('orders.request-payment', $order));
        $payment = Payment::where('order_id', $order->id)->firstOrFail();

        Http::fake(['api.paystack.co/transaction/verify/*' => Http::response([
            'status' => true,
            'data' => ['reference' => $payment->reference, 'status' => 'success', 'amount' => 500000],
        ])]);

        $response = $this->get(route('storefront.payments.callback', $this->business).'?reference='.$payment->reference);

        $response->assertRedirect(route('storefront.orders.confirmation', [$this->business, $order->public_token]));
        $fresh = $order->fresh();
        $this->assertSame('paid', $fresh->payment_status);
        $this->assertSame('confirmed', $fresh->order_status);
        $this->assertNotNull($fresh->inventory_deducted_at);
        $this->assertSame(9, $this->product->fresh()->stock_quantity);
    }

    public function test_a_duplicate_webhook_does_not_duplicate_payment_inventory_or_commission(): void
    {
        $this->business->update(['paystack_subaccount_code' => 'ACCT_test123']);
        Http::fake(['api.paystack.co/transaction/initialize' => Http::response([
            'status' => true,
            'data' => ['authorization_url' => 'https://checkout.paystack.com/link123', 'access_code' => 'code', 'reference' => 'ignored'],
        ])]);

        $order = $this->placeWhatsAppOrder();
        $this->actingAs($this->owner)->post(route('orders.request-payment', $order));
        $payment = Payment::where('order_id', $order->id)->firstOrFail();

        $verified = ['reference' => $payment->reference, 'status' => 'success', 'amount' => 500000];
        app(\App\Services\PaymentService::class)->handleVerifiedTransaction($verified);
        app(\App\Services\PaymentService::class)->handleVerifiedTransaction($verified); // simulated duplicate webhook

        $this->assertSame(9, $this->product->fresh()->stock_quantity, 'Stock must only be deducted once.');
        $this->assertSame(1, \App\Models\InventoryTransaction::where('type', 'sale')->count());
        $this->assertSame(1, Payment::where('order_id', $order->id)->count());
    }

    public function test_direct_paystack_checkout_continues_working_unaffected(): void
    {
        Http::fake(['api.paystack.co/transaction/initialize' => Http::response([
            'status' => true,
            'data' => ['authorization_url' => 'https://checkout.paystack.com/direct', 'access_code' => 'code', 'reference' => 'ignored'],
        ])]);

        $this->post(route('storefront.cart.store', $this->business), ['product_id' => $this->product->id, 'quantity' => 1]);
        $response = $this->post(route('storefront.checkout.store', $this->business), [
            'name' => 'John Doe', 'phone' => '08012345678', 'email' => 'john@example.com', 'address' => 'Lagos', 'payment_method' => 'paystack',
        ]);

        $response->assertRedirect('https://checkout.paystack.com/direct');
        $order = Order::firstOrFail();
        $this->assertSame('storefront', $order->source);
    }

    public function test_free_sellers_can_place_and_confirm_whatsapp_orders(): void
    {
        // No plan/subscription set up at all — matches a brand-new Free business.
        $this->business->update(['paystack_subaccount_code' => 'ACCT_test123']);
        Http::fake(['api.paystack.co/*' => Http::response([
            'status' => true,
            'data' => ['authorization_url' => 'https://checkout.paystack.com/link123', 'access_code' => 'code', 'reference' => 'ignored'],
        ])]);

        $order = $this->placeWhatsAppOrder();
        $response = $this->actingAs($this->owner)->post(route('orders.request-payment', $order));

        $response->assertSessionHas('status');
        $this->assertSame('awaiting_payment', $order->fresh()->order_status);
    }
}
