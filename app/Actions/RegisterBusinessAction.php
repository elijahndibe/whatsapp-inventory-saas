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
            // country/currency/timezone are auto-detected client-side
            // (resources/js/geo.js) but only ever sent when non-empty —
            // omitting the key here rather than passing an explicit null
            // lets the businesses table's own column defaults apply
            // exactly as they did before this field existed.
            $business = Business::create(array_filter([
                'name' => $data['business_name'],
                'phone' => $data['phone'] ?? null,
                'whatsapp_number' => $data['phone'] ?? null,
                'country' => $data['country'] ?? null,
                'currency' => $data['currency'] ?? null,
                'timezone' => $data['timezone'] ?? null,
            ], fn ($value) => $value !== null && $value !== ''));

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
