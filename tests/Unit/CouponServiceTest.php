<?php

namespace Tests\Unit;

use App\Models\Business;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\Feature;
use App\Models\Order;
use App\Services\CouponService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponServiceTest extends TestCase
{
    use RefreshDatabase;

    private CouponService $service;
    private Business $business;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(CouponService::class);
        $this->business = Business::factory()->create();
    }

    private function makeCoupon(array $overrides = []): Coupon
    {
        return Coupon::create(array_merge([
            'business_id' => $this->business->id,
            'code' => 'SAVE10',
            'type' => Coupon::TYPE_PERCENTAGE,
            'value' => 10,
            'is_active' => true,
        ], $overrides));
    }

    public function test_a_valid_percentage_coupon_discounts_the_subtotal(): void
    {
        $this->makeCoupon(['value' => 10]);

        $result = $this->service->validate($this->business, 'save10', 1000);

        $this->assertNull($result['error']);
        $this->assertEquals(100.0, $result['discount']);
    }

    public function test_the_code_is_matched_case_insensitively(): void
    {
        $this->makeCoupon(['code' => 'WELCOME']);

        $result = $this->service->validate($this->business, 'welcome', 500);

        $this->assertNull($result['error']);
    }

    public function test_a_percentage_discount_is_capped_by_max_discount_amount(): void
    {
        $this->makeCoupon(['type' => Coupon::TYPE_PERCENTAGE, 'value' => 50, 'max_discount_amount' => 300]);

        $result = $this->service->validate($this->business, 'SAVE10', 1000); // 50% of 1000 = 500, capped to 300

        $this->assertEquals(300.0, $result['discount']);
    }

    public function test_a_fixed_discount_never_exceeds_the_subtotal(): void
    {
        $this->makeCoupon(['type' => Coupon::TYPE_FIXED, 'value' => 5000]);

        $result = $this->service->validate($this->business, 'SAVE10', 200);

        $this->assertEquals(200.0, $result['discount'], 'A coupon must never discount an order below zero.');
    }

    public function test_a_coupon_below_the_minimum_order_amount_is_rejected(): void
    {
        $this->makeCoupon(['minimum_order_amount' => 5000]);

        $result = $this->service->validate($this->business, 'SAVE10', 1000);

        $this->assertNotNull($result['error']);
        $this->assertNull($result['coupon']);
    }

    public function test_an_expired_coupon_is_rejected(): void
    {
        $this->makeCoupon(['expires_at' => now()->subDay()]);

        $result = $this->service->validate($this->business, 'SAVE10', 1000);

        $this->assertStringContainsString('expired', $result['error']);
    }

    public function test_a_coupon_that_has_not_started_yet_is_rejected(): void
    {
        $this->makeCoupon(['starts_at' => now()->addDay()]);

        $result = $this->service->validate($this->business, 'SAVE10', 1000);

        $this->assertNotNull($result['error']);
    }

    public function test_an_inactive_coupon_is_rejected(): void
    {
        $this->makeCoupon(['is_active' => false]);

        $result = $this->service->validate($this->business, 'SAVE10', 1000);

        $this->assertNotNull($result['error']);
    }

    public function test_a_coupon_that_has_reached_its_total_usage_limit_is_rejected(): void
    {
        $this->makeCoupon(['usage_limit' => 2, 'times_used' => 2]);

        $result = $this->service->validate($this->business, 'SAVE10', 1000);

        $this->assertStringContainsString('usage limit', $result['error']);
    }

    public function test_a_customer_who_has_already_used_the_coupon_is_rejected_when_a_per_customer_limit_applies(): void
    {
        $coupon = $this->makeCoupon(['usage_limit_per_customer' => 1]);
        $customer = Customer::factory()->create(['business_id' => $this->business->id, 'phone' => '08012345678']);
        Order::create([
            'business_id' => $this->business->id,
            'customer_id' => $customer->id,
            'coupon_id' => $coupon->id,
            'coupon_code' => $coupon->code,
            'subtotal' => 1000,
            'total' => 900,
            'currency' => 'NGN',
        ]);

        $result = $this->service->validate($this->business, 'SAVE10', 1000, '08012345678');

        $this->assertNotNull($result['error']);
    }

    public function test_a_different_customer_can_still_use_a_per_customer_limited_coupon(): void
    {
        $coupon = $this->makeCoupon(['usage_limit_per_customer' => 1]);
        $customer = Customer::factory()->create(['business_id' => $this->business->id, 'phone' => '08012345678']);
        Order::create([
            'business_id' => $this->business->id,
            'customer_id' => $customer->id,
            'coupon_id' => $coupon->id,
            'coupon_code' => $coupon->code,
            'subtotal' => 1000,
            'total' => 900,
            'currency' => 'NGN',
        ]);

        $result = $this->service->validate($this->business, 'SAVE10', 1000, '08099999999');

        $this->assertNull($result['error']);
    }

    public function test_a_code_that_does_not_exist_is_rejected(): void
    {
        $result = $this->service->validate($this->business, 'NOPE', 1000);

        $this->assertNotNull($result['error']);
    }

    public function test_a_coupon_belonging_to_another_business_is_not_matched(): void
    {
        $otherBusiness = Business::factory()->create();
        Coupon::create(['business_id' => $otherBusiness->id, 'code' => 'SAVE10', 'type' => Coupon::TYPE_PERCENTAGE, 'value' => 10, 'is_active' => true]);

        $result = $this->service->validate($this->business, 'SAVE10', 1000);

        $this->assertNotNull($result['error']);
    }

    public function test_coupons_are_rejected_when_the_feature_is_globally_disabled(): void
    {
        $this->makeCoupon();
        Feature::firstOrCreate(['key' => 'coupons'], ['name' => 'Coupon codes', 'type' => Feature::TYPE_BOOLEAN, 'is_enabled' => false]);

        $result = $this->service->validate($this->business, 'SAVE10', 1000);

        $this->assertNotNull($result['error']);
    }

    public function test_redeem_increments_times_used(): void
    {
        $coupon = $this->makeCoupon(['times_used' => 3]);

        $this->service->redeem($coupon);

        $this->assertSame(4, $coupon->fresh()->times_used);
    }
}
