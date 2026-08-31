<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * The full permission set available to businesses on the platform.
     * Adding a new permission here is the only place it needs to be
     * declared — controllers/policies should check against these names,
     * never hard-code role checks.
     */
    public const PERMISSIONS = [
        'view products', 'create products', 'edit products', 'delete products',
        'view inventory', 'adjust inventory',
        'view orders', 'update orders',
        'view customers', 'manage customers',
        'view reports',
        'manage coupons',
        'manage staff',
        'manage settings',
        'manage payments',
    ];

    /**
     * Owner-level permissions Admins are deliberately denied, per spec:
     * "Admin: Almost full business access but cannot perform sensitive
     * owner-level actions."
     */
    public const OWNER_ONLY_PERMISSIONS = [
        'manage staff',
        'manage settings',
        'manage payments',
    ];

    public function run(): void
    {
        // The permission/role cache can go stale mid-run (e.g. a prior
        // artisan invocation cached an empty set before these rows
        // existed), which makes the immediate syncPermissions() calls
        // below fail with PermissionDoesNotExist. Force a fresh read.
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::PERMISSIONS as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $owner = Role::findOrCreate('Owner', 'web');
        $owner->syncPermissions(self::PERMISSIONS);

        $admin = Role::findOrCreate('Admin', 'web');
        $admin->syncPermissions(array_diff(self::PERMISSIONS, self::OWNER_ONLY_PERMISSIONS));

        // Staff start with no permissions; the Owner/Admin grants them
        // individually per staff member from the staff management screen.
        Role::findOrCreate('Staff', 'web');
    }
}
