<?php

declare(strict_types=1);

namespace App\Observers;

use App\Actions\FlushStorefrontCategoryCache;
use App\Models\Category;

final class CategoryObserver
{
    public function created(Category $category): void
    {
        FlushStorefrontCategoryCache::flush();
    }

    public function updated(Category $category): void
    {
        FlushStorefrontCategoryCache::flush();
    }

    public function deleted(Category $category): void
    {
        FlushStorefrontCategoryCache::flush();
    }

    public function restored(Category $category): void
    {
        FlushStorefrontCategoryCache::flush();
    }

    public function forceDeleted(Category $category): void
    {
        FlushStorefrontCategoryCache::flush();
    }
}
