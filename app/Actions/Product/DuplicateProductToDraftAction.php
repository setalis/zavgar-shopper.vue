<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\Models\CatalogTranslation;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Shopper\Actions\Store\InitialQuantityInventory;
use Shopper\Core\Models\AttributeProduct;
use Shopper\Core\Models\Price;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

final class DuplicateProductToDraftAction
{
    public function __construct(
        private InitialQuantityInventory $initialQuantityInventory,
    ) {}

    public function handle(Product $product): Product
    {
        $product->load([
            'categories',
            'collections',
            'channels',
            'tags',
            'relatedProducts',
            'attributeProducts',
            'prices',
            'media',
            'translations',
            'variants.prices',
            'variants.media',
            'variants.values',
            'variants.translations',
        ]);

        return DB::transaction(function () use ($product): Product {
            $draft = $this->replicateRecord($product, ['slug', 'sku', 'barcode']);
            $draft->is_visible = false;
            $draft->published_at = now();
            $draft->save();

            $this->syncRelations($product, $draft);
            $this->copyAttributes($product, $draft);
            $this->copyPrices($product, $draft);
            $this->copyMedia($product, $draft);
            $this->copyTranslations($product, $draft);
            $this->copyStock($product, $draft);
            $this->copyVariants($product, $draft);

            return $draft->refresh();
        });
    }

    private function syncRelations(Product $product, Product $draft): void
    {
        $draft->categories()->sync($product->categories->modelKeys());
        $draft->collections()->sync($product->collections->modelKeys());
        $draft->channels()->sync($product->channels->modelKeys());
        $draft->tags()->sync($product->tags->modelKeys());
        $draft->relatedProducts()->sync($product->relatedProducts->modelKeys());
    }

    private function copyAttributes(Product $product, Product $draft): void
    {
        foreach ($product->attributeProducts as $attributeProduct) {
            if (! $attributeProduct instanceof AttributeProduct) {
                continue;
            }

            $draft->options()->attach($attributeProduct->attribute_id, [
                'attribute_value_id' => $attributeProduct->attribute_value_id,
                'attribute_custom_value' => $attributeProduct->attribute_custom_value,
            ]);
        }
    }

    private function copyPrices(Product|ProductVariant $source, Product|ProductVariant $target): void
    {
        foreach ($source->prices as $price) {
            if (! $price instanceof Price) {
                continue;
            }

            $target->prices()->create([
                'amount' => $price->amount,
                'compare_amount' => $price->compare_amount,
                'cost_amount' => $price->cost_amount,
                'currency_id' => $price->currency_id,
            ]);
        }
    }

    private function copyMedia(Product|ProductVariant $source, Product|ProductVariant $target): void
    {
        foreach (array_keys($source->getMediaCollections()) as $collection) {
            foreach ($source->getMedia($collection) as $media) {
                if (! $media instanceof Media) {
                    continue;
                }

                $media->copy($target, $collection);
            }
        }
    }

    private function copyTranslations(Model $source, Model $target): void
    {
        if (! method_exists($source, 'translations')) {
            return;
        }

        foreach ($source->translations as $translation) {
            if (! $translation instanceof CatalogTranslation) {
                continue;
            }

            CatalogTranslation::syncFor($target, $translation->locale, $translation->only(CatalogTranslation::COLUMNS));
        }
    }

    private function copyStock(Product|ProductVariant $source, Product|ProductVariant $target): void
    {
        $quantity = $source->stock;

        if ($quantity > 0) {
            ($this->initialQuantityInventory)($quantity, $target);
        }
    }

    /**
     * @param  list<string>  $uniqueFields
     */
    private function replicateRecord(Product|ProductVariant $source, array $uniqueFields): Product|ProductVariant
    {
        $columns = $source->getConnection()->getSchemaBuilder()->getColumnListing($source->getTable());
        $except = [
            ...array_values(array_diff(array_keys($source->getAttributes()), $columns)),
            ...$uniqueFields,
        ];

        return $source->replicate(array_values(array_unique($except)));
    }

    private function copyVariants(Product $product, Product $draft): void
    {
        foreach ($product->variants as $variant) {
            if (! $variant instanceof ProductVariant) {
                continue;
            }

            $replica = $this->replicateRecord($variant, ['sku', 'barcode', 'ean', 'upc']);
            $replica->product_id = $draft->id;
            $replica->save();

            $replica->values()->sync($variant->values->modelKeys());
            $this->copyPrices($variant, $replica);
            $this->copyMedia($variant, $replica);
            $this->copyTranslations($variant, $replica);
            $this->copyStock($variant, $replica);
        }
    }
}
