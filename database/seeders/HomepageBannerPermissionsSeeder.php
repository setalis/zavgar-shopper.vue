<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Shopper\Models\Permission;
use Shopper\Models\Role;
use Spatie\Permission\PermissionRegistrar;

final class HomepageBannerPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        Permission::generate('homepage_banners');

        $permissions = [
            'browse_homepage_banners',
            'read_homepage_banners',
            'edit_homepage_banners',
            'add_homepage_banners',
            'delete_homepage_banners',
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
