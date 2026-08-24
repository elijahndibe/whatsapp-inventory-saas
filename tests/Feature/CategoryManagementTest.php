<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryManagementTest extends TestCase
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

    public function test_owner_can_view_the_categories_index_and_create_form(): void
    {
        $this->actingAs($this->owner)->get(route('categories.index'))->assertOk();
        $this->actingAs($this->owner)->get(route('categories.create'))->assertOk();
    }

    public function test_owner_can_view_the_edit_category_form(): void
    {
        $category = Category::factory()->create(['business_id' => $this->business->id]);

        $this->actingAs($this->owner)->get(route('categories.edit', $category))->assertOk();
    }

    public function test_owner_can_create_a_category(): void
    {
        $response = $this->actingAs($this->owner)->post(route('categories.store'), [
            'name' => 'Dresses',
            'status' => 'active',
        ]);

        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseHas('categories', [
            'business_id' => $this->business->id,
            'name' => 'Dresses',
            'slug' => 'dresses',
        ]);
    }

    public function test_a_category_with_products_cannot_be_deleted(): void
    {
        $category = Category::factory()->create(['business_id' => $this->business->id]);
        Product::factory()->create(['business_id' => $this->business->id, 'category_id' => $category->id]);

        $response = $this->actingAs($this->owner)->delete(route('categories.destroy', $category));

        $response->assertSessionHas('error');
        $this->assertModelExists($category);
    }

    public function test_an_empty_category_can_be_deleted(): void
    {
        $category = Category::factory()->create(['business_id' => $this->business->id]);

        $response = $this->actingAs($this->owner)->delete(route('categories.destroy', $category));

        $response->assertRedirect(route('categories.index'));
        $this->assertModelMissing($category);
    }

    public function test_admin_cannot_be_denied_product_permissions_by_default(): void
    {
        $admin = User::factory()->create(['business_id' => $this->business->id]);
        $admin->assignRole('Admin');

        $response = $this->actingAs($admin)->get(route('categories.index'));

        $response->assertOk();
    }
}
