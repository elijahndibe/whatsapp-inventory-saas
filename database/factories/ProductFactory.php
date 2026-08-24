<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'business_id' => Business::factory(),
            'name' => fake()->unique()->words(3, true),
            'price' => fake()->randomFloat(2, 5, 500),
            'stock_quantity' => 20,
            'low_stock_threshold' => 5,
            'status' => 'active',
        ];
    }

    public function lowStock(): static
    {
        return $this->state(fn () => ['stock_quantity' => 3, 'low_stock_threshold' => 5]);
    }

    public function outOfStock(): static
    {
        return $this->state(fn () => ['stock_quantity' => 0]);
    }
}
