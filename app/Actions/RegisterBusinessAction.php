<?php

namespace App\Actions;

use App\Models\Business;
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
            $business = Business::create([
                'name' => $data['business_name'],
                'phone' => $data['phone'] ?? null,
                'whatsapp_number' => $data['phone'] ?? null,
            ]);

            $user = User::create([
                'business_id' => $business->id,
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            $user->assignRole('Owner');

            return $user;
        });
    }
}
