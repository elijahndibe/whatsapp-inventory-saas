<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Feature;
use App\Models\Plan;
use App\Models\PlanFeature;

/**
 * The single place feature access and numeric limits are evaluated —
 * nothing outside this class should read Feature/PlanFeature rows or
 * inspect a plan's limits directly (see Feature::enabled()-style call
 * sites in InvoiceController, LocationController, OrderController,
 * ReportController, StaffController, Storefront\CheckoutController,
 * CheckPlanLimit, SendWhatsAppOrderMessage).
 *
 * Resolution order:
 *  1. A feature that doesn't exist, or is globally disabled, is always
 *     blocked — the platform-wide kill-switch beats everything else,
 *     even a plan that technically grants it.
 *  2. While the "Subscription System" platform setting is OFF (the
 *     launch default), tier limits don't apply at all: every business
 *     gets full access to every globally-enabled feature and unlimited
 *     numeric limits. Admins can still pre-configure the Free/Pro/
 *     Business matrix from the Features panel — it simply has no live
 *     effect until Subscription System is switched ON.
 *  3. Once ON, access is resolved against the business's actual active
 *     plan, falling back to the plan flagged is_default (Free) when the
 *     business has none.
 */
class FeatureService
{
    public function __construct(private readonly PlatformSettingsService $settings) {}

    public function enabled(Business $business, string $key): bool
    {
        $feature = Feature::where('key', $key)->first();

        // An explicitly-defined, globally-disabled feature is always
        // blocked — the platform kill-switch wins over everything else,
        // including subscription mode.
        if ($feature && ! $feature->is_enabled) {
            return false;
        }

        if (! $this->settings->subscriptionSystemEnabled()) {
            // No tiering while off. A feature with no catalog row at all
            // (nothing has been configured to gate it yet) is likewise
            // never gated — only an explicit is_enabled=false blocks.
            return true;
        }

        if (! $feature) {
            return false; // subscription tiering is on, but nothing grants this
        }

        $planFeature = $this->planFeatureFor($business, $feature);

        return (bool) ($planFeature?->enabled);
    }

    /**
     * @return int|null null means unlimited
     */
    public function limit(Business $business, string $key): ?int
    {
        $feature = Feature::where('key', $key)->where('type', Feature::TYPE_LIMIT)->first();

        if ($feature && ! $feature->is_enabled) {
            return 0;
        }

        if (! $this->settings->subscriptionSystemEnabled()) {
            return null;
        }

        if (! $feature) {
            return 0;
        }

        $planFeature = $this->planFeatureFor($business, $feature);

        if (! $planFeature) {
            return 0;
        }

        return $planFeature->value;
    }

    public function withinLimit(Business $business, string $key, int $currentUsage): bool
    {
        $limit = $this->limit($business, $key);

        return $limit === null || $currentUsage < $limit;
    }

    private function planFeatureFor(Business $business, Feature $feature): ?PlanFeature
    {
        $plan = $this->resolvePlan($business);

        if (! $plan) {
            return null;
        }

        return PlanFeature::where('plan_id', $plan->id)->where('feature_id', $feature->id)->first();
    }

    private function resolvePlan(Business $business): ?Plan
    {
        $subscription = $business->currentSubscription();

        if ($subscription && $subscription->isActive()) {
            return $subscription->plan;
        }

        return Plan::where('is_default', true)->first();
    }
}
