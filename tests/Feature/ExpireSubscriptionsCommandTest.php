<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpireSubscriptionsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_command_expires_overdue_subscriptions(): void
    {
        Plan::create(['name' => 'Free', 'slug' => 'free', 'price' => 0]);
        $paid = Plan::create(['name' => 'Paid', 'slug' => 'paid', 'price' => 100]);
        $business = Business::factory()->create();
        Subscription::create([
            'business_id' => $business->id, 'plan_id' => $paid->id, 'status' => 'active',
            'starts_at' => now()->subDays(40), 'ends_at' => now()->subDay(),
        ]);

        $this->artisan('subscriptions:expire')
            ->expectsOutputToContain('Expired 1 subscription(s).')
            ->assertExitCode(0);
    }
}
