<?php

declare(strict_types=1);

use Database\Seeders\HomepageBannerPermissionsSeeder;
use Illuminate\Database\Migrations\Migration;
use Shopper\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        (new HomepageBannerPermissionsSeeder)->run();
    }

    public function down(): void
    {
        Permission::query()
            ->whereIn('name', [
                'browse_homepage_banners',
                'read_homepage_banners',
                'edit_homepage_banners',
                'add_homepage_banners',
                'delete_homepage_banners',
            ])
            ->delete();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
