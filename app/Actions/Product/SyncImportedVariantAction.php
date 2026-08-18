<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Support\FormatsVariantAttributes;
use Illuminate\Validation\ValidationException;
use Shopper\Actions\Store\Product\CreateNewVariant;
use Shopper\Core\Models\Attribute;
use Shopper\Core\Models\AttributeValue;

final class SyncImportedVariantAction
{
    public function __construct(
        private CreateNewVariant $createNewVariant,
        private ApplyImportedProductDataAction $applyImportedProductData,
        private FormatsVariantAttributes $formatsVariantAttributes,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(Product $parent, array $data, ?ProductVariant $variant = null): ProductVariant
    {
        if (! $parent->isVariant()) {
            throw ValidationException::withMessages([
                'parent_sku' => __('backend.product_imports.parent_not_variant'),
            ]);
        }

        $valueIds = $this->syncAttributeValues($parent, $data);

        if (! $variant instanceof ProductVariant) {
            $variant = ($this->createNewVariant)([
                'product_id' => $parent->id,
                'name' => filled($data['name'] ?? null) ? $data['name'] : $parent->name,
                'sku' => $data['sku'],
                'barcode' => $data['barcode'] ?? null,
                'values' => $valueIds,
            ]);
        } else {
            $this->updateVariant($variant, $data);

            if ($valueIds !== []) {
                $variant->values()->sync($valueIds);
            }
        }

        $this->applyImportedProductData->handle($variant, $data);

        return $variant->refresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function updateVariant(ProductVariant $variant, array $data): void
    {
        $updates = [];

        if (filled($data['name'] ?? null)) {
            $updates['name'] = $data['name'];
        }

        if (array_key_exists('barcode', $data) && filled($data['barcode'])) {
            $updates['barcode'] = $data['barcode'];
        }

        if ($updates !== []) {
            $variant->update($updates);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @return list<int>
     */
    private function syncAttributeValues(Product $parent, array $data): array
    {
        $pairs = $this->formatsVariantAttributes->parse(
            is_string($data['attributes'] ?? null) ? $data['attributes'] : null,
        );

        if ($pairs === []) {
            return [];
        }

        $valueIds = [];

        foreach ($pairs as $pair) {
            $attribute = $this->resolveAttribute($pair['name']);
            $value = $this->resolveAttributeValue($attribute, $pair['value']);

            $this->attachOption($parent, $attribute, $value);
            $valueIds[] = $value->id;
        }

        return $valueIds;
    }

    private function resolveAttribute(string $name): Attribute
    {
        $attribute = Attribute::query()
            ->where(function ($query) use ($name): void {
                $query->where('name', $name)
                    ->orWhere('slug', str()->slug($name));
            })
            ->first();

        if (! $attribute instanceof Attribute) {
            throw ValidationException::withMessages([
                'attributes' => __('backend.product_imports.unknown_attribute', ['name' => $name]),
            ]);
        }

        return $attribute;
    }

    private function resolveAttributeValue(Attribute $attribute, string $value): AttributeValue
    {
        $attributeValue = AttributeValue::query()
            ->where('attribute_id', $attribute->id)
            ->where(function ($query) use ($value): void {
                $query->where('value', $value)
                    ->orWhere('key', str()->slug($value));
            })
            ->first();

        if ($attributeValue instanceof AttributeValue) {
            return $attributeValue;
        }

        return AttributeValue::create([
            'attribute_id' => $attribute->id,
            'key' => str()->slug($value),
            'value' => $value,
            'position' => (int) $attribute->values()->max('position') + 1,
        ]);
    }

    private function attachOption(Product $parent, Attribute $attribute, AttributeValue $value): void
    {
        $alreadyAttached = $parent->options()
            ->whereKey($attribute->id)
            ->wherePivot('attribute_value_id', $value->id)
            ->exists();

        if ($alreadyAttached) {
            return;
        }

        $parent->options()->attach($attribute->id, [
            'attribute_value_id' => $value->id,
        ]);
    }
}
