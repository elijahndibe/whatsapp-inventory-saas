<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryManagementTest extends TestCase
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

    public function test_owner_can_increase_stock_via_the_adjustment_endpoint(): void
    {
        $product = Product::factory()->create(['business_id' => $this->business->id, 'stock_quantity' => 10]);

        $response = $this->actingAs($this->owner)->post(route('products.inventory.adjust', $product), [
            'mode' => 'increase',
            'quantity' => 5,
            'type' => 'purchase',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');
        $this->assertSame(15, $product->fresh()->stock_quantity);
    }

    public function test_decreasing_below_zero_shows_an_error_and_does_not_change_stock(): void
    {
        $product = Product::factory()->create(['business_id' => $this->business->id, 'stock_quantity' => 2]);

        $response = $this->actingAs($this->owner)->post(route('products.inventory.adjust', $product), [
            'mode' => 'decrease',
            'quantity' => 10,
            'type' => 'sale',
        ]);

        $response->assertSessionHas('error');
        $this->assertSame(2, $product->fresh()->stock_quantity);
    }

    public function test_a_staff_member_without_adjust_permission_cannot_change_stock(): void
    {
        $staff = User::factory()->create(['business_id' => $this->business->id]);
        $staff->assignRole('Staff');
        $product = Product::factory()->create(['business_id' => $this->business->id, 'stock_quantity' => 10]);

        $response = $this->actingAs($staff)->post(route('products.inventory.adjust', $product), [
            'mode' => 'increase',
            'quantity' => 5,
            'type' => 'purchase',
        ]);

        $response->assertForbidden();
        $this->assertSame(10, $product->fresh()->stock_quantity);
    }

    public function test_low_stock_and_out_of_stock_products_appear_on_the_inventory_overview(): void
    {
        $low = Product::factory()->lowStock()->create(['business_id' => $this->business->id, 'name' => 'Low Item']);
        $out = Product::factory()->outOfStock()->create(['business_id' => $this->business->id, 'name' => 'Out Item']);
        Product::factory()->create(['business_id' => $this->business->id, 'name' => 'Healthy Item', 'stock_quantity' => 50, 'low_stock_threshold' => 5]);

        $response = $this->actingAs($this->owner)->get(route('inventory.index'));

        $response->assertOk();
        $response->assertSeeText('Low Item');
        $response->assertSeeText('Out Item');
    }

    public function test_stock_history_is_visible_for_a_product(): void
    {
        $product = Product::factory()->create(['business_id' => $this->business->id, 'stock_quantity' => 10]);
        app(\App\Services\InventoryService::class)->increase($product, 5, 'purchase', ['notes' => 'restock']);

        $response = $this->actingAs($this->owner)->get(route('products.inventory.history', $product));

        $response->assertOk();
        $response->assertSeeText('restock');
    }
}
