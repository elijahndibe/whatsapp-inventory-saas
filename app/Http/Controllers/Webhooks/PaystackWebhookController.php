<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessPaystackRefundWebhook;
use App\Jobs\ProcessPaystackWebhook;
use App\Jobs\ProcessSubscriptionPaymentWebhook;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaystackWebhookController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $signature = $request->header('x-paystack-signature');
        $expected = hash_hmac('sha512', $request->getContent(), (string) config('services.paystack.secret_key'));

        if (! $signature || ! hash_equals($expected, $signature)) {
            Log::warning('Rejected Paystack webhook: invalid signature.');

            return response()->json(['message' => 'invalid signature'], 400);
        }

        $event = $request->input('event');
        $reference = $request->input('data.reference');

        if ($event === 'charge.success' && $reference) {
            // Two independent payment flows share this one webhook URL —
            // routed by reference prefix (order payments: PAY-, subscription
            // upgrades: SUB-) rather than sharing one model/idempotency key.
            if (Str::startsWith($reference, 'SUB-')) {
                ProcessSubscriptionPaymentWebhook::dispatch($reference);
            } else {
                ProcessPaystackWebhook::dispatch($reference);
            }
        }

        // Refunds initiated from Paystack's own dashboard (or, in future,
        // by us via their API) land here. Unlike charge.success, there's no
        // separate "verify refund" server-to-server call to re-confirm
        // this against — Paystack doesn't offer the same re-verification
        // path for refunds that it does for transactions — so this trusts
        // the webhook body, which the signature check above has already
        // authenticated as genuinely from Paystack. The transaction
        // reference has appeared under both of these keys across Paystack
        // API versions, so both are checked defensively; if neither is
        // present this is logged and skipped rather than guessed at.
        if ($event === 'refund.processed') {
            $transactionReference = $request->input('data.transaction.reference')
                ?? $request->input('data.transaction_reference');
            $refundedAmount = (int) $request->input('data.amount', 0);

            if ($transactionReference && $refundedAmount > 0) {
                ProcessPaystackRefundWebhook::dispatch($transactionReference, $refundedAmount);
            } else {
                Log::warning('Paystack refund webhook missing a transaction reference or amount.', [
                    'payload' => $request->input('data'),
                ]);
            }
        }

        // Always 200 quickly once the signature checks out — actual
        // verification happens in the queued job, never inline here.
        return response()->json(['message' => 'ok']);
    }
}
