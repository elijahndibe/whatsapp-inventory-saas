<?php

namespace Tests\Unit;

use App\Models\Business;
use App\Models\BusinessLocation;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class StockTransferTest extends TestCase
{
    use RefreshDatabase;

    private InventoryService $service;
    private Business $business;
    private Product $product;
    private BusinessLocation $main;
    private BusinessLocation $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(InventoryService::class);
        $this->business = Business::factory()->create();
        $this->product = Product::factory()->create(['business_id' => $this->business->id, 'stock_quantity' => 20]);
        $this->main = $this->business->locations()->create(['name' => 'Main', 'status' => 'active', 'is_default' => true]);
        $this->branch = $this->business->locations()->create(['name' => 'Branch', 'status' => 'active']);
    }

    public function test_setting_location_stock_creates_an_allocation_row(): void
    {
        $this->service->setLocationStock($this->product, $this->main, 15);

        $this->assertSame(15, $this->product->locationStock()->where('location_id', $this->main->id)->first()->quantity);
        $this->assertSame(20, $this->product->fresh()->stock_quantity, 'Aggregate stock must be unaffected by location allocation.');
    }

    public function test_transferring_stock_moves_it_between_locations_without_touching_the_aggregate(): void
    {
        $this->service->setLocationStock($this->product, $this->main, 10);

        $this->service->transferStock($this->product, $this->main, $this->branch, 4);

        $this->assertSame(6, $this->product->locationStock()->where('location_id', $this->main->id)->first()->quantity);
        $this->assertSame(4, $this->product->locationStock()->where('location_id', $this->branch->id)->first()->quantity);
        $this->assertSame(20, $this->product->fresh()->stock_quantity);
    }

    public function test_transferring_more_than_allocated_is_rejected(): void
    {
        $this->service->setLocationStock($this->product, $this->main, 3);

        $this->expectException(InvalidArgumentException::class);

        $this->service->transferStock($this->product, $this->main, $this->branch, 5);
    }

    public function test_transferring_to_the_same_location_is_rejected(): void
    {
        $this->service->setLocationStock($this->product, $this->main, 10);

        $this->expectException(InvalidArgumentException::class);

        $this->service->transferStock($this->product, $this->main, $this->main, 1);
    }

    public function test_a_transfer_is_logged_with_from_and_to_locations(): void
    {
        $this->service->setLocationStock($this->product, $this->main, 10);

        $transaction = $this->service->transferStock($this->product, $this->main, $this->branch, 4);

        $this->assertSame('transfer', $transaction->type);
        $this->assertSame($this->main->id, $transaction->from_location_id);
        $this->assertSame($this->branch->id, $transaction->to_location_id);
        $this->assertSame(0, $transaction->quantity);
    }

    public function test_a_rejected_transfer_does_not_move_any_stock(): void
    {
        $this->service->setLocationStock($this->product, $this->main, 3);

        try {
            $this->service->transferStock($this->product, $this->main, $this->branch, 5);
        } catch (InvalidArgumentException) {
            // expected
        }

        $this->assertSame(3, $this->product->locationStock()->where('location_id', $this->main->id)->first()->quantity);
        $this->assertSame(0, InventoryTransaction::where('type', 'transfer')->count());
    }
}
