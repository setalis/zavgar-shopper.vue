<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\HasCatalogTranslations;
use App\Concerns\InteractsWithStorefrontMedia;
use App\Concerns\ResolvesStorefrontPrice;
use App\Concerns\ResolvesStorefrontReviews;
use App\Concerns\ResolvesStorefrontStock;
use App\Support\StorefrontLocale;
use App\Traits\HasProductPricing;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Shopper\Core\Models\Price;
use Shopper\Models\Product as Model;

final class Product extends Model
{
    use HasCatalogTranslations;
    use HasProductPricing;
    use InteractsWithStorefrontMedia;
    use ResolvesStorefrontPrice;
    use ResolvesStorefrontReviews;
    use ResolvesStorefrontStock;

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    #[Scope]
    protected function matchingSearch(Builder $query, string $term): Builder
    {
        $escaped = str_replace(['%', '_'], ['\%', '\_'], $term);
        $like = "%{$escaped}%";
        $table = $query->getModel()->getTable();

        return $query->where(function (Builder $query) use ($like, $table): void {
            $query->where("{$table}.name", 'like', $like)
                ->orWhere("{$table}.sku", 'like', $like)
                ->when(
                    ! StorefrontLocale::isDefault(),
                    fn (Builder $query): Builder => $query->orWhereHas(
                        'translations',
                        fn (Builder $translations): Builder => $translations
                            ->where('locale', StorefrontLocale::current())
                            ->where('name', 'like', $like),
                    ),
                )
                ->orWhereHas(
                    'variants',
                    fn (Builder $variants): Builder => $variants->where('sku', 'like', $like),
                );
        });
    }

    /** @param  Builder<self>  $query */
    #[Scope]
    protected function withCurrentPrices(Builder $query): Builder
    {
        $currencyCode = current_currency();
        $productsTable = $query->getModel()->getTable();

        return $query
            ->with([
                'prices' => fn ($q) => $q->whereRelation('currency', 'code', $currencyCode),
            ])
            ->addSelect([
                'min_variant_amount' => $this->minimumVariantPriceSubquery($productsTable, $currencyCode, 'amount'),
                'min_variant_compare_amount' => $this->minimumVariantPriceSubquery($productsTable, $currencyCode, 'compare_amount'),
            ]);
    }

    /**
     * SQL for the amount shown on the storefront: the product's own price,
     * otherwise the cheapest variant price in the current currency.
     *
     * @return array{sql: string, bindings: list<mixed>}
     */
    public function storefrontAmountSql(string $productsTable): array
    {
        $currencyCode = current_currency();
        $own = $this->ownPriceSubquery($productsTable, $currencyCode, 'amount');
        $variant = $this->minimumVariantPriceSubquery($productsTable, $currencyCode, 'amount');

        return [
            'sql' => sprintf('COALESCE((%s), (%s))', $own->toSql(), $variant->toSql()),
            'bindings' => [...$own->getBindings(), ...$variant->getBindings()],
        ];
    }

    private function ownPriceSubquery(string $productsTable, string $currencyCode, string $column): QueryBuilder
    {
        $pricesTable = shopper_table('prices');
        $currenciesTable = shopper_table('currencies');

        return Price::query()
            ->select("{$pricesTable}.{$column}")
            ->join($currenciesTable, "{$currenciesTable}.id", '=', "{$pricesTable}.currency_id")
            ->whereColumn("{$pricesTable}.priceable_id", "{$productsTable}.id")
            ->where("{$pricesTable}.priceable_type", 'product')
            ->where("{$currenciesTable}.code", $currencyCode)
            ->whereNotNull("{$pricesTable}.amount")
            ->limit(1)
            ->toBase();
    }

    private function minimumVariantPriceSubquery(string $productsTable, string $currencyCode, string $column): QueryBuilder
    {
        $pricesTable = shopper_table('prices');
        $variantsTable = shopper_table('product_variants');
        $currenciesTable = shopper_table('currencies');

        return Price::query()
            ->select("{$pricesTable}.{$column}")
            ->join($variantsTable, function ($join) use ($pricesTable, $variantsTable): void {
                $join->on("{$pricesTable}.priceable_id", '=', "{$variantsTable}.id")
                    ->where("{$pricesTable}.priceable_type", 'variant');
            })
            ->join($currenciesTable, "{$currenciesTable}.id", '=', "{$pricesTable}.currency_id")
            ->whereColumn("{$variantsTable}.product_id", "{$productsTable}.id")
            ->where("{$currenciesTable}.code", $currencyCode)
            ->whereNotNull("{$pricesTable}.amount")
            ->orderBy("{$pricesTable}.amount")
            ->limit(1)
            ->toBase();
    }
}
