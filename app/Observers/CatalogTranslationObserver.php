<?php

declare(strict_types=1);

namespace App\Observers;

use App\Actions\FlushStorefrontCategoryCache;
use App\Models\CatalogTranslation;
use App\Models\Category;

final class CatalogTranslationObserver
{
    public function saved(CatalogTranslation $translation): void
    {
        $this->flushIfCategory($translation);
    }

    public function deleted(CatalogTranslation $translation): void
    {
        $this->flushIfCategory($translation);
    }

    private function flushIfCategory(CatalogTranslation $translation): void
    {
        if ($translation->translatable_type !== (new Category)->getMorphClass()) {
            return;
        }

        FlushStorefrontCategoryCache::flush();
    }
}
