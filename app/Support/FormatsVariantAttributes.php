<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Product;
use App\Models\ProductVariant;
use Shopper\Core\Models\AttributeProduct;
use Shopper\Core\Models\AttributeValue;

final class FormatsVariantAttributes
{
    /**
     * @return list<array{name: string, value: string}>
     */
    public function parse(?string $attributes): array
    {
        if (! is_string($attributes) || blank($attributes)) {
            return [];
        }

        $pairs = [];

        foreach (explode('|', $attributes) as $part) {
            $part = trim($part);

            if ($part === '' || ! str_contains($part, '=')) {
                continue;
            }

            [$name, $value] = explode('=', $part, 2);
            $name = trim($name);
            $value = trim($value);

            if ($name === '' || $value === '') {
                continue;
            }

            $pairs[] = [
                'name' => $name,
                'value' => $value,
            ];
        }

        return $pairs;
    }

    public function forCategories(Product $product): string
    {
        $product->loadMissing('categories.parent');

        return $product->categories
            ->map(fn ($category): string => $category->getLabelOptionName())
            ->filter()
            ->unique()
            ->sort()
            ->implode(' | ');
    }

    public function forProduct(Product $product): string
    {
        $product->loadMissing([
            'attributeProducts.attribute',
            'attributeProducts.value',
            'variants.values',
        ]);

        $dimensionIds = $product->variants
            ->flatMap(fn (ProductVariant $variant): array => $variant->values->pluck('attribute_id')->all())
            ->map(intval(...))
            ->unique()
            ->all();

        return $this->joinPairs(
            $product->attributeProducts
                ->filter(fn (AttributeProduct $row): bool => $row->attribute !== null && filled($row->real_value))
                ->reject(fn (AttributeProduct $row): bool => in_array((int) $row->attribute_id, $dimensionIds, true))
                ->map(fn (AttributeProduct $row): string => $row->attribute->name.'='.$row->real_value)
                ->all(),
        );
    }

    public function forVariant(ProductVariant $variant): string
    {
        $variant->loadMissing('product');

        return $this->forProduct($variant->product);
    }

    public function forVariantDimensions(ProductVariant $variant): string
    {
        $variant->loadMissing('values.attribute');

        return $this->joinPairs(
            $variant->values
                ->filter(fn (AttributeValue $value): bool => $value->attribute !== null)
                ->map(fn (AttributeValue $value): string => $value->attribute->name.'='.$value->value)
                ->all(),
        );
    }

    public function toString(ProductVariant $variant): string
    {
        return $this->forVariantDimensions($variant);
    }

    /**
     * @param  list<string>  $pairs
     */
    private function joinPairs(array $pairs): string
    {
        $unique = array_values(array_unique($pairs));
        sort($unique);

        return implode(' | ', $unique);
    }
}
