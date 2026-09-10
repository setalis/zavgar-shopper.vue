<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\HasCatalogTranslations;
use App\Concerns\InteractsWithStorefrontMedia;
use Shopper\Models\Collection as Model;

final class Collection extends Model
{
    use HasCatalogTranslations;
    use InteractsWithStorefrontMedia;

    /**
     * @return array<string, string>
     */
    public function catalogTranslationMap(): array
    {
        return [
            'name' => 'name',
            'description' => 'description',
            'seo_title' => 'seo_title',
            'seo_description' => 'seo_description',
        ];
    }
}
