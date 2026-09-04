<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\Models\Brand;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Shopper\Core\Models\AttributeProduct;
use Shopper\Core\Models\AttributeValue;

final class ApplyCategoryAttributeFilters
{
    /**
     * @param  Builder<Product>  $query
     * @param  array<string, list<string>>  $selected
     * @param  list<array{id: int, name: string, slug: string, type: string, values: list<array{key: string, label: string}>}>  $facets
     * @return Builder<Product>
     */
    public function handle(Builder $query, array $selected, array $facets): Builder
    {
        $facetsBySlug = collect($facets)->keyBy('slug');
        $productsTable = $query->getModel()->getTable();

        foreach ($selected as $slug => $keys) {
            $facet = $facetsBySlug->get($slug);

            if (! is_array($facet)) {
                continue;
            }

            $allowedKeys = collect($facet['values'])->pluck('key')->all();
            $keys = array_values(array_intersect($keys, $allowedKeys));

            if ($keys === []) {
                continue;
            }

            if ($slug === BuildCategoryAttributeFilters::BRAND_SLUG) {
                $query->whereIn(
                    "{$productsTable}.brand_id",
                    Brand::query()
                        ->enabled()
                        ->select('id')
                        ->whereIn('slug', $keys),
                );

                continue;
            }

            $attributeId = $facet['id'];

            $query->whereIn(
                "{$productsTable}.id",
                AttributeProduct::query()
                    ->select('product_id')
                    ->where('attribute_id', $attributeId)
                    ->where(function ($constraint) use ($attributeId, $keys): void {
                        $constraint
                            ->whereIn('attribute_custom_value', $keys)
                            ->orWhereIn(
                                'attribute_value_id',
                                AttributeValue::query()
                                    ->select('id')
                                    ->where('attribute_id', $attributeId)
                                    ->whereIn('key', $keys),
                            );
                    }),
            );
        }

        return $query;
    }
}
