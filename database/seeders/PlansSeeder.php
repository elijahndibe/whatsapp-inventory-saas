<?php

namespace Database\Seeders;

use App\Models\Feature;
use App\Models\Plan;
use App\Models\PlanFeature;
use Illuminate\Database\Seeder;

class PlansSeeder extends Seeder
{
    public function run(): void
    {
        $free = Plan::updateOrCreate(['slug' => 'free'], [
            'name' => 'Free',
            'is_default' => true,
            'price' => 0,
            'currency' => 'NGN',
            'duration_days' => 36500, // effectively indefinite
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $pro = Plan::updateOrCreate(['slug' => 'pro'], [
            'name' => 'Pro',
            'is_default' => false,
            'price' => 5000,
            'currency' => 'NGN',
            'duration_days' => 30,
            'is_active' => true,
            'sort_order' => 2,
        ]);

        $business = Plan::updateOrCreate(['slug' => 'business'], [
            'name' => 'Business',
            'is_default' => false,
            'price' => 15000,
            'currency' => 'NGN',
            'duration_days' => 30,
            'is_active' => true,
            'sort_order' => 3,
        ]);

        // Every seller — Free included — can accept Paystack payments: the
        // commission model earns nothing if Free-tier sellers can't take
        // online payments. Only genuinely advanced functionality is gated.
        $matrix = [
            'products' => ['free' => 50, 'pro' => 500, 'business' => null],
            'orders_per_month' => ['free' => null, 'pro' => null, 'business' => null],
            'staff' => ['free' => 0, 'pro' => 5, 'business' => 20],
            'locations' => ['free' => 1, 'pro' => 3, 'business' => null],
            'paystack' => ['free' => true, 'pro' => true, 'business' => true],
            // Free for every plan today, per product decision — but wired
            // through the same plan matrix as everything else, so it can
            // be turned into a Pro/Business upsell later with an admin
            // toggle on the Features page, no code change required.
            'coupons' => ['free' => true, 'pro' => true, 'business' => true],
            'invoices' => ['free' => true, 'pro' => true, 'business' => true],
            'advanced_analytics' => ['free' => false, 'pro' => true, 'business' => true],
            'advanced_reports' => ['free' => false, 'pro' => true, 'business' => true],
            'whatsapp_cloud_api' => ['free' => false, 'pro' => false, 'business' => true],
            'priority_support' => ['free' => false, 'pro' => false, 'business' => true],
        ];

        $plans = ['free' => $free, 'pro' => $pro, 'business' => $business];

        foreach ($matrix as $featureKey => $byPlan) {
            $feature = Feature::where('key', $featureKey)->first();

            if (! $feature) {
                continue; // FeaturesSeeder hasn't run yet in a non-standard call order
            }

            foreach ($byPlan as $slug => $value) {
                PlanFeature::updateOrCreate(
                    ['plan_id' => $plans[$slug]->id, 'feature_id' => $feature->id],
                    $feature->type === Feature::TYPE_LIMIT
                        ? ['enabled' => true, 'value' => $value]
                        : ['enabled' => (bool) $value, 'value' => null],
                );
            }
        }
    }
}
