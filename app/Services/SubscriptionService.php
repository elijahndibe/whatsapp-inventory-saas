<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Plan;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Subscription *lifecycle* only (subscribing, activating from a verified
 * payment, expiring). Feature access and numeric limits are evaluated by
 * FeatureService, not here — see FeatureService for why.
 */
class SubscriptionService
{
    public function currentPlan(Business $business): ?Plan
    {
        $subscription = $business->currentSubscription();

        if (! $subscription || ! $subscription->isActive()) {
            return null;
        }

        return $subscription->plan;
    }

    /**
     * Activates a plan for a business — either free (immediate, no
     * payment) or paid (called after a verified Paystack transaction).
     * A new subscription row is always created rather than mutating the
     * existing one, preserving history of what the business was on and
     * when.
     */
    public function subscribeToPlan(Business $business, Plan $plan, array $paymentContext = []): Subscription
    {
        return DB::transaction(function () use ($business, $plan, $paymentContext) {
            $business->subscriptions()->where('status', 'active')->update(['status' => 'cancelled']);

            return Subscription::create([
                'business_id' => $business->id,
                'plan_id' => $plan->id,
                'status' => 'active',
                'starts_at' => now(),
                'ends_at' => $plan->isFree() ? null : now()->addDays($plan->duration_days),
                'payment_reference' => $paymentContext['reference'] ?? null,
                'amount_paid' => $paymentContext['amount'] ?? null,
                'paid_at' => isset($paymentContext['reference']) ? now() : null,
            ]);
        });
    }

    /**
     * Applies a Paystack-verified subscription payment (never a raw
     * webhook payload or callback query string — the caller must have
     * already called PaystackService::verifyTransaction()). Idempotent by
     * payment_reference: calling this twice for the same reference (the
     * callback and the webhook racing) activates the plan only once.
     */
    public function activateFromVerifiedPayment(array $verified): ?Subscription
    {
        $reference = $verified['reference'] ?? null;

        if (Subscription::where('payment_reference', $reference)->exists()) {
            return Subscription::where('payment_reference', $reference)->first();
        }

        if (($verified['status'] ?? null) !== 'success') {
            return null;
        }

        $businessId = $verified['metadata']['business_id'] ?? null;
        $planId = $verified['metadata']['plan_id'] ?? null;

        $business = $businessId ? Business::find($businessId) : null;
        $plan = $planId ? Plan::find($planId) : null;

        if (! $business || ! $plan) {
            return null;
        }

        // Integrity check: the amount actually paid must match the plan's
        // price, not just whatever the client-side request claimed.
        $expectedAmount = (int) round($plan->price * 100);
        if ((int) ($verified['amount'] ?? 0) !== $expectedAmount) {
            return null;
        }

        return $this->subscribeToPlan($business, $plan, [
            'reference' => $reference,
            'amount' => $plan->price,
        ]);
    }

    /**
     * Marks any subscription past its ends_at as expired and drops the
     * business back to the Free plan, so it never ends up with no
     * subscription at all (which would fail open / unrestricted — expiry
     * should mean falling back to Free's limits, not no limits).
     */
    public function expireOverdueSubscriptions(): int
    {
        $expired = Subscription::where('status', 'active')
            ->whereNotNull('ends_at')
            ->where('ends_at', '<', Carbon::now())
            ->get();

        $freePlan = Plan::where('slug', 'free')->first();

        foreach ($expired as $subscription) {
            DB::transaction(function () use ($subscription, $freePlan) {
                $subscription->update(['status' => 'expired']);

                if ($freePlan) {
                    Subscription::create([
                        'business_id' => $subscription->business_id,
                        'plan_id' => $freePlan->id,
                        'status' => 'active',
                        'starts_at' => now(),
                        'ends_at' => null,
                    ]);
                }
            });
        }

        return $expired->count();
    }
}
