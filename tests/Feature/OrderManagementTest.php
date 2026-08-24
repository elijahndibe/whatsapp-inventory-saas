<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderManagementTest extends TestCase
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

    private function makeOrder(array $overrides = []): Order
    {
        $customer = Customer::factory()->create(['business_id' => $this->business->id]);
        $product = Product::factory()->create(['business_id' => $this->business->id, 'stock_quantity' => 10]);

        $order = Order::create(array_merge([
            'business_id' => $this->business->id,
            'customer_id' => $customer->id,
            'subtotal' => 1000,
            'total' => 1000,
            'currency' => 'NGN',
        ], $overrides));

        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 2,
            'price' => 500,
            'subtotal' => 1000,
        ]);

        return $order;
    }

    public function test_owner_can_view_the_orders_index(): void
    {
        $this->makeOrder();

        $response = $this->actingAs($this->owner)->get(route('orders.index'));

        $response->assertOk();
    }

    public function test_owner_can_view_an_order(): void
    {
        $order = $this->makeOrder();

        $response = $this->actingAs($this->owner)->get(route('orders.show', $order));

        $response->assertOk();
        $response->assertSeeText($order->order_number);
    }

    public function test_owner_can_update_order_status_and_it_deducts_stock(): void
    {
        $order = $this->makeOrder();
        $product = $order->items->first()->product;

        $response = $this->actingAs($this->owner)->patch(route('orders.status.update', $order), [
            'order_status' => 'confirmed',
        ]);

        $response->assertRedirect();
        $this->assertSame('confirmed', $order->fresh()->order_status);
        $this->assertSame(8, $product->fresh()->stock_quantity);
    }

    public function test_owner_can_update_payment_status(): void
    {
        $order = $this->makeOrder();

        $this->actingAs($this->owner)->patch(route('orders.payment-status.update', $order), [
            'payment_status' => 'paid',
        ]);

        $this->assertSame('paid', $order->fresh()->payment_status);
    }

    public function test_a_staff_member_without_permission_cannot_view_orders(): void
    {
        $staff = User::factory()->create(['business_id' => $this->business->id]);
        $staff->assignRole('Staff');
        $order = $this->makeOrder();

        $this->actingAs($staff)->get(route('orders.index'))->assertForbidden();
        $this->actingAs($staff)->get(route('orders.show', $order))->assertForbidden();
    }

    public function test_a_user_cannot_view_another_businesss_order(): void
    {
        $otherBusiness = Business::factory()->create();
        $customer = Customer::factory()->create(['business_id' => $otherBusiness->id]);
        $foreignOrder = Order::create([
            'business_id' => $otherBusiness->id,
            'customer_id' => $customer->id,
            'subtotal' => 100,
            'total' => 100,
            'currency' => 'NGN',
        ]);

        $response = $this->actingAs($this->owner)->get(route('orders.show', $foreignOrder));

        $response->assertNotFound();
    }

    public function test_search_filters_orders_by_order_number(): void
    {
        $order = $this->makeOrder();
        $this->makeOrder();

        $response = $this->actingAs($this->owner)->get(route('orders.index', ['search' => $order->order_number]));

        $response->assertSeeText($order->order_number);
    }
}
