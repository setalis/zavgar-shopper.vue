<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Product;
use App\Models\ProductVariant;
use Shopper\Core\Models\AttributeProduct;

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
        $product->loadMissing('attributeProducts.attribute', 'attributeProducts.value');

        return $this->joinPairs(
            $product->attributeProducts
                ->filter(fn (AttributeProduct $row): bool => $row->attribute !== null && filled($row->real_value))
                ->map(fn (AttributeProduct $row): string => $row->attribute->name.'='.$row->real_value)
                ->all(),
        );
    }

    public function forVariant(ProductVariant $variant): string
    {
        $variant->loadMissing([
            'values.attribute',
            'product.attributeProducts.attribute',
            'product.attributeProducts.value',
        ]);

        $variantAttributeIds = $variant->values->pluck('attribute_id')->all();
        $pairs = [];

        foreach ($variant->product->attributeProducts as $row) {
            if ($row->attribute === null || blank($row->real_value)) {
                continue;
            }

            if (in_array($row->attribute_id, $variantAttributeIds, true)) {
                continue;
            }

            $pairs[] = $row->attribute->name.'='.$row->real_value;
        }

        foreach ($variant->values as $value) {
            if ($value->attribute === null) {
                continue;
            }

            $pairs[] = $value->attribute->name.'='.$value->value;
        }

        return $this->joinPairs($pairs);
    }

    public function toString(ProductVariant $variant): string
    {
        return $this->forVariant($variant);
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
