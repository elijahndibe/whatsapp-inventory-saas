<?php

namespace Tests\Unit;

use App\Models\Business;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Subscription *lifecycle* only — feature/limit resolution is covered by
 * FeatureServiceTest (see FeatureService, which now owns that logic).
 */
class SubscriptionServiceTest extends TestCase
{
    use RefreshDatabase;

    private SubscriptionService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(SubscriptionService::class);
    }

    public function test_a_business_with_no_subscription_has_no_current_plan(): void
    {
        $business = Business::factory()->create();

        $this->assertNull($this->service->currentPlan($business));
    }

    public function test_subscribing_to_a_new_plan_cancels_the_previous_active_subscription(): void
    {
        $free = Plan::create(['name' => 'Free', 'slug' => 'free-test', 'price' => 0]);
        $paid = Plan::create(['name' => 'Paid', 'slug' => 'paid-test', 'price' => 100, 'duration_days' => 30]);
        $business = Business::factory()->create();

        $first = $this->service->subscribeToPlan($business, $free);
        $second = $this->service->subscribeToPlan($business, $paid);

        $this->assertSame('cancelled', $first->fresh()->status);
        $this->assertSame('active', $second->fresh()->status);
        $this->assertNotNull($second->ends_at);
        $this->assertSame($paid->id, $this->service->currentPlan($business)->id);
    }

    public function test_expiring_overdue_subscriptions_drops_the_business_back_to_free(): void
    {
        $freePlan = Plan::create(['name' => 'Free', 'slug' => 'free', 'price' => 0]);
        $paid = Plan::create(['name' => 'Paid', 'slug' => 'paid', 'price' => 100]);
        $business = Business::factory()->create();

        $subscription = Subscription::create([
            'business_id' => $business->id, 'plan_id' => $paid->id, 'status' => 'active',
            'starts_at' => now()->subDays(40), 'ends_at' => now()->subDay(),
        ]);

        $count = $this->service->expireOverdueSubscriptions();

        $this->assertSame(1, $count);
        $this->assertSame('expired', $subscription->fresh()->status);
        $this->assertSame($freePlan->id, $this->service->currentPlan($business)->id);
    }

    public function test_a_subscription_not_yet_due_is_left_alone(): void
    {
        $plan = Plan::create(['name' => 'Paid', 'price' => 100]);
        $business = Business::factory()->create();
        Subscription::create([
            'business_id' => $business->id, 'plan_id' => $plan->id, 'status' => 'active',
            'starts_at' => now(), 'ends_at' => now()->addDays(10),
        ]);

        $count = $this->service->expireOverdueSubscriptions();

        $this->assertSame(0, $count);
        $this->assertSame($plan->id, $this->service->currentPlan($business)->id);
    }

    public function test_activate_from_verified_payment_creates_the_subscription(): void
    {
        $plan = Plan::create(['name' => 'Business', 'price' => 150, 'currency' => 'NGN']);
        $business = Business::factory()->create();

        $subscription = $this->service->activateFromVerifiedPayment([
            'reference' => 'SUB-TEST1',
            'status' => 'success',
            'amount' => 15000,
            'metadata' => ['business_id' => $business->id, 'plan_id' => $plan->id],
        ]);

        $this->assertNotNull($subscription);
        $this->assertSame($plan->id, $subscription->plan_id);
        $this->assertSame('SUB-TEST1', $subscription->payment_reference);
    }

    public function test_activate_from_verified_payment_is_idempotent_for_the_same_reference(): void
    {
        $plan = Plan::create(['name' => 'Business', 'price' => 150, 'currency' => 'NGN']);
        $business = Business::factory()->create();
        $payload = ['reference' => 'SUB-TEST2', 'status' => 'success', 'amount' => 15000, 'metadata' => ['business_id' => $business->id, 'plan_id' => $plan->id]];

        $this->service->activateFromVerifiedPayment($payload);
        $this->service->activateFromVerifiedPayment($payload);

        $this->assertSame(1, Subscription::where('payment_reference', 'SUB-TEST2')->count());
    }

    public function test_activate_from_verified_payment_rejects_an_amount_mismatch(): void
    {
        $plan = Plan::create(['name' => 'Business', 'price' => 150, 'currency' => 'NGN']);
        $business = Business::factory()->create();

        $subscription = $this->service->activateFromVerifiedPayment([
            'reference' => 'SUB-TEST3',
            'status' => 'success',
            'amount' => 100, // does not match plan price of 15000 kobo
            'metadata' => ['business_id' => $business->id, 'plan_id' => $plan->id],
        ]);

        $this->assertNull($subscription);
        $this->assertNull($this->service->currentPlan($business));
    }
}
