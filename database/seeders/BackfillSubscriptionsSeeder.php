<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Database\Seeder;

/**
 * One-time catch-up for businesses created before subscriptions existed
 * (Phases 1-6). New registrations get a Free subscription automatically
 * via RegisterBusinessAction; this just brings existing data in line so
 * the plan/billing UI has something real to show for them too.
 */
class BackfillSubscriptionsSeeder extends Seeder
{
    public function run(): void
    {
        $freePlan = Plan::where('slug', 'free')->first();

        if (! $freePlan) {
            return;
        }

        Business::whereDoesntHave('subscriptions')->get()->each(function (Business $business) use ($freePlan) {
            Subscription::create([
                'business_id' => $business->id,
                'plan_id' => $freePlan->id,
                'status' => 'active',
                'starts_at' => now(),
                'ends_at' => null,
            ]);
        });
    }
}
