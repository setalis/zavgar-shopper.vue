<?php

declare(strict_types=1);

namespace App\Filament\Exports;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Support\DetectsCsvDelimiter;
use App\Support\FormatsVariantAttributes;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Number;
use Shopper\Core\Enum\ProductType;

final class ProductExporter extends Exporter
{
    protected static ?string $model = Product::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('sku')
                ->label(__('shopper::layout.tables.sku')),
            ExportColumn::make('parent_sku')
                ->label(__('backend.product_imports.parent_sku'))
                ->state(fn (): string => ''),
            ExportColumn::make('name')
                ->label(__('shopper::forms.label.name')),
            ExportColumn::make('slug')
                ->label(__('shopper::forms.label.slug')),
            ExportColumn::make('summary')
                ->label(__('shopper::forms.label.summary')),
            ExportColumn::make('description')
                ->label(__('shopper::forms.label.description')),
            ExportColumn::make('barcode')
                ->label(__('shopper::forms.label.barcode')),
            ExportColumn::make('type')
                ->label(__('shopper::forms.label.type')),
            ExportColumn::make('is_visible')
                ->label(__('shopper::forms.label.visibility')),
            ExportColumn::make('featured')
                ->label(__('shopper::forms.label.featured')),
            ExportColumn::make('brand.name')
                ->label(__('shopper::forms.label.brand')),
            ExportColumn::make('categories')
                ->label(__('backend.product_imports.categories'))
                ->state(fn (Product $record): string => app(FormatsVariantAttributes::class)->forCategories($record)),
            ExportColumn::make('price')
                ->label(__('shopper::layout.tables.price'))
                ->state(fn (Product $record): ?int => $record->isVariant() ? null : $record->getPrice()?->amount),
            ExportColumn::make('stock')
                ->label(__('shopper::forms.label.quantity'))
                ->state(fn (Product $record): ?int => $record->isVariant() ? null : $record->stock),
            ExportColumn::make('attributes')
                ->label(__('backend.product_imports.attributes'))
                ->state(fn (Product $record): string => app(FormatsVariantAttributes::class)->forProduct($record)),
            ExportColumn::make('variant_attributes')
                ->label(__('backend.product_imports.variant_attributes'))
                ->state(fn (): string => ''),
            ExportColumn::make('published_at')
                ->label(__('shopper::forms.label.published_at')),
        ];
    }

    /**
     * @param  Builder<Product>  $query
     * @return Builder<Product>
     */
    public static function modifyQuery(Builder $query): Builder
    {
        return $query->with([
            'brand',
            'prices.currency',
            'categories.parent',
            'attributeProducts.attribute',
            'attributeProducts.value',
            'variants.values.attribute',
            'variants.prices.currency',
        ]);
    }

    /**
     * @return list<list<string>>
     */
    public function rowsFor(Product $record): array
    {
        $rows = [$this->stringifyRow(($this)($record))];

        if (! $record->isVariant() || $record->variants->isEmpty()) {
            return $rows;
        }

        ProductVariant::loadCurrentStock($record->variants);

        foreach ($record->variants as $variant) {
            $rows[] = $this->rowForVariant($record, $variant);
        }

        return $rows;
    }

    /**
     * @return list<string>
     */
    public function rowForVariant(Product $product, ProductVariant $variant): array
    {
        $values = [
            'sku' => $variant->sku ?? '',
            'parent_sku' => $product->sku ?? '',
            'name' => $variant->name,
            'slug' => $product->slug,
            'summary' => $product->summary ?? '',
            'description' => $product->description ?? '',
            'barcode' => $variant->barcode ?? '',
            'type' => ProductType::Variant->value,
            'is_visible' => $product->is_visible ? '1' : '0',
            'featured' => $product->featured ? '1' : '0',
            'brand.name' => $product->brand?->name ?? '',
            'categories' => app(FormatsVariantAttributes::class)->forCategories($product),
            'price' => $variant->getPrice()?->amount,
            'stock' => $variant->stock,
            'attributes' => app(FormatsVariantAttributes::class)->forProduct($product),
            'variant_attributes' => app(FormatsVariantAttributes::class)->forVariantDimensions($variant),
            'published_at' => $product->published_at?->toDateTimeString() ?? '',
        ];

        $data = [];

        foreach (array_keys($this->columnMap) as $column) {
            $data[] = $values[$column] ?? '';
        }

        return $this->stringifyRow($data);
    }

    /**
     * @param  list<mixed>  $row
     * @return list<string>
     */
    private function stringifyRow(array $row): array
    {
        return array_map(
            fn (mixed $value): string => $value === null ? '' : (string) $value,
            $row,
        );
    }

    public static function getCsvDelimiter(): string
    {
        return DetectsCsvDelimiter::EXCEL;
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = __('backend.product_imports.export_completed', [
            'count' => Number::format($export->successful_rows),
        ]);

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.__('backend.product_imports.export_failed', [
                'count' => Number::format($failedRowsCount),
            ]);
        }

        return $body;
    }
}
