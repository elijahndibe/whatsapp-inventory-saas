<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Bootstraps the first platform super-admin. There is deliberately no
 * public registration path to super-admin — it must be provisioned here
 * or directly in the database, never self-service.
 */
class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        if (User::where('is_super_admin', true)->exists()) {
            return;
        }

        $email = 'superadmin@example.com';
        $password = 'Password123!';

        User::create([
            'business_id' => null,
            'name' => 'Platform Admin',
            'email' => $email,
            'password' => Hash::make($password),
            'is_super_admin' => true,
        ]);

        $this->command?->warn("Super admin created — email: {$email}  password: {$password} (change this in production)");
    }
}
