<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Scopes\BusinessScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class PaymentService
{
    public function __construct(
        private readonly PaystackService $paystack,
        private readonly OrderService $orders,
    ) {}

    /**
     * Starts a new payment attempt for an order and returns the Paystack
     * hosted checkout URL to redirect the customer to. Each attempt gets
     * its own reference — an order can have several Payment rows if a
     * first attempt fails or is abandoned and the customer retries.
     */
    public function initializeForOrder(Order $order, string $email): array
    {
        $reference = $this->generateReference();

        $payment = Payment::create([
            'business_id' => $order->business_id,
            'order_id' => $order->id,
            'reference' => $reference,
            'gateway' => 'paystack',
            'amount' => $order->total, // major units in, minor units stored (Attribute mutator)
            'currency' => $order->currency,
            'status' => 'pending',
        ]);

        $result = $this->paystack->initializeTransaction([
            'email' => $email,
            'amount' => $order->totalInMinorUnits(),
            'currency' => $order->currency,
            'reference' => $reference,
            'callback_url' => route('storefront.payments.callback', $order->business),
            'metadata' => [
                'business_id' => $order->business_id,
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ],
        ]);

        return ['payment' => $payment, 'authorization_url' => $result['authorization_url']];
    }

    /**
     * Applies a Paystack-verified transaction (i.e. the response from
     * PaystackService::verifyTransaction(), never a raw client-supplied
     * payload) to our records. Safe to call more than once for the same
     * reference — from both the customer's browser callback and the
     * webhook, in either order — because everything after the row lock
     * is guarded by the payment's current status.
     */
    public function handleVerifiedTransaction(array $verified): Payment
    {
        return DB::transaction(function () use ($verified) {
            $reference = $verified['reference'] ?? null;

            $payment = Payment::withoutGlobalScope(BusinessScope::class)
                ->where('reference', $reference)
                ->lockForUpdate()
                ->first();

            if (! $payment) {
                throw new RuntimeException("No payment found for reference: {$reference}");
            }

            if ($payment->status === 'success') {
                return $payment; // already processed — duplicate webhook/callback race
            }

            $isSuccessful = ($verified['status'] ?? null) === 'success';
            $amountMatches = (int) ($verified['amount'] ?? 0) === (int) $payment->getRawOriginal('amount');

            $payment->status = ($isSuccessful && $amountMatches) ? 'success' : 'failed';
            $payment->gateway_response = json_encode($verified);
            $payment->paid_at = $payment->status === 'success' ? now() : null;
            $payment->save();

            if ($payment->status === 'success') {
                $order = Order::withoutGlobalScope(BusinessScope::class)->findOrFail($payment->order_id);
                $this->orders->markAsPaidViaGateway($order, ['reference' => $payment->reference]);
            }

            return $payment;
        });
    }

    private function generateReference(): string
    {
        do {
            $reference = 'PAY-'.strtoupper(Str::random(14));
        } while (Payment::withoutGlobalScope(BusinessScope::class)->where('reference', $reference)->exists());

        return $reference;
    }
}
