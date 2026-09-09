<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Shopper\Core\Models\Attribute;
use Shopper\Core\Models\AttributeProduct;
use Shopper\Core\Models\AttributeValue;

final class CatalogFieldMap
{
    /**
     * @return array<string, string>
     */
    public static function for(Model $model): array
    {
        if (method_exists($model, 'catalogTranslationMap')) {
            /** @var array<string, string> $map */
            $map = $model->catalogTranslationMap();

            return $map;
        }

        return match ($model::class) {
            Attribute::class => ['name' => 'name'],
            AttributeValue::class => ['value' => 'value'],
            AttributeProduct::class => ['attribute_custom_value' => 'value'],
            default => ['name' => 'name'],
        };
    }
}
