<?php

declare(strict_types=1);

use Database\Seeders\MenuItemPermissionsSeeder;
use Illuminate\Database\Migrations\Migration;
use Shopper\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        (new MenuItemPermissionsSeeder)->run();
    }

    public function down(): void
    {
        Permission::query()
            ->whereIn('name', [
                'browse_menu_items',
                'read_menu_items',
                'edit_menu_items',
                'add_menu_items',
                'delete_menu_items',
            ])
            ->delete();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
