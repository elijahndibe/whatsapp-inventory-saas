<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_adding_a_product_to_the_cart_shows_it_on_the_cart_page(): void
    {
        $business = Business::factory()->create();
        $product = Product::factory()->create(['business_id' => $business->id, 'name' => 'Ankara Dress', 'stock_quantity' => 10]);

        $this->post(route('storefront.cart.store', $business), ['product_id' => $product->id, 'quantity' => 2]);
        $response = $this->get(route('storefront.cart.index', $business));

        $response->assertSeeText('Ankara Dress');
        $response->assertSeeText('2');
    }

    public function test_adding_more_than_available_stock_is_rejected(): void
    {
        $business = Business::factory()->create(['allow_overselling' => false]);
        $product = Product::factory()->create(['business_id' => $business->id, 'stock_quantity' => 3]);

        $response = $this->post(route('storefront.cart.store', $business), ['product_id' => $product->id, 'quantity' => 5]);

        $response->assertSessionHas('error');
        $cartResponse = $this->get(route('storefront.cart.index', $business));
        $cartResponse->assertSeeText('Your cart is empty');
    }

    public function test_a_product_from_another_business_cannot_be_added_to_this_businesss_cart(): void
    {
        $businessA = Business::factory()->create();
        $businessB = Business::factory()->create();
        $foreignProduct = Product::factory()->create(['business_id' => $businessB->id]);

        $response = $this->post(route('storefront.cart.store', $businessA), ['product_id' => $foreignProduct->id, 'quantity' => 1]);

        $response->assertNotFound();
    }

    public function test_updating_quantity_to_zero_removes_the_item(): void
    {
        $business = Business::factory()->create();
        $product = Product::factory()->create(['business_id' => $business->id, 'stock_quantity' => 10]);
        $this->post(route('storefront.cart.store', $business), ['product_id' => $product->id, 'quantity' => 2]);

        $this->patch(route('storefront.cart.update', [$business, $product]), ['quantity' => 0]);

        $response = $this->get(route('storefront.cart.index', $business));
        $response->assertSeeText('Your cart is empty');
    }

    public function test_removing_an_item_from_the_cart(): void
    {
        $business = Business::factory()->create();
        $product = Product::factory()->create(['business_id' => $business->id, 'stock_quantity' => 10]);
        $this->post(route('storefront.cart.store', $business), ['product_id' => $product->id, 'quantity' => 2]);

        $this->delete(route('storefront.cart.destroy', [$business, $product]));

        $response = $this->get(route('storefront.cart.index', $business));
        $response->assertSeeText('Your cart is empty');
    }

    public function test_carts_for_different_businesses_do_not_interfere(): void
    {
        $businessA = Business::factory()->create();
        $businessB = Business::factory()->create();
        $productA = Product::factory()->create(['business_id' => $businessA->id, 'name' => 'Product A', 'stock_quantity' => 10]);
        $productB = Product::factory()->create(['business_id' => $businessB->id, 'name' => 'Product B', 'stock_quantity' => 10]);

        $this->post(route('storefront.cart.store', $businessA), ['product_id' => $productA->id, 'quantity' => 1]);
        $cartA = $this->get(route('storefront.cart.index', $businessA));
        $cartA->assertSeeText('Product A');
        $cartA->assertDontSeeText('Product B');

        $this->post(route('storefront.cart.store', $businessB), ['product_id' => $productB->id, 'quantity' => 1]);
        $cartB = $this->get(route('storefront.cart.index', $businessB));
        $cartB->assertSeeText('Product B');
        $cartB->assertDontSeeText('Product A');
    }
}
