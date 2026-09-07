<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Shopper\Models\Permission;
use Shopper\Models\Role;
use Spatie\Permission\PermissionRegistrar;

final class MenuItemPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        Permission::generate('menu_items');

        $permissions = [
            'browse_menu_items',
            'read_menu_items',
            'edit_menu_items',
            'add_menu_items',
            'delete_menu_items',
        ];

        Role::query()
            ->whereIn('name', [
                config('shopper.admin.roles.admin'),
                config('shopper.admin.roles.manager'),
            ])
            ->get()
            ->each(fn (Role $role) => $role->givePermissionTo($permissions));

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
