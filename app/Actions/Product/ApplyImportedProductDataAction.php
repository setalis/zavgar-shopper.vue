<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Support\FormatsVariantAttributes;
use App\Support\ResolvesCatalogAttributes;
use Illuminate\Validation\ValidationException;
use Shopper\Core\Models\Attribute;
use Shopper\Core\Models\Contracts\Inventory;
use Shopper\Core\Models\Currency;
use Shopper\Core\Models\Price;

final class ApplyImportedProductDataAction
{
    public function __construct(
        private FormatsVariantAttributes $formatsVariantAttributes,
        private ResolvesCatalogAttributes $resolvesCatalogAttributes,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     * @param  list<int>  $skipAttributeIds
     */
    public function handle(Product|ProductVariant $record, array $data, array $skipAttributeIds = []): Product|ProductVariant
    {
        if ($record instanceof Product) {
            $this->syncCategories($record, $data);
            $this->syncProductAttributes($record, $data, $skipAttributeIds);
        }

        if ($record instanceof Product && $record->isVariant()) {
            return $record->refresh();
        }

        $this->syncPrice($record, $data);
        $this->syncQuantity($record, $data);

        return $record->refresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function syncCategories(Product $product, array $data): void
    {
        if (! array_key_exists('categories', $data) || $data['categories'] === null || $data['categories'] === '') {
            return;
        }

        if (! is_string($data['categories'])) {
            return;
        }

        $ids = [];

        foreach (explode('|', $data['categories']) as $label) {
            $label = trim($label);

            if ($label === '') {
                continue;
            }

            $ids[] = $this->resolveCategory($label)->id;
        }

        $product->categories()->sync(array_values(array_unique($ids)));
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  list<int>  $skipAttributeIds
     */
    public function syncProductAttributes(Product $product, array $data, array $skipAttributeIds = []): void
    {
        $pairs = $this->formatsVariantAttributes->parse(
            is_string($data['attributes'] ?? null) ? $data['attributes'] : null,
        );

        if ($pairs === []) {
            return;
        }

        $valuesByAttributeId = [];

        foreach ($pairs as $pair) {
            $attribute = $this->resolvesCatalogAttributes->attribute($pair['name']);

            if (in_array($attribute->id, $skipAttributeIds, true)) {
                continue;
            }

            $valuesByAttributeId[$attribute->id]['attribute'] = $attribute;
            $valuesByAttributeId[$attribute->id]['values'][] = $pair['value'];
        }

        foreach ($valuesByAttributeId as $payload) {
            $this->replaceProductAttribute($product, $payload['attribute'], $payload['values']);
        }
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

    /**
     * @param  list<string>  $values
     */
    private function replaceProductAttribute(Product $product, Attribute $attribute, array $values): void
    {
        $product->options()->detach($attribute->id);

        foreach (array_unique($values) as $value) {
            if ($attribute->hasTextValue()) {
                $product->options()->attach($attribute->id, [
                    'attribute_custom_value' => $value,
                ]);

                continue;
            }

            $attributeValue = $this->resolvesCatalogAttributes->value($attribute, $value);

            $product->options()->attach($attribute->id, [
                'attribute_value_id' => $attributeValue->id,
            ]);
        }
    }

    private function resolveCategory(string $label): Category
    {
        $name = str_contains($label, '/')
            ? trim((string) str($label)->afterLast('/'))
            : $label;

        $category = Category::query()
            ->where(function ($query) use ($label, $name): void {
                $query->where('name', $label)
                    ->orWhere('name', $name)
                    ->orWhere('slug', str()->slug($label))
                    ->orWhere('slug', str()->slug($name));
            })
            ->first();

        if (! $category instanceof Category) {
            throw ValidationException::withMessages([
                'categories' => __('backend.product_imports.unknown_category', ['name' => $label]),
            ]);
        }

        return $category;
    }
}
