<?php

namespace Database\Seeders;

use App\Models\Feature;
use Illuminate\Database\Seeder;

/**
 * The platform-wide feature catalog. Each row's is_enabled is the global
 * kill-switch (Admin > Features); per-plan access/limits live in
 * plan_features, seeded by PlansSeeder. WhatsApp click-to-chat ordering is
 * deliberately NOT listed here — it's core and must never be gateable, so
 * no code path checks a feature key for it (see FeatureService).
 */
class FeaturesSeeder extends Seeder
{
    public function run(): void
    {
        $features = [
            ['key' => 'products', 'name' => 'Products', 'type' => Feature::TYPE_LIMIT, 'description' => 'Maximum number of products a business may list.'],
            ['key' => 'orders_per_month', 'name' => 'Orders per month', 'type' => Feature::TYPE_LIMIT, 'description' => 'Maximum orders a storefront may accept per calendar month.'],
            ['key' => 'staff', 'name' => 'Staff accounts', 'type' => Feature::TYPE_LIMIT, 'description' => 'Maximum additional staff/team member accounts.'],
            ['key' => 'locations', 'name' => 'Locations', 'type' => Feature::TYPE_LIMIT, 'description' => 'Maximum business locations/branches.'],
            ['key' => 'paystack', 'name' => 'Paystack payments', 'type' => Feature::TYPE_BOOLEAN, 'description' => 'Accept online payments via Paystack.'],
            ['key' => 'coupons', 'name' => 'Coupon codes', 'type' => Feature::TYPE_BOOLEAN, 'description' => 'Create discount codes customers can redeem at checkout.'],
            ['key' => 'invoices', 'name' => 'Invoices & receipts', 'type' => Feature::TYPE_BOOLEAN, 'description' => 'Generate PDF invoices and receipts.'],
            ['key' => 'whatsapp_cloud_api', 'name' => 'WhatsApp Cloud API', 'type' => Feature::TYPE_BOOLEAN, 'description' => 'Automated WhatsApp order status messages via Meta Cloud API.'],
            ['key' => 'advanced_analytics', 'name' => 'Advanced analytics', 'type' => Feature::TYPE_BOOLEAN, 'description' => 'Category breakdown and payment method charts on Reports.'],
            ['key' => 'advanced_reports', 'name' => 'Advanced reports', 'type' => Feature::TYPE_BOOLEAN, 'description' => 'Extended reporting beyond the basic sales timeline.'],
            ['key' => 'priority_support', 'name' => 'Priority support', 'type' => Feature::TYPE_BOOLEAN, 'description' => 'Priority customer support access.'],
        ];

        foreach ($features as $feature) {
            Feature::updateOrCreate(['key' => $feature['key']], $feature + ['is_enabled' => true]);
        }
    }
}
