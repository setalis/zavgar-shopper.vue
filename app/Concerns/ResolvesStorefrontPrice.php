<?php

declare(strict_types=1);

namespace App\Concerns;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Shopper\Core\Models\Price;

trait ResolvesStorefrontPrice
{
    public function initializeResolvesStorefrontPrice(): void
    {
        $this->append(['storefront_price']);
        $this->makeHidden(['min_variant_amount', 'min_variant_compare_amount']);
    }

    /**
     * @return Attribute<array{amount: int, compare_amount: int|null, from: bool}|null, never>
     */
    protected function storefrontPrice(): Attribute
    {
        return Attribute::get(function (): ?array {
            $ownPrice = $this->resolveOwnStorefrontPrice();

            if ($ownPrice !== null) {
                return $ownPrice;
            }

            return $this->resolveMinimumVariantStorefrontPrice();
        });
    }

    /**
     * @return array{amount: int, compare_amount: int|null, from: bool}|null
     */
    private function resolveOwnStorefrontPrice(): ?array
    {
        if (! $this->relationLoaded('prices')) {
            return null;
        }

        /** @var Price|null $price */
        $price = $this->prices->first(
            fn (Price $price): bool => $price->amount !== null,
        );

        if ($price === null) {
            return null;
        }

        return [
            'amount' => (int) $price->amount,
            'compare_amount' => $price->compare_amount !== null
                ? (int) $price->compare_amount
                : null,
            'from' => false,
        ];
    }

    /**
     * @return array{amount: int, compare_amount: int|null, from: bool}|null
     */
    private function resolveMinimumVariantStorefrontPrice(): ?array
    {
        if (array_key_exists('min_variant_amount', $this->attributes)) {
            if ($this->attributes['min_variant_amount'] === null) {
                return null;
            }

            return [
                'amount' => (int) $this->attributes['min_variant_amount'],
                'compare_amount' => $this->attributes['min_variant_compare_amount'] !== null
                    ? (int) $this->attributes['min_variant_compare_amount']
                    : null,
                'from' => true,
            ];
        }

        $currencyCode = current_currency();

        if (! $this->relationLoaded('variants')) {
            $this->load([
                'variants.prices' => fn ($q) => $q->whereRelation('currency', 'code', $currencyCode),
            ]);
        }

        /** @var Price|null $price */
        $price = $this->variants
            ->flatMap(function ($variant) {
                if (! $variant->relationLoaded('prices')) {
                    return [];
                }

                return $variant->prices;
            })
            ->filter(fn (Price $price): bool => $price->amount !== null)
            ->sortBy('amount')
            ->first();

        if ($price === null) {
            return null;
        }

        return [
            'amount' => (int) $price->amount,
            'compare_amount' => $price->compare_amount !== null
                ? (int) $price->compare_amount
                : null,
            'from' => true,
        ];
    }
}
