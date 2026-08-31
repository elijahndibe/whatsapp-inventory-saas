<?php

namespace Tests\Unit;

use App\Models\Business;
use App\Models\Customer;
use App\Models\InventoryTransaction;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Services\PaymentService;
use App\Services\PlatformSettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    private PaymentService $service;
    private Business $business;
    private Product $product;
    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(PaymentService::class);
        $this->business = Business::factory()->create(['currency' => 'NGN']);
        $this->product = Product::factory()->create(['business_id' => $this->business->id, 'stock_quantity' => 10, 'price' => 500]);
        $customer = Customer::factory()->create(['business_id' => $this->business->id]);

        $this->order = Order::create([
            'business_id' => $this->business->id,
            'customer_id' => $customer->id,
            'subtotal' => 500,
            'total' => 500,
            'currency' => 'NGN',
            'payment_method' => 'paystack',
        ]);
        $this->order->items()->create([
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'quantity' => 2,
            'price' => 250,
            'subtotal' => 500,
        ]);
    }

    public function test_initialize_for_order_creates_a_pending_payment_and_returns_a_checkout_url(): void
    {
        Http::fake([
            'api.paystack.co/*' => Http::response([
                'status' => true,
                'data' => ['authorization_url' => 'https://checkout.paystack.com/xyz', 'access_code' => 'code', 'reference' => 'ignored'],
            ]),
        ]);

        $result = $this->service->initializeForOrder($this->order, 'buyer@example.com');

        $this->assertSame('https://checkout.paystack.com/xyz', $result['authorization_url']);
        $this->assertSame('pending', $result['payment']->status);
        $this->assertSame($this->order->id, $result['payment']->order_id);
        $this->assertEquals(500, $result['payment']->amount);

        Http::assertSent(fn ($request) => ($request['amount'] ?? null) === 50000); // minor units
    }

    public function test_commission_is_calculated_server_side_and_snapshotted_on_the_payment(): void
    {
        Http::fake(['api.paystack.co/*' => Http::response([
            'status' => true,
            'data' => ['authorization_url' => 'https://checkout.paystack.com/xyz', 'access_code' => 'code', 'reference' => 'ignored'],
        ])]);

        app(PlatformSettingsService::class)->set('commission.rate', 1.5);

        $result = $this->service->initializeForOrder($this->order, 'buyer@example.com');

        $payment = $result['payment']->fresh();
        $this->assertSame(1.5, $payment->commission_rate);
        // Order total is 500 (₦500 = 50000 kobo); 1.5% = 750 kobo.
        $this->assertSame(750, $payment->commissionAmountInMinorUnits());
        $this->assertSame(49250, $payment->sellerAmountInMinorUnits());
    }

    public function test_a_payments_commission_snapshot_is_unaffected_by_a_later_platform_rate_change(): void
    {
        Http::fake(['api.paystack.co/*' => Http::response([
            'status' => true,
            'data' => ['authorization_url' => 'https://checkout.paystack.com/xyz', 'access_code' => 'code', 'reference' => 'ignored'],
        ])]);

        $settings = app(PlatformSettingsService::class);
        $settings->set('commission.rate', 1.5);

        $result = $this->service->initializeForOrder($this->order, 'buyer@example.com');
        $payment = $result['payment'];

        $settings->set('commission.rate', 1.0); // simulates the rate changing months later

        $this->assertSame(1.5, $payment->fresh()->commission_rate, 'A stored payment must keep the rate that applied when it was created.');
    }

    public function test_split_params_are_sent_when_the_business_has_a_connected_paystack_subaccount(): void
    {
        Http::fake(['api.paystack.co/*' => Http::response([
            'status' => true,
            'data' => ['authorization_url' => 'https://checkout.paystack.com/xyz', 'access_code' => 'code', 'reference' => 'ignored'],
        ])]);

        $this->business->update(['paystack_subaccount_code' => 'ACCT_test123']);
        app(PlatformSettingsService::class)->set('commission.rate', 1.5);

        $this->service->initializeForOrder($this->order->fresh(), 'buyer@example.com');

        Http::assertSent(fn ($request) => ($request['subaccount'] ?? null) === 'ACCT_test123'
            && ($request['bearer'] ?? null) === 'account'
            && array_key_exists('transaction_charge', $request->data()));
    }

    public function test_no_split_params_are_sent_without_a_connected_subaccount(): void
    {
        Http::fake(['api.paystack.co/*' => Http::response([
            'status' => true,
            'data' => ['authorization_url' => 'https://checkout.paystack.com/xyz', 'access_code' => 'code', 'reference' => 'ignored'],
        ])]);

        $this->service->initializeForOrder($this->order, 'buyer@example.com');

        Http::assertSent(fn ($request) => ! array_key_exists('subaccount', $request->data()));
    }

    public function test_handling_a_successful_verified_transaction_marks_payment_and_order_paid_and_deducts_stock(): void
    {
        $payment = Payment::create([
            'business_id' => $this->business->id,
            'order_id' => $this->order->id,
            'reference' => 'PAY-TEST1',
            'gateway' => 'paystack',
            'amount' => 500,
            'currency' => 'NGN',
            'status' => 'pending',
        ]);

        $this->service->handleVerifiedTransaction([
            'reference' => 'PAY-TEST1',
            'status' => 'success',
            'amount' => 50000, // must match payment's raw minor-unit amount
        ]);

        $this->assertSame('success', $payment->fresh()->status);
        $this->assertNotNull($payment->fresh()->paid_at);

        $order = $this->order->fresh();
        $this->assertSame('paid', $order->payment_status);
        $this->assertSame('confirmed', $order->order_status);
        $this->assertNotNull($order->inventory_deducted_at);
        $this->assertSame(8, $this->product->fresh()->stock_quantity);
        $this->assertSame(1, InventoryTransaction::where('type', 'sale')->count());
    }

    public function test_a_second_call_for_the_same_reference_is_a_no_op(): void
    {
        $payment = Payment::create([
            'business_id' => $this->business->id,
            'order_id' => $this->order->id,
            'reference' => 'PAY-TEST2',
            'gateway' => 'paystack',
            'amount' => 500,
            'currency' => 'NGN',
            'status' => 'pending',
        ]);

        $verified = ['reference' => 'PAY-TEST2', 'status' => 'success', 'amount' => 50000];

        $this->service->handleVerifiedTransaction($verified);
        $this->service->handleVerifiedTransaction($verified); // simulates webhook + callback race, or a webhook retry

        $this->assertSame(8, $this->product->fresh()->stock_quantity, 'Stock must only be deducted once.');
        $this->assertSame(1, InventoryTransaction::where('type', 'sale')->count());
    }

    public function test_an_amount_mismatch_is_marked_failed_and_does_not_pay_the_order(): void
    {
        Payment::create([
            'business_id' => $this->business->id,
            'order_id' => $this->order->id,
            'reference' => 'PAY-TEST3',
            'gateway' => 'paystack',
            'amount' => 500,
            'currency' => 'NGN',
            'status' => 'pending',
        ]);

        $this->service->handleVerifiedTransaction([
            'reference' => 'PAY-TEST3',
            'status' => 'success',
            'amount' => 10000, // does not match the 50000 the payment expects
        ]);

        $this->assertSame('failed', Payment::where('reference', 'PAY-TEST3')->first()->status);
        $this->assertSame('pending', $this->order->fresh()->payment_status);
        $this->assertSame(10, $this->product->fresh()->stock_quantity, 'Stock must not be touched on a rejected payment.');
    }

    public function test_a_failed_gateway_status_is_recorded_without_paying_the_order(): void
    {
        Payment::create([
            'business_id' => $this->business->id,
            'order_id' => $this->order->id,
            'reference' => 'PAY-TEST4',
            'gateway' => 'paystack',
            'amount' => 500,
            'currency' => 'NGN',
            'status' => 'pending',
        ]);

        $this->service->handleVerifiedTransaction([
            'reference' => 'PAY-TEST4',
            'status' => 'failed',
            'amount' => 50000,
        ]);

        $this->assertSame('failed', Payment::where('reference', 'PAY-TEST4')->first()->status);
        $this->assertSame('pending', $this->order->fresh()->payment_status);
    }

    public function test_a_full_refund_marks_the_order_refunded_without_touching_stock_or_commission(): void
    {
        $payment = Payment::create([
            'business_id' => $this->business->id,
            'order_id' => $this->order->id,
            'reference' => 'PAY-REFUND1',
            'gateway' => 'paystack',
            'amount' => 500,
            'currency' => 'NGN',
            'status' => 'success',
            'paid_at' => now(),
            'commission_rate' => 1.5,
            'commission_amount' => 7.5,
            'seller_amount' => 492.5,
        ]);
        $this->order->update(['payment_status' => 'paid', 'order_status' => 'confirmed', 'inventory_deducted_at' => now()]);
        $this->product->decrement('stock_quantity', 2); // mirrors the deduction that already happened at payment time

        $this->service->handleRefund('PAY-REFUND1', 50000);

        $payment = $payment->fresh();
        $this->assertEquals(500, $payment->refunded_amount);
        $this->assertNotNull($payment->refunded_at);
        $this->assertTrue($payment->isFullyRefunded());
        $this->assertSame('refunded', $this->order->fresh()->payment_status);

        // Commission is kept regardless — not clawed back on refund.
        $this->assertEquals(7.5, $payment->commission_amount);
        $this->assertEquals(492.5, $payment->seller_amount);

        // Stock is never auto-restored — a refund doesn't imply a return.
        $this->assertSame(8, $this->product->fresh()->stock_quantity);
    }

    public function test_a_partial_refund_marks_the_order_partially_refunded(): void
    {
        Payment::create([
            'business_id' => $this->business->id,
            'order_id' => $this->order->id,
            'reference' => 'PAY-REFUND2',
            'gateway' => 'paystack',
            'amount' => 500,
            'currency' => 'NGN',
            'status' => 'success',
            'paid_at' => now(),
        ]);
        $this->order->update(['payment_status' => 'paid']);

        $this->service->handleRefund('PAY-REFUND2', 20000); // ₦200 of ₦500

        $payment = Payment::where('reference', 'PAY-REFUND2')->first();
        $this->assertEquals(200, $payment->refunded_amount);
        $this->assertFalse($payment->isFullyRefunded());
        $this->assertSame('partially_refunded', $this->order->fresh()->payment_status);
    }

    public function test_a_repeated_refund_webhook_for_the_same_amount_is_a_no_op(): void
    {
        Payment::create([
            'business_id' => $this->business->id,
            'order_id' => $this->order->id,
            'reference' => 'PAY-REFUND3',
            'gateway' => 'paystack',
            'amount' => 500,
            'currency' => 'NGN',
            'status' => 'success',
            'paid_at' => now(),
        ]);
        $this->order->update(['payment_status' => 'paid']);

        $this->service->handleRefund('PAY-REFUND3', 50000);
        $firstRefundedAt = Payment::where('reference', 'PAY-REFUND3')->first()->refunded_at;

        $this->service->handleRefund('PAY-REFUND3', 50000); // duplicate webhook

        $payment = Payment::where('reference', 'PAY-REFUND3')->first();
        $this->assertEquals(500, $payment->refunded_amount, 'Refunded amount must not double-count on a repeated webhook.');
        $this->assertEquals($firstRefundedAt, $payment->refunded_at);
    }

    public function test_a_refund_for_a_payment_that_was_never_successful_is_ignored(): void
    {
        Payment::create([
            'business_id' => $this->business->id,
            'order_id' => $this->order->id,
            'reference' => 'PAY-REFUND4',
            'gateway' => 'paystack',
            'amount' => 500,
            'currency' => 'NGN',
            'status' => 'pending',
        ]);

        $this->service->handleRefund('PAY-REFUND4', 50000);

        $payment = Payment::where('reference', 'PAY-REFUND4')->first();
        $this->assertNull($payment->refunded_at);
        $this->assertSame('pending', $this->order->fresh()->payment_status);
    }
}
