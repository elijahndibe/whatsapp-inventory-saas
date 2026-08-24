<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerManagementTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->business = Business::factory()->create();
        $this->owner = User::factory()->create(['business_id' => $this->business->id]);
        $this->owner->assignRole('Owner');
    }

    public function test_owner_can_view_the_customers_index_with_order_aggregates(): void
    {
        $customer = Customer::factory()->create(['business_id' => $this->business->id, 'name' => 'Jane Doe']);
        Order::create([
            'business_id' => $this->business->id,
            'customer_id' => $customer->id,
            'subtotal' => 20000,
            'total' => 20000,
            'currency' => 'NGN',
        ]);

        $response = $this->actingAs($this->owner)->get(route('customers.index'));

        $response->assertOk();
        $response->assertSeeText('Jane Doe');
        $response->assertSeeText('20,000.00');
    }

    public function test_owner_can_view_a_customer_profile_with_order_history(): void
    {
        $customer = Customer::factory()->create(['business_id' => $this->business->id]);
        $order = Order::create([
            'business_id' => $this->business->id,
            'customer_id' => $customer->id,
            'subtotal' => 5000,
            'total' => 5000,
            'currency' => 'NGN',
        ]);

        $response = $this->actingAs($this->owner)->get(route('customers.show', $customer));

        $response->assertOk();
        $response->assertSeeText($order->order_number);
    }

    public function test_owner_can_update_a_customer(): void
    {
        $customer = Customer::factory()->create(['business_id' => $this->business->id]);

        $response = $this->actingAs($this->owner)->put(route('customers.update', $customer), [
            'name' => 'Updated Name',
            'phone' => $customer->phone,
            'notes' => 'VIP customer',
        ]);

        $response->assertRedirect(route('customers.show', $customer));
        $this->assertSame('Updated Name', $customer->fresh()->name);
        $this->assertSame('VIP customer', $customer->fresh()->notes);
    }

    public function test_a_user_cannot_view_another_businesss_customer(): void
    {
        $otherBusiness = Business::factory()->create();
        $foreignCustomer = Customer::factory()->create(['business_id' => $otherBusiness->id]);

        $response = $this->actingAs($this->owner)->get(route('customers.show', $foreignCustomer));

        $response->assertNotFound();
    }

    public function test_a_staff_member_without_permission_cannot_view_customers(): void
    {
        $staff = User::factory()->create(['business_id' => $this->business->id]);
        $staff->assignRole('Staff');

        $this->actingAs($staff)->get(route('customers.index'))->assertForbidden();
    }
}
