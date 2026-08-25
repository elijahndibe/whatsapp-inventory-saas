<?php

namespace App\Jobs;

use App\Services\PaystackService;
use App\Services\SubscriptionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Mirrors ProcessPaystackWebhook but for subscription (plan upgrade)
 * payments rather than order payments — kept separate because the two
 * have different downstream models (Subscription vs Payment/Order) and
 * different idempotency keys.
 */
class ProcessSubscriptionPaymentWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;
    public array $backoff = [10, 30, 60, 300];

    public function __construct(public readonly string $reference) {}

    public function handle(PaystackService $paystack, SubscriptionService $subscriptions): void
    {
        try {
            $verified = $paystack->verifyTransaction($this->reference);
        } catch (\Throwable $e) {
            Log::warning('Subscription webhook: verify call failed', ['reference' => $this->reference, 'error' => $e->getMessage()]);
            throw $e;
        }

        $subscriptions->activateFromVerifiedPayment($verified);
    }
}
