<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Category;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductManagementTest extends TestCase
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

    public function test_owner_can_view_the_products_index(): void
    {
        Product::factory()->count(3)->create(['business_id' => $this->business->id]);

        $response = $this->actingAs($this->owner)->get(route('products.index'));

        $response->assertOk();
    }

    public function test_owner_can_view_the_create_product_form(): void
    {
        $response = $this->actingAs($this->owner)->get(route('products.create'));

        $response->assertOk();
    }

    public function test_owner_can_view_the_edit_product_form(): void
    {
        $product = Product::factory()->create(['business_id' => $this->business->id]);

        $response = $this->actingAs($this->owner)->get(route('products.edit', $product));

        $response->assertOk();
    }

    public function test_owner_can_create_a_product_with_initial_stock_and_an_image(): void
    {
        Storage::fake('public');
        $category = Category::factory()->create(['business_id' => $this->business->id]);

        $response = $this->actingAs($this->owner)->post(route('products.store'), [
            'category_id' => $category->id,
            'name' => 'Ankara Dress',
            'sku' => 'ANK-001',
            'price' => 15000,
            'cost_price' => 9000,
            'stock_quantity' => 20,
            'low_stock_threshold' => 5,
            'status' => 'active',
            'images' => [UploadedFile::fake()->image('dress.jpg')],
        ]);

        $product = Product::where('name', 'Ankara Dress')->firstOrFail();
        $response->assertRedirect(route('products.edit', $product));

        $this->assertSame(20, $product->stock_quantity);
        $this->assertEquals(15000, $product->price);
        $this->assertSame(1, $product->images()->count());
        $this->assertSame(
            1,
            InventoryTransaction::where('product_id', $product->id)->where('type', 'purchase')->count(),
            'Initial stock must be logged as an inventory transaction, not silently set.'
        );

        Storage::disk('public')->assertExists($product->images->first()->path);
    }

    public function test_sku_must_be_unique_within_the_business(): void
    {
        Product::factory()->create(['business_id' => $this->business->id, 'sku' => 'DUPLICATE']);

        $response = $this->actingAs($this->owner)->post(route('products.store'), [
            'name' => 'Another Product',
            'sku' => 'DUPLICATE',
            'price' => 1000,
            'stock_quantity' => 1,
            'low_stock_threshold' => 1,
            'status' => 'active',
        ]);

        $response->assertSessionHasErrors('sku');
    }

    public function test_the_same_sku_is_allowed_across_different_businesses(): void
    {
        $otherBusiness = Business::factory()->create();
        Product::factory()->create(['business_id' => $otherBusiness->id, 'sku' => 'SHARED']);

        $response = $this->actingAs($this->owner)->post(route('products.store'), [
            'name' => 'My Product',
            'sku' => 'SHARED',
            'price' => 1000,
            'stock_quantity' => 1,
            'low_stock_threshold' => 1,
            'status' => 'active',
        ]);

        $response->assertSessionDoesntHaveErrors('sku');
    }

    public function test_editing_a_product_does_not_accept_a_stock_quantity_field(): void
    {
        $product = Product::factory()->create(['business_id' => $this->business->id, 'stock_quantity' => 10]);

        $this->actingAs($this->owner)->put(route('products.update', $product), [
            'name' => $product->name,
            'price' => $product->price,
            'low_stock_threshold' => 5,
            'status' => 'active',
            'stock_quantity' => 999, // must be ignored — stock changes only via InventoryService
        ]);

        $this->assertSame(10, $product->fresh()->stock_quantity);
    }

    public function test_a_user_cannot_edit_another_businesss_product(): void
    {
        $otherBusiness = Business::factory()->create();
        $foreignProduct = Product::factory()->create(['business_id' => $otherBusiness->id]);

        $response = $this->actingAs($this->owner)->get(route('products.edit', $foreignProduct));

        $response->assertNotFound();
    }

    public function test_a_staff_member_without_permission_cannot_view_products(): void
    {
        $staff = User::factory()->create(['business_id' => $this->business->id]);
        $staff->assignRole('Staff'); // Staff starts with zero permissions

        $response = $this->actingAs($staff)->get(route('products.index'));

        $response->assertForbidden();
    }

    public function test_owner_can_delete_a_product(): void
    {
        $product = Product::factory()->create(['business_id' => $this->business->id]);

        $response = $this->actingAs($this->owner)->delete(route('products.destroy', $product));

        $response->assertRedirect(route('products.index'));
        $this->assertModelMissing($product);
    }
}
