<?php

namespace App\Jobs;

use App\Services\PaymentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Runs outside the webhook HTTP response, same as ProcessPaystackWebhook —
 * Paystack always gets an immediate 200 regardless of how long applying
 * the refund takes.
 */
class ProcessPaystackRefundWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;
    public array $backoff = [10, 30, 60, 300];

    public function __construct(
        public readonly string $transactionReference,
        public readonly int $refundedAmountMinorUnits,
    ) {}

    public function handle(PaymentService $payments): void
    {
        $payments->handleRefund($this->transactionReference, $this->refundedAmountMinorUnits);
    }
}
