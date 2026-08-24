<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardStatsTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_real_counts_scoped_to_the_users_business(): void
    {
        $business = Business::factory()->create();
        $otherBusiness = Business::factory()->create();
        $user = User::factory()->create(['business_id' => $business->id]);

        Product::factory()->lowStock()->create(['business_id' => $business->id]);
        Product::factory()->outOfStock()->create(['business_id' => $business->id]);
        Customer::factory()->count(2)->create(['business_id' => $business->id]);

        // Belongs to a different business — must not be counted.
        Product::factory()->lowStock()->create(['business_id' => $otherBusiness->id]);

        $customer = Customer::factory()->create(['business_id' => $business->id]);
        Order::create([
            'business_id' => $business->id,
            'customer_id' => $customer->id,
            'subtotal' => 100,
            'total' => 100,
            'currency' => 'NGN',
            'order_status' => 'pending',
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewHas('stats', function (array $stats) {
            return $stats['Low Stock'] === 1
                && $stats['Out of Stock'] === 1
                && $stats['Total Customers'] === 3
                && $stats['Total Orders'] === 1
                && $stats['Pending Orders'] === 1;
        });
    }
}
