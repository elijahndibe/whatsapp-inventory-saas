<?php

namespace Tests\Unit;

use App\Exceptions\InsufficientStockException;
use App\Models\Business;
use App\Models\Customer;
use App\Models\InventoryTransaction;
use App\Models\Order;
use App\Models\Product;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderServiceStatusTest extends TestCase
{
    use RefreshDatabase;

    private OrderService $service;
    private Business $business;
    private Product $product;
    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(OrderService::class);
        $this->business = Business::factory()->create(['allow_overselling' => false]);
        $this->product = Product::factory()->create(['business_id' => $this->business->id, 'stock_quantity' => 10]);
        $customer = Customer::factory()->create(['business_id' => $this->business->id]);

        $this->order = Order::create([
            'business_id' => $this->business->id,
            'customer_id' => $customer->id,
            'subtotal' => 100,
            'total' => 100,
            'currency' => 'NGN',
        ]);
        $this->order->items()->create([
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'quantity' => 3,
            'price' => 100,
            'subtotal' => 300,
        ]);
    }

    public function test_confirming_an_order_deducts_stock_for_each_item(): void
    {
        $this->service->updateStatus($this->order, 'confirmed');

        $this->assertSame(7, $this->product->fresh()->stock_quantity);
        $this->assertNotNull($this->order->fresh()->inventory_deducted_at);
        $this->assertSame(
            1,
            InventoryTransaction::where('product_id', $this->product->id)->where('type', 'sale')->count()
        );
    }

    public function test_moving_between_non_pending_statuses_does_not_deduct_stock_again(): void
    {
        $this->service->updateStatus($this->order, 'confirmed');
        $this->service->updateStatus($this->order->fresh(), 'processing');
        $this->service->updateStatus($this->order->fresh(), 'shipped');

        $this->assertSame(7, $this->product->fresh()->stock_quantity);
        $this->assertSame(
            1,
            InventoryTransaction::where('product_id', $this->product->id)->where('type', 'sale')->count(),
            'Stock must only be deducted once no matter how many times the status changes afterward.'
        );
    }

    public function test_jumping_straight_to_a_later_status_still_deducts_stock_once(): void
    {
        $this->service->updateStatus($this->order, 'processing');

        $this->assertSame(7, $this->product->fresh()->stock_quantity);
    }

    public function test_cancelling_a_confirmed_order_restocks_it(): void
    {
        $this->service->updateStatus($this->order, 'confirmed');
        $this->service->updateStatus($this->order->fresh(), 'cancelled');

        $this->assertSame(10, $this->product->fresh()->stock_quantity);
        $this->assertNull($this->order->fresh()->inventory_deducted_at);
        $this->assertSame(
            1,
            InventoryTransaction::where('product_id', $this->product->id)->where('type', 'return')->count()
        );
    }

    public function test_cancelling_a_still_pending_order_does_not_touch_stock(): void
    {
        $this->service->updateStatus($this->order, 'cancelled');

        $this->assertSame(10, $this->product->fresh()->stock_quantity);
        $this->assertSame(0, InventoryTransaction::count());
    }

    public function test_confirming_and_cancelling_repeatedly_never_drifts_stock(): void
    {
        $this->service->updateStatus($this->order, 'confirmed');
        $this->service->updateStatus($this->order->fresh(), 'cancelled');
        $this->service->updateStatus($this->order->fresh(), 'confirmed');
        $this->service->updateStatus($this->order->fresh(), 'cancelled');

        $this->assertSame(10, $this->product->fresh()->stock_quantity);
    }

    public function test_confirming_an_order_with_insufficient_stock_throws_and_leaves_status_unchanged(): void
    {
        $this->product->update(['stock_quantity' => 1]); // less than the ordered quantity of 3

        $this->expectException(InsufficientStockException::class);

        try {
            $this->service->updateStatus($this->order, 'confirmed');
        } finally {
            $this->assertSame('pending', $this->order->fresh()->order_status);
            $this->assertNull($this->order->fresh()->inventory_deducted_at);
            $this->assertSame(1, $this->product->fresh()->stock_quantity);
        }
    }

    public function test_updating_to_the_same_status_is_a_no_op(): void
    {
        $this->service->updateStatus($this->order, 'confirmed');
        $this->service->updateStatus($this->order->fresh(), 'confirmed');

        $this->assertSame(7, $this->product->fresh()->stock_quantity);
        $this->assertSame(1, InventoryTransaction::count());
    }
}
