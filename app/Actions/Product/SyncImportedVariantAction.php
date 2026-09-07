<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Support\FormatsVariantAttributes;
use App\Support\ResolvesCatalogAttributes;
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
        private ResolvesCatalogAttributes $resolvesCatalogAttributes,
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

        $valueIds = $this->syncAttributeValues($parent, $data, $variant);
        $skipAttributeIds = $this->attributeIdsForValues($valueIds);

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
        $this->applyImportedProductData->handle($parent, $data, $skipAttributeIds);

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
    private function syncAttributeValues(Product $parent, array $data, ?ProductVariant $variant): array
    {
        $pairs = $this->formatsVariantAttributes->parse(
            is_string($data['attributes'] ?? null) ? $data['attributes'] : null,
        );

        if ($pairs === []) {
            return [];
        }

        $dimensionIds = $this->variantDimensionIds($parent, $variant);
        $valueIds = [];

        foreach ($pairs as $pair) {
            $attribute = $this->resolvesCatalogAttributes->attribute($pair['name']);

            if ($attribute->hasTextValue()) {
                continue;
            }

            if ($dimensionIds !== [] && ! in_array($attribute->id, $dimensionIds, true)) {
                continue;
            }

            $value = $this->resolvesCatalogAttributes->value($attribute, $pair['value']);
            $this->attachOption($parent, $attribute, $value);
            $valueIds[] = $value->id;
        }

        return array_values(array_unique($valueIds));
    }

    /**
     * @return list<int>
     */
    private function variantDimensionIds(Product $parent, ?ProductVariant $variant): array
    {
        $parent->loadMissing('variants.values');

        $ids = $parent->variants
            ->flatMap(fn (ProductVariant $existing): array => $existing->values->pluck('attribute_id')->all())
            ->all();

        if ($variant instanceof ProductVariant) {
            $variant->loadMissing('values');
            $ids = [...$ids, ...$variant->values->pluck('attribute_id')->all()];
        }

        return array_values(array_unique(array_map(intval(...), $ids)));
    }

    /**
     * @param  list<int>  $valueIds
     * @return list<int>
     */
    private function attributeIdsForValues(array $valueIds): array
    {
        if ($valueIds === []) {
            return [];
        }

        return AttributeValue::query()
            ->whereIn('id', $valueIds)
            ->pluck('attribute_id')
            ->map(intval(...))
            ->unique()
            ->values()
            ->all();
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
