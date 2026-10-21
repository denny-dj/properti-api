<?php

namespace Database\Seeders;

use App\Enums\RoleName;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    /** Permission khusus administrator. */
    public const ADMIN_PERMISSIONS = [
        'users.manage',
        'partners.verify',
        'properties.verify',
        'bookings.view',
        'transactions.view',
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::ADMIN_PERMISSIONS as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        foreach (RoleName::cases() as $role) {
            Role::findOrCreate($role->value, 'web');
        }

        Role::findByName(RoleName::Admin->value, 'web')->syncPermissions(self::ADMIN_PERMISSIONS);
    }
}
