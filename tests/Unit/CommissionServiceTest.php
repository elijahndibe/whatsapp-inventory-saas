<?php

namespace Tests\Unit;

use App\Models\Business;
use App\Services\CommissionService;
use App\Services\PlatformSettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommissionServiceTest extends TestCase
{
    use RefreshDatabase;

    private CommissionService $commission;
    private PlatformSettingsService $settings;

    protected function setUp(): void
    {
        parent::setUp();

        $this->commission = app(CommissionService::class);
        $this->settings = app(PlatformSettingsService::class);
    }

    public function test_default_platform_rate_is_applied_when_the_business_has_no_override(): void
    {
        $this->settings->set('commission.rate', 1.5);
        $business = Business::factory()->create(['commission_rate' => null]);

        $result = $this->commission->calculate($business, 100000); // 1000.00 in kobo

        $this->assertSame(1.5, $result['rate']);
        $this->assertSame(1500, $result['commission_amount']); // 1.5% of 100000
        $this->assertSame(98500, $result['seller_amount']);
    }

    public function test_a_seller_specific_override_takes_priority_over_the_platform_default(): void
    {
        $this->settings->set('commission.rate', 1.5);
        $business = Business::factory()->create(['commission_rate' => 0.5]);

        $result = $this->commission->calculate($business, 100000);

        $this->assertSame(0.5, $result['rate']);
        $this->assertSame(500, $result['commission_amount']);
        $this->assertSame(99500, $result['seller_amount']);
    }

    public function test_disabled_commission_charges_nothing(): void
    {
        $this->settings->set('commission.enabled', false);
        $business = Business::factory()->create();

        $result = $this->commission->calculate($business, 100000);

        $this->assertSame(0.0, $result['rate']);
        $this->assertSame(0, $result['commission_amount']);
        $this->assertSame(100000, $result['seller_amount']);
    }

    public function test_rate_is_clamped_to_the_configured_minimum(): void
    {
        $this->settings->set('commission.min', 2.0);
        $business = Business::factory()->create(['commission_rate' => 0.5]);

        $result = $this->commission->calculate($business, 100000);

        $this->assertSame(2.0, $result['rate']);
    }

    public function test_rate_is_clamped_to_the_configured_maximum(): void
    {
        $this->settings->set('commission.max', 3.0);
        $business = Business::factory()->create(['commission_rate' => 10.0]);

        $result = $this->commission->calculate($business, 100000);

        $this->assertSame(3.0, $result['rate']);
    }

    public function test_commission_amount_rounds_to_the_nearest_minor_unit(): void
    {
        $this->settings->set('commission.rate', 1.5);
        $business = Business::factory()->create();

        $result = $this->commission->calculate($business, 333); // 1.5% of 333 = 4.995

        $this->assertSame(5, $result['commission_amount']);
        $this->assertSame(328, $result['seller_amount']);
    }

    public function test_changing_the_platform_rate_later_does_not_affect_a_value_already_calculated(): void
    {
        $this->settings->set('commission.rate', 1.5);
        $business = Business::factory()->create();

        $january = $this->commission->calculate($business, 100000);

        $this->settings->set('commission.rate', 1.0);
        $march = $this->commission->calculate($business, 100000);

        $this->assertSame(1.5, $january['rate']);
        $this->assertSame(1.0, $march['rate']);
        $this->assertSame(1500, $january['commission_amount']);
        $this->assertSame(1000, $march['commission_amount']);
    }
}
