<?php

declare(strict_types=1);

namespace App\Observers;

use App\Actions\FlushStorefrontMenuCache;
use App\Models\MenuItem;

final class MenuItemObserver
{
    public function created(MenuItem $menuItem): void
    {
        FlushStorefrontMenuCache::flush();
    }

    public function updated(MenuItem $menuItem): void
    {
        FlushStorefrontMenuCache::flush();
    }

    public function deleted(MenuItem $menuItem): void
    {
        FlushStorefrontMenuCache::flush();
    }

    public function restored(MenuItem $menuItem): void
    {
        FlushStorefrontMenuCache::flush();
    }

    public function forceDeleted(MenuItem $menuItem): void
    {
        FlushStorefrontMenuCache::flush();
    }
}
