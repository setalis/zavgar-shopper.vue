<?php

declare(strict_types=1);

namespace App\Models;

use App\Actions\LocalizeCatalog;
use App\Concerns\HasCatalogTranslations;
use App\Concerns\InteractsWithStorefrontMedia;
use App\Support\StorefrontLocale;
use App\Traits\HasProductPricing;
use Shopper\Core\Models\AttributeValue;
use Shopper\Models\ProductVariant as Model;

final class ProductVariant extends Model
{
    use HasCatalogTranslations;
    use HasProductPricing;
    use InteractsWithStorefrontMedia;

    /**
     * @return array<string, string>
     */
    public function catalogTranslationMap(): array
    {
        return [
            'name' => 'name',
        ];
    }

    public function localizeForStorefront(): static
    {
        resolve(LocalizeCatalog::class)->handle($this);

        if (StorefrontLocale::isDefault()) {
            return $this;
        }

        if (filled($this->catalogTranslation()?->name)) {
            return $this;
        }

        $this->loadMissing('values.attribute');

        $localizer = resolve(LocalizeCatalog::class);
        $hasTranslatedValue = false;
        $parts = [];

        foreach ($this->values as $value) {
            if (! $value instanceof AttributeValue) {
                continue;
            }

            $original = $value->value;
            $localizer->handle($value);

            if ($value->value !== $original) {
                $hasTranslatedValue = true;
            }

            if (filled($value->value)) {
                $parts[] = $value->value;
            }
        }

        if ($hasTranslatedValue && $parts !== []) {
            $this->setAttribute('name', implode(' / ', $parts));
        }

        return $this;
    }
}
