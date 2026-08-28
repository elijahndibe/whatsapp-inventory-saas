<?php

namespace App\Services;

use App\Models\Business;
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
        private readonly CommissionService $commission,
    ) {}

    /**
     * Starts a new payment attempt for an order and returns the Paystack
     * hosted checkout URL to redirect the customer to. Each attempt gets
     * its own reference — an order can have several Payment rows if a
     * first attempt fails or is abandoned and the customer retries.
     *
     * Commission is calculated server-side here (never from anything the
     * client sends) and snapshotted onto the Payment row immediately —
     * this is the permanent record later reports must read, regardless of
     * what the commission configuration becomes afterwards.
     */
    public function initializeForOrder(Order $order, string $email): array
    {
        $reference = $this->generateReference();
        $grossAmount = $order->totalInMinorUnits();
        $split = $this->commission->calculate($order->business, $grossAmount);

        $payment = Payment::create([
            'business_id' => $order->business_id,
            'order_id' => $order->id,
            'reference' => $reference,
            'gateway' => 'paystack',
            'amount' => $order->total, // major units in, minor units stored (Attribute mutator)
            'currency' => $order->currency,
            'status' => 'pending',
            'commission_rate' => $split['rate'],
            'commission_amount' => $split['commission_amount'] / 100,
            'seller_amount' => $split['seller_amount'] / 100,
            'settlement_status' => 'pending',
        ]);

        $result = $this->paystack->initializeTransaction([
            'email' => $email,
            'amount' => $grossAmount,
            'currency' => $order->currency,
            'reference' => $reference,
            'callback_url' => route('storefront.payments.callback', $order->business),
            'metadata' => [
                'business_id' => $order->business_id,
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ],
            ...$this->splitParams($order->business, $split['commission_amount']),
        ]);

        // Persisted (not just returned) so a seller can revisit the order
        // later — e.g. the WhatsApp payment-link flow — and re-send/copy
        // the same link instead of a fresh Paystack transaction being
        // generated on every page view.
        $payment->update(['authorization_url' => $result['authorization_url']]);

        return ['payment' => $payment, 'authorization_url' => $result['authorization_url']];
    }

    /**
     * Reuses an order's most recent still-pending payment attempt if one
     * exists (so re-clicking "request payment" doesn't spawn a new
     * Paystack transaction every time), otherwise starts a new one. Used
     * by the WhatsApp seller-initiated payment-link flow — direct
     * storefront checkout always starts fresh via initializeForOrder()
     * since a customer paying immediately has no "come back later" case.
     */
    public function initializeOrReuseForOrder(Order $order, string $email): array
    {
        $pending = $order->payments()->where('status', 'pending')->whereNotNull('authorization_url')->latest('id')->first();

        if ($pending) {
            return ['payment' => $pending, 'authorization_url' => $pending->authorization_url];
        }

        return $this->initializeForOrder($order, $email);
    }

    /**
     * When the seller has a connected Paystack subaccount, Paystack
     * splits the payment automatically at settlement: the platform
     * receives transaction_charge, the seller's subaccount bears the
     * rest. Without a connected subaccount, the full amount lands in the
     * platform's own account (today's behaviour) and settlement_status
     * stays platform_held for manual reconciliation later.
     */
    private function splitParams(Business $business, int $commissionAmountMinorUnits): array
    {
        if (! $business->hasPaystackSubaccount()) {
            return [];
        }

        return [
            'subaccount' => $business->paystack_subaccount_code,
            'transaction_charge' => $commissionAmountMinorUnits,
            'bearer' => 'account',
        ];
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

            if ($payment->status === 'success') {
                $payment->payment_fee = ((int) ($verified['fees'] ?? 0)) / 100;

                $business = Business::withoutGlobalScope(BusinessScope::class)->find($payment->business_id);
                $payment->settlement_status = $business?->hasPaystackSubaccount() ? 'settled' : 'platform_held';
            } else {
                $payment->settlement_status = 'failed';
            }

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
