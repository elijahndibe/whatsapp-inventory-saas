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

    public function test_owner_can_create_a_product_with_a_brand_new_category_without_leaving_the_form(): void
    {
        $response = $this->actingAs($this->owner)->post(route('products.store'), [
            'category_id' => 'new',
            'new_category_name' => 'Sneakers',
            'name' => 'Air Max',
            'price' => 25000,
            'stock_quantity' => 5,
            'low_stock_threshold' => 2,
            'status' => 'active',
        ]);

        $product = Product::where('name', 'Air Max')->firstOrFail();
        $response->assertRedirect(route('products.edit', $product));

        $category = Category::where('business_id', $this->business->id)->where('name', 'Sneakers')->firstOrFail();
        $this->assertSame($category->id, $product->category_id);
        $this->assertSame('active', $category->status);
    }

    public function test_submitting_the_same_new_category_name_twice_reuses_it_instead_of_duplicating(): void
    {
        $this->actingAs($this->owner)->post(route('products.store'), [
            'category_id' => 'new', 'new_category_name' => 'Sneakers',
            'name' => 'Air Max', 'price' => 25000, 'stock_quantity' => 5, 'low_stock_threshold' => 2, 'status' => 'active',
        ]);
        $this->actingAs($this->owner)->post(route('products.store'), [
            'category_id' => 'new', 'new_category_name' => 'Sneakers',
            'name' => 'Air Force 1', 'price' => 20000, 'stock_quantity' => 5, 'low_stock_threshold' => 2, 'status' => 'active',
        ]);

        $this->assertSame(1, Category::where('business_id', $this->business->id)->where('name', 'Sneakers')->count());
        $this->assertSame(2, Product::where('business_id', $this->business->id)->count());
    }

    public function test_a_new_category_name_is_required_when_add_new_category_is_selected(): void
    {
        $response = $this->actingAs($this->owner)->post(route('products.store'), [
            'category_id' => 'new',
            'name' => 'Air Max',
            'price' => 25000,
            'stock_quantity' => 5,
            'low_stock_threshold' => 2,
            'status' => 'active',
        ]);

        $response->assertSessionHasErrors('new_category_name');
        $this->assertSame(0, Product::where('name', 'Air Max')->count());
    }

    public function test_owner_can_reassign_a_product_to_a_brand_new_category_when_editing(): void
    {
        $existingCategory = Category::factory()->create(['business_id' => $this->business->id]);
        $product = Product::factory()->create(['business_id' => $this->business->id, 'category_id' => $existingCategory->id]);

        $this->actingAs($this->owner)->put(route('products.update', $product), [
            'category_id' => 'new',
            'new_category_name' => 'Footwear',
            'name' => $product->name,
            'price' => $product->price,
            'low_stock_threshold' => 5,
            'status' => 'active',
        ]);

        $newCategory = Category::where('business_id', $this->business->id)->where('name', 'Footwear')->firstOrFail();
        $this->assertSame($newCategory->id, $product->fresh()->category_id);
    }

    public function test_a_new_category_created_inline_is_scoped_to_the_current_business_only(): void
    {
        $otherBusiness = Business::factory()->create();
        Category::factory()->create(['business_id' => $otherBusiness->id, 'name' => 'Sneakers']);

        $this->actingAs($this->owner)->post(route('products.store'), [
            'category_id' => 'new', 'new_category_name' => 'Sneakers',
            'name' => 'Air Max', 'price' => 25000, 'stock_quantity' => 5, 'low_stock_threshold' => 2, 'status' => 'active',
        ]);

        $this->assertSame(1, Category::where('business_id', $this->business->id)->where('name', 'Sneakers')->count());
        $this->assertSame(1, Category::withoutGlobalScopes()->where('business_id', $otherBusiness->id)->where('name', 'Sneakers')->count());
    }

    public function test_the_create_product_form_offers_the_curated_suggested_categories(): void
    {
        $response = $this->actingAs($this->owner)->get(route('products.create'));

        $response->assertOk();
        $response->assertViewHas('suggestedCategories', function (array $byDepartment) {
            return in_array('Phones & Tablets', $byDepartment['Electronics'] ?? [], true);
        });
        $response->assertSee('suggested:Phones &amp; Tablets', false);
    }

    public function test_picking_a_suggested_category_creates_it_for_this_business(): void
    {
        $response = $this->actingAs($this->owner)->post(route('products.store'), [
            'category_id' => 'suggested:Phones & Tablets',
            'name' => 'iPhone Case',
            'price' => 5000,
            'stock_quantity' => 10,
            'low_stock_threshold' => 2,
            'status' => 'active',
        ]);

        $product = Product::where('name', 'iPhone Case')->firstOrFail();
        $response->assertRedirect(route('products.edit', $product));

        $category = Category::where('business_id', $this->business->id)->where('name', 'Phones & Tablets')->firstOrFail();
        $this->assertSame($category->id, $product->category_id);
        $this->assertSame('active', $category->status);
    }

    public function test_picking_the_same_suggested_category_twice_reuses_it_instead_of_duplicating(): void
    {
        $this->actingAs($this->owner)->post(route('products.store'), [
            'category_id' => 'suggested:Books', 'name' => 'Novel One',
            'price' => 3000, 'stock_quantity' => 5, 'low_stock_threshold' => 1, 'status' => 'active',
        ]);
        $this->actingAs($this->owner)->post(route('products.store'), [
            'category_id' => 'suggested:Books', 'name' => 'Novel Two',
            'price' => 3500, 'stock_quantity' => 5, 'low_stock_threshold' => 1, 'status' => 'active',
        ]);

        $this->assertSame(1, Category::where('business_id', $this->business->id)->where('name', 'Books')->count());
    }

    public function test_a_category_already_added_is_not_offered_as_a_suggestion_again(): void
    {
        Category::factory()->create(['business_id' => $this->business->id, 'name' => 'Books']);

        $response = $this->actingAs($this->owner)->get(route('products.create'));

        $response->assertViewHas('suggestedCategories', function (array $byDepartment) {
            return ! in_array('Books', $byDepartment['Books, Office & Stationery'] ?? [], true);
        });
    }

    public function test_an_unknown_suggested_category_name_is_rejected(): void
    {
        $response = $this->actingAs($this->owner)->post(route('products.store'), [
            'category_id' => 'suggested:Not A Real Category',
            'name' => 'Air Max', 'price' => 25000, 'stock_quantity' => 5, 'low_stock_threshold' => 2, 'status' => 'active',
        ]);

        $response->assertSessionHasErrors('category_id');
        $this->assertNull(Product::where('name', 'Air Max')->first());
    }
}
