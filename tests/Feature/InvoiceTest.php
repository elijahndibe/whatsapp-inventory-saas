<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $owner;
    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->business = Business::factory()->create();
        $this->owner = User::factory()->create(['business_id' => $this->business->id]);
        $this->owner->assignRole('Owner');

        $customer = Customer::factory()->create(['business_id' => $this->business->id]);
        $this->order = Order::create([
            'business_id' => $this->business->id,
            'customer_id' => $customer->id,
            'subtotal' => 1000,
            'total' => 1000,
            'currency' => 'NGN',
        ]);
        $this->order->items()->create([
            'product_name' => 'Test Product', 'quantity' => 1, 'price' => 1000, 'subtotal' => 1000,
        ]);
    }

    public function test_owner_can_view_the_invoice_pdf(): void
    {
        $response = $this->actingAs($this->owner)->get(route('orders.invoice', $this->order));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_receipt_404s_for_an_unpaid_order(): void
    {
        $response = $this->actingAs($this->owner)->get(route('orders.receipt', $this->order));

        $response->assertNotFound();
    }

    public function test_receipt_is_available_once_paid(): void
    {
        $this->order->update(['payment_status' => 'paid']);

        $response = $this->actingAs($this->owner)->get(route('orders.receipt', $this->order));

        $response->assertOk();
    }

    public function test_a_staff_member_without_permission_cannot_view_an_invoice(): void
    {
        $staff = User::factory()->create(['business_id' => $this->business->id]);
        $staff->assignRole('Staff');

        $this->actingAs($staff)->get(route('orders.invoice', $this->order))->assertForbidden();
    }

    public function test_a_user_cannot_view_another_businesss_invoice(): void
    {
        $otherBusiness = Business::factory()->create();
        $otherOwner = User::factory()->create(['business_id' => $otherBusiness->id]);
        $otherOwner->assignRole('Owner');

        $this->actingAs($otherOwner)->get(route('orders.invoice', $this->order))->assertNotFound();
    }
}
