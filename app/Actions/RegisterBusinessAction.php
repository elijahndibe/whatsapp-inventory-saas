<?php

namespace App\Actions;

use App\Models\Business;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Creates a new tenant: the business plus its first user (the Owner).
 * Wrapped in a transaction so a failure never leaves an orphaned
 * business with no owner, or vice versa.
 */
class RegisterBusinessAction
{
    public function execute(array $data): User
    {
        return DB::transaction(function () use ($data) {
            // country/currency/timezone aren't collected at registration —
            // the businesses table's own defaults (Nigeria/NGN/Africa/Lagos)
            // apply, and Settings already has its own detect-and-select
            // fields for a business to correct them afterward.
            $business = Business::create([
                'name' => $data['business_name'],
                'phone' => $data['phone'],
                'whatsapp_number' => $data['phone'],
            ]);

            $user = User::create([
                'business_id' => $business->id,
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            $user->assignRole('Owner');

            if ($freePlan = Plan::where('slug', 'free')->first()) {
                $business->subscriptions()->create([
                    'plan_id' => $freePlan->id,
                    'status' => 'active',
                    'starts_at' => now(),
                    'ends_at' => null,
                ]);
            }

            return $user;
        });
    }
}
