<?php

namespace App\Jobs;

use App\Services\PaymentService;
use App\Services\PaystackService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Runs outside the webhook HTTP response so Paystack always gets an
 * immediate 200 regardless of how long our own verify call + processing
 * takes. The webhook payload itself is never trusted for the outcome —
 * this job re-verifies the transaction server-to-server before applying
 * anything.
 */
class ProcessPaystackWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;
    public array $backoff = [10, 30, 60, 300];

    public function __construct(public readonly string $reference) {}

    public function handle(PaystackService $paystack, PaymentService $payments): void
    {
        try {
            $verified = $paystack->verifyTransaction($this->reference);
        } catch (\Throwable $e) {
            Log::warning('Paystack webhook: verify call failed', ['reference' => $this->reference, 'error' => $e->getMessage()]);
            throw $e; // let the queue retry
        }

        $payments->handleVerifiedTransaction($verified);
    }
}
