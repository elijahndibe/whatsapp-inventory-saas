<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Notifications\LowStockNotification;
use App\Notifications\NewOrderNotification;
use App\Notifications\PaymentReceivedNotification;
use App\Services\InventoryService;
use App\Services\OrderService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationsTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $owner;
    private User $staffWithoutPermission;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->business = Business::factory()->create();
        $this->owner = User::factory()->create(['business_id' => $this->business->id]);
        $this->owner->assignRole('Owner');

        $this->staffWithoutPermission = User::factory()->create(['business_id' => $this->business->id]);
        $this->staffWithoutPermission->assignRole('Staff'); // zero permissions
    }

    public function test_creating_an_order_notifies_staff_who_can_view_orders_but_not_those_who_cannot(): void
    {
        Notification::fake();

        $product = Product::factory()->create(['business_id' => $this->business->id, 'stock_quantity' => 10]);
        $items = collect([(object) ['product' => $product, 'quantity' => 1, 'subtotal' => (float) $product->price]]);

        $order = app(OrderService::class)->createFromCart($this->business, $items, [
            'name' => 'Jane Doe', 'phone' => '08011112222', 'address' => 'Lagos',
        ]);

        Notification::assertSentTo($this->owner, NewOrderNotification::class, fn ($n) => $n->order->is($order));
        Notification::assertNotSentTo($this->staffWithoutPermission, NewOrderNotification::class);
    }

    public function test_marking_payment_status_paid_notifies_staff(): void
    {
        Notification::fake();

        $customer = Customer::factory()->create(['business_id' => $this->business->id]);
        $order = Order::create([
            'business_id' => $this->business->id, 'customer_id' => $customer->id,
            'subtotal' => 100, 'total' => 100, 'currency' => 'NGN',
        ]);

        app(OrderService::class)->updatePaymentStatus($order, 'paid');

        Notification::assertSentTo($this->owner, PaymentReceivedNotification::class);
    }

    public function test_marking_already_paid_order_paid_again_does_not_re_notify(): void
    {
        Notification::fake();

        $customer = Customer::factory()->create(['business_id' => $this->business->id]);
        $order = Order::create([
            'business_id' => $this->business->id, 'customer_id' => $customer->id,
            'subtotal' => 100, 'total' => 100, 'currency' => 'NGN', 'payment_status' => 'paid',
        ]);

        app(OrderService::class)->updatePaymentStatus($order, 'paid');

        Notification::assertNothingSent();
    }

    public function test_stock_crossing_into_low_triggers_one_notification_not_a_repeat_per_sale(): void
    {
        Notification::fake();

        $product = Product::factory()->create([
            'business_id' => $this->business->id,
            'stock_quantity' => 10,
            'low_stock_threshold' => 5,
        ]);
        $inventory = app(InventoryService::class);

        $inventory->decrease($product, 6, 'sale'); // 10 -> 4, crosses into low stock
        Notification::assertSentTo($this->owner, LowStockNotification::class, 1);

        $inventory->decrease($product, 1, 'sale'); // 4 -> 3, still low, must not re-notify
        Notification::assertSentTo($this->owner, LowStockNotification::class, 1);
    }

    public function test_a_sale_that_does_not_cross_the_threshold_does_not_notify(): void
    {
        Notification::fake();

        $product = Product::factory()->create([
            'business_id' => $this->business->id,
            'stock_quantity' => 50,
            'low_stock_threshold' => 5,
        ]);

        app(InventoryService::class)->decrease($product, 5, 'sale'); // 50 -> 45, still healthy

        Notification::assertNothingSent();
    }
}
