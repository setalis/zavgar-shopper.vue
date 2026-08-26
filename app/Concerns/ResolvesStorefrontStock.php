<?php

declare(strict_types=1);

namespace App\Concerns;

use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Shopper\Core\Enum\ProductType;
use Shopper\Core\Models\InventoryHistory;

trait ResolvesStorefrontStock
{
    public function initializeResolvesStorefrontStock(): void
    {
        $this->append(['storefront_stock']);
        $this->makeHidden(['storefront_variant_stock', 'storefront_own_stock']);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    #[Scope]
    protected function withCurrentStock(Builder $query): Builder
    {
        $productsTable = $query->getModel()->getTable();

        return $query->addSelect([
            'storefront_variant_stock' => $this->variantStockSubquery($productsTable),
            'storefront_own_stock' => $this->ownStockSubquery($productsTable),
        ]);
    }

    /**
     * @return Attribute<int, never>
     */
    protected function storefrontStock(): Attribute
    {
        return Attribute::get(function (): int {
            if ($this->type === ProductType::Variant) {
                if (array_key_exists('storefront_variant_stock', $this->attributes)) {
                    return (int) $this->attributes['storefront_variant_stock'];
                }

                if ($this->relationLoaded('variants')) {
                    return (int) $this->variants->sum(
                        fn ($variant): int => (int) $variant->stock,
                    );
                }
            }

            if (array_key_exists('storefront_own_stock', $this->attributes)) {
                return (int) $this->attributes['storefront_own_stock'];
            }

            return $this->getStock();
        });
    }

    private function variantStockSubquery(string $productsTable): QueryBuilder
    {
        $historiesTable = shopper_table('inventory_histories');
        $variantsTable = shopper_table('product_variants');

        return InventoryHistory::query()
            ->selectRaw("coalesce(sum({$historiesTable}.quantity), 0)")
            ->join($variantsTable, function ($join) use ($historiesTable, $variantsTable): void {
                $join->on("{$historiesTable}.stockable_id", '=', "{$variantsTable}.id")
                    ->whereIn("{$historiesTable}.stockable_type", $this->stockableTypes(ProductVariant::class));
            })
            ->whereColumn("{$variantsTable}.product_id", "{$productsTable}.id")
            ->where("{$historiesTable}.created_at", '<=', now())
            ->toBase();
    }

    private function ownStockSubquery(string $productsTable): QueryBuilder
    {
        $historiesTable = shopper_table('inventory_histories');

        return InventoryHistory::query()
            ->selectRaw("coalesce(sum({$historiesTable}.quantity), 0)")
            ->whereColumn("{$historiesTable}.stockable_id", "{$productsTable}.id")
            ->whereIn("{$historiesTable}.stockable_type", $this->stockableTypes(static::class))
            ->where("{$historiesTable}.created_at", '<=', now())
            ->toBase();
    }

    /**
     * @param  class-string  $class
     * @return list<string>
     */
    private function stockableTypes(string $class): array
    {
        $morphClass = (new $class)->getMorphClass();

        return array_values(array_unique([$morphClass, $class]));
    }
}
