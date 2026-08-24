<?php

namespace Tests\Unit;

use App\Models\Business;
use App\Models\Customer;
use App\Models\InventoryTransaction;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Services\PaymentService;
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
}
