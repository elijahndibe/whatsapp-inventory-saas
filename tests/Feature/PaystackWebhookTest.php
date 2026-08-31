<?php

namespace Tests\Feature;

use App\Jobs\ProcessPaystackRefundWebhook;
use App\Jobs\ProcessPaystackWebhook;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class PaystackWebhookTest extends TestCase
{
    use RefreshDatabase;

    private function sign(string $body): string
    {
        return hash_hmac('sha512', $body, config('services.paystack.secret_key'));
    }

    public function test_a_valid_signature_dispatches_the_processing_job(): void
    {
        Bus::fake();

        $payload = ['event' => 'charge.success', 'data' => ['reference' => 'PAY-ABC123']];
        $body = json_encode($payload);

        $response = $this->call('POST', route('webhooks.paystack'), [], [], [], [
            'HTTP_x-paystack-signature' => $this->sign($body),
            'CONTENT_TYPE' => 'application/json',
        ], $body);

        $response->assertOk();
        Bus::assertDispatched(ProcessPaystackWebhook::class, fn ($job) => $job->reference === 'PAY-ABC123');
    }

    public function test_an_invalid_signature_is_rejected_and_nothing_is_dispatched(): void
    {
        Bus::fake();

        $payload = ['event' => 'charge.success', 'data' => ['reference' => 'PAY-ABC123']];
        $body = json_encode($payload);

        $response = $this->call('POST', route('webhooks.paystack'), [], [], [], [
            'HTTP_x-paystack-signature' => 'not-the-right-signature',
            'CONTENT_TYPE' => 'application/json',
        ], $body);

        $response->assertStatus(400);
        Bus::assertNotDispatched(ProcessPaystackWebhook::class);
    }

    public function test_a_missing_signature_header_is_rejected(): void
    {
        Bus::fake();

        $response = $this->postJson(route('webhooks.paystack'), ['event' => 'charge.success', 'data' => ['reference' => 'X']]);

        $response->assertStatus(400);
        Bus::assertNotDispatched(ProcessPaystackWebhook::class);
    }

    public function test_an_unrelated_event_type_is_acknowledged_but_not_processed(): void
    {
        Bus::fake();

        $payload = ['event' => 'transfer.success', 'data' => ['reference' => 'PAY-ABC123']];
        $body = json_encode($payload);

        $response = $this->call('POST', route('webhooks.paystack'), [], [], [], [
            'HTTP_x-paystack-signature' => $this->sign($body),
            'CONTENT_TYPE' => 'application/json',
        ], $body);

        $response->assertOk();
        Bus::assertNotDispatched(ProcessPaystackWebhook::class);
    }

    public function test_a_refund_processed_event_dispatches_the_refund_job(): void
    {
        Bus::fake();

        $payload = ['event' => 'refund.processed', 'data' => [
            'amount' => 20000,
            'transaction' => ['reference' => 'PAY-REFUNDME'],
        ]];
        $body = json_encode($payload);

        $response = $this->call('POST', route('webhooks.paystack'), [], [], [], [
            'HTTP_x-paystack-signature' => $this->sign($body),
            'CONTENT_TYPE' => 'application/json',
        ], $body);

        $response->assertOk();
        Bus::assertDispatched(ProcessPaystackRefundWebhook::class, fn ($job) => $job->transactionReference === 'PAY-REFUNDME'
            && $job->refundedAmountMinorUnits === 20000);
    }

    public function test_a_refund_processed_event_also_accepts_the_flat_transaction_reference_key(): void
    {
        Bus::fake();

        $payload = ['event' => 'refund.processed', 'data' => [
            'amount' => 50000,
            'transaction_reference' => 'PAY-REFUNDME2',
        ]];
        $body = json_encode($payload);

        $response = $this->call('POST', route('webhooks.paystack'), [], [], [], [
            'HTTP_x-paystack-signature' => $this->sign($body),
            'CONTENT_TYPE' => 'application/json',
        ], $body);

        $response->assertOk();
        Bus::assertDispatched(ProcessPaystackRefundWebhook::class, fn ($job) => $job->transactionReference === 'PAY-REFUNDME2');
    }

    public function test_a_refund_processed_event_missing_a_reference_is_acknowledged_but_not_processed(): void
    {
        Bus::fake();

        $payload = ['event' => 'refund.processed', 'data' => ['amount' => 20000]];
        $body = json_encode($payload);

        $response = $this->call('POST', route('webhooks.paystack'), [], [], [], [
            'HTTP_x-paystack-signature' => $this->sign($body),
            'CONTENT_TYPE' => 'application/json',
        ], $body);

        $response->assertOk();
        Bus::assertNotDispatched(ProcessPaystackRefundWebhook::class);
    }
}
