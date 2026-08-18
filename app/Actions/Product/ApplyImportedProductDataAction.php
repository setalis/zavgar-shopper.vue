<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\Models\Product;
use App\Models\ProductVariant;
use Shopper\Core\Models\Contracts\Inventory;
use Shopper\Core\Models\Currency;
use Shopper\Core\Models\Price;

final class ApplyImportedProductDataAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(Product|ProductVariant $record, array $data): Product|ProductVariant
    {
        if ($record instanceof Product && $record->isVariant()) {
            return $record;
        }

        $this->syncPrice($record, $data);
        $this->syncQuantity($record, $data);

        return $record->refresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function syncPrice(Product|ProductVariant $record, array $data): void
    {
        if (! array_key_exists('price', $data) || $data['price'] === null || $data['price'] === '') {
            return;
        }

        $currency = Currency::query()
            ->where('code', shopper_currency())
            ->first();

        if (! $currency instanceof Currency) {
            return;
        }

        Price::query()->updateOrCreate(
            [
                'priceable_id' => $record->id,
                'priceable_type' => $record->getMorphClass(),
                'currency_id' => $currency->id,
            ],
            [
                'amount' => (int) $data['price'],
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function syncQuantity(Product|ProductVariant $record, array $data): void
    {
        if (! array_key_exists('quantity', $data) || $data['quantity'] === null || $data['quantity'] === '') {
            return;
        }

        $inventory = resolve(Inventory::class)::query()->scopes('default')->first();

        if (! $inventory instanceof Inventory) {
            return;
        }

        $record->setStock(
            newQuantity: (int) $data['quantity'],
            inventoryId: $inventory->id,
        );
    }
}
