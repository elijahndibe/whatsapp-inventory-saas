<?php

namespace Tests\Unit;

use App\Jobs\ProcessPaystackWebhook;
use App\Models\Business;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ProcessPaystackWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_job_verifies_with_paystack_and_applies_the_result(): void
    {
        $business = Business::factory()->create();
        $customer = Customer::factory()->create(['business_id' => $business->id]);
        $order = Order::create([
            'business_id' => $business->id,
            'customer_id' => $customer->id,
            'subtotal' => 100,
            'total' => 100,
            'currency' => 'NGN',
        ]);
        Payment::create([
            'business_id' => $business->id,
            'order_id' => $order->id,
            'reference' => 'PAY-JOBTEST',
            'gateway' => 'paystack',
            'amount' => 100,
            'currency' => 'NGN',
            'status' => 'pending',
        ]);

        Http::fake([
            'api.paystack.co/transaction/verify/*' => Http::response([
                'status' => true,
                'data' => ['reference' => 'PAY-JOBTEST', 'status' => 'success', 'amount' => 10000],
            ]),
        ]);

        (new ProcessPaystackWebhook('PAY-JOBTEST'))->handle(app(\App\Services\PaystackService::class), app(\App\Services\PaymentService::class));

        $this->assertSame('paid', $order->fresh()->payment_status);
        $this->assertSame('success', Payment::where('reference', 'PAY-JOBTEST')->first()->status);
    }
}
