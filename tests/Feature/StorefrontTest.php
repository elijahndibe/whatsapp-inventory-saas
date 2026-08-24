<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_storefront_page_renders_for_an_active_business(): void
    {
        $business = Business::factory()->create();

        $response = $this->get(route('storefront.show', $business));

        $response->assertOk();
        $response->assertSeeText($business->name);
    }

    public function test_a_suspended_businesss_storefront_404s(): void
    {
        $business = Business::factory()->suspended()->create();

        $response = $this->get(route('storefront.show', $business));

        $response->assertNotFound();
    }

    /**
     * Critical: as a guest, Auth::check() is false, so BusinessScope (which
     * keys off the authenticated user's business_id) does nothing at all.
     * Every storefront query MUST scope explicitly via Product::forBusiness()
     * — this test exists to catch a regression where that scoping is
     * accidentally dropped and one business's storefront starts leaking
     * another business's products.
     */
    public function test_a_guest_never_sees_another_businesss_products_on_the_storefront(): void
    {
        $businessA = Business::factory()->create();
        $businessB = Business::factory()->create();

        Product::factory()->create(['business_id' => $businessA->id, 'name' => 'Business A Product', 'status' => 'active']);
        Product::factory()->create(['business_id' => $businessB->id, 'name' => 'Business B Product', 'status' => 'active']);

        $response = $this->get(route('storefront.show', $businessA));

        $response->assertSeeText('Business A Product');
        $response->assertDontSeeText('Business B Product');
    }

    public function test_only_active_products_appear_on_the_storefront(): void
    {
        $business = Business::factory()->create();
        Product::factory()->create(['business_id' => $business->id, 'name' => 'Visible Product', 'status' => 'active']);
        Product::factory()->create(['business_id' => $business->id, 'name' => 'Hidden Product', 'status' => 'inactive']);

        $response = $this->get(route('storefront.show', $business));

        $response->assertSeeText('Visible Product');
        $response->assertDontSeeText('Hidden Product');
    }

    public function test_the_product_detail_page_renders(): void
    {
        $business = Business::factory()->create();
        $product = Product::factory()->create(['business_id' => $business->id, 'name' => 'Ankara Dress', 'status' => 'active']);

        $response = $this->get(route('storefront.products.show', [$business, $product->slug]));

        $response->assertOk();
        $response->assertSeeText('Ankara Dress');
    }

    public function test_a_product_from_another_business_cannot_be_viewed_via_this_businesss_storefront_url(): void
    {
        $businessA = Business::factory()->create();
        $businessB = Business::factory()->create();
        $foreignProduct = Product::factory()->create(['business_id' => $businessB->id, 'status' => 'active']);

        $response = $this->get(route('storefront.products.show', [$businessA, $foreignProduct->slug]));

        $response->assertNotFound();
    }

    public function test_search_filters_products(): void
    {
        $business = Business::factory()->create();
        Product::factory()->create(['business_id' => $business->id, 'name' => 'Red Shoes', 'status' => 'active']);
        Product::factory()->create(['business_id' => $business->id, 'name' => 'Blue Hat', 'status' => 'active']);

        $response = $this->get(route('storefront.show', $business).'?search=Shoes');

        $response->assertSeeText('Red Shoes');
        $response->assertDontSeeText('Blue Hat');
    }

    public function test_category_filter_scopes_products_to_that_category(): void
    {
        $business = Business::factory()->create();
        $categoryA = Category::factory()->create(['business_id' => $business->id]);
        $categoryB = Category::factory()->create(['business_id' => $business->id]);
        Product::factory()->create(['business_id' => $business->id, 'category_id' => $categoryA->id, 'name' => 'In Category A', 'status' => 'active']);
        Product::factory()->create(['business_id' => $business->id, 'category_id' => $categoryB->id, 'name' => 'In Category B', 'status' => 'active']);

        $response = $this->get(route('storefront.show', $business).'?category_id='.$categoryA->id);

        $response->assertSeeText('In Category A');
        $response->assertDontSeeText('In Category B');
    }
}
