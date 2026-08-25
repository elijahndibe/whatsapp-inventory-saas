<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlansSeeder extends Seeder
{
    public function run(): void
    {
        Plan::updateOrCreate(['slug' => 'free'], [
            'name' => 'Free',
            'price' => 0,
            'currency' => 'NGN',
            'duration_days' => 36500, // effectively indefinite
            'max_products' => 50,
            'max_orders_per_month' => 20,
            'max_staff' => 1,
            'max_locations' => 1,
            'features' => [
                'whatsapp_ordering' => true,
                'basic_inventory' => true,
                'paystack' => false,
                'invoices' => false,
                'whatsapp_cloud_api' => false,
                'advanced_analytics' => false,
                'priority_support' => false,
            ],
            'is_active' => true,
            'sort_order' => 1,
        ]);

        Plan::updateOrCreate(['slug' => 'starter'], [
            'name' => 'Starter',
            'price' => 5000,
            'currency' => 'NGN',
            'duration_days' => 30,
            'max_products' => 500,
            'max_orders_per_month' => null,
            'max_staff' => 3,
            'max_locations' => 1,
            'features' => [
                'whatsapp_ordering' => true,
                'basic_inventory' => true,
                'paystack' => true,
                'invoices' => true,
                'whatsapp_cloud_api' => false,
                'advanced_analytics' => false,
                'priority_support' => false,
            ],
            'is_active' => true,
            'sort_order' => 2,
        ]);

        Plan::updateOrCreate(['slug' => 'business'], [
            'name' => 'Business',
            'price' => 15000,
            'currency' => 'NGN',
            'duration_days' => 30,
            'max_products' => null,
            'max_orders_per_month' => null,
            'max_staff' => null,
            'max_locations' => null,
            'features' => [
                'whatsapp_ordering' => true,
                'basic_inventory' => true,
                'paystack' => true,
                'invoices' => true,
                'whatsapp_cloud_api' => true,
                'advanced_analytics' => true,
                'priority_support' => true,
            ],
            'is_active' => true,
            'sort_order' => 3,
        ]);
    }
}
