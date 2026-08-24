<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'business_id' => Business::factory(),
            'customer_id' => Customer::factory(),
            'subtotal' => fake()->randomFloat(2, 10, 1000),
            'total' => fake()->randomFloat(2, 10, 1000),
            'currency' => 'NGN',
            'payment_method' => 'whatsapp',
        ];
    }
}
