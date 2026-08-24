<?php

namespace Tests\Unit;

use App\Exceptions\InsufficientStockException;
use App\Models\Business;
use App\Models\Product;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class InventoryServiceTest extends TestCase
{
    use RefreshDatabase;

    private InventoryService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(InventoryService::class);
    }

    public function test_increase_adds_to_stock_and_logs_a_transaction(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 10]);

        $transaction = $this->service->increase($product, 5, 'purchase', ['notes' => 'restock']);

        $this->assertSame(15, $product->fresh()->stock_quantity);
        $this->assertSame(5, $transaction->quantity);
        $this->assertSame(10, $transaction->previous_quantity);
        $this->assertSame(15, $transaction->new_quantity);
        $this->assertSame('purchase', $transaction->type);
        $this->assertSame('restock', $transaction->notes);
    }

    public function test_decrease_subtracts_from_stock(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 10]);

        $this->service->decrease($product, 4, 'sale');

        $this->assertSame(6, $product->fresh()->stock_quantity);
    }

    public function test_decrease_below_zero_throws_when_overselling_is_disabled(): void
    {
        $business = Business::factory()->create(['allow_overselling' => false]);
        $product = Product::factory()->create(['business_id' => $business->id, 'stock_quantity' => 3]);

        $this->expectException(InsufficientStockException::class);

        $this->service->decrease($product, 5, 'sale');

        $this->assertSame(3, $product->fresh()->stock_quantity, 'Stock must be unchanged after a rejected decrease.');
    }

    public function test_decrease_below_zero_succeeds_when_overselling_is_enabled(): void
    {
        $business = Business::factory()->create(['allow_overselling' => true]);
        $product = Product::factory()->create(['business_id' => $business->id, 'stock_quantity' => 3]);

        $this->service->decrease($product, 5, 'sale');

        $this->assertSame(-2, $product->fresh()->stock_quantity);
    }

    public function test_no_inventory_transaction_is_written_when_a_decrease_is_rejected(): void
    {
        $business = Business::factory()->create(['allow_overselling' => false]);
        $product = Product::factory()->create(['business_id' => $business->id, 'stock_quantity' => 2]);

        try {
            $this->service->decrease($product, 10, 'sale');
        } catch (InsufficientStockException) {
            // expected
        }

        $this->assertSame(0, $product->inventoryTransactions()->count());
    }

    public function test_adjust_to_sets_an_absolute_quantity_and_logs_the_delta(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 10]);

        $transaction = $this->service->adjustTo($product, 25, ['notes' => 'stock count']);

        $this->assertSame(25, $product->fresh()->stock_quantity);
        $this->assertSame(15, $transaction->quantity);
        $this->assertSame('adjustment', $transaction->type);
    }

    public function test_zero_quantity_is_rejected(): void
    {
        $product = Product::factory()->create();

        $this->expectException(InvalidArgumentException::class);

        $this->service->increase($product, 0, 'purchase');
    }

    public function test_created_by_defaults_to_the_authenticated_user(): void
    {
        $business = Business::factory()->create();
        $user = User::factory()->create(['business_id' => $business->id]);
        $product = Product::factory()->create(['business_id' => $business->id, 'stock_quantity' => 5]);

        $this->actingAs($user);

        $transaction = $this->service->increase($product, 2, 'purchase');

        $this->assertSame($user->id, $transaction->created_by);
    }
}
