<?php

declare(strict_types=1);

namespace App\Filament\Imports;

use App\Actions\Product\ApplyImportedProductDataAction;
use App\Actions\Product\SyncImportedVariantAction;
use App\Enums\PendingProductImportStatus;
use App\Models\PendingProductImport;
use App\Models\Product;
use App\Models\ProductVariant;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Shopper\Core\Enum\ProductType;
use Shopper\Core\Models\Brand;

final class ProductImporter extends Importer
{
    protected static ?string $model = Product::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('sku')
                ->label(__('shopper::layout.tables.sku'))
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255'])
                ->example('SKU-1001'),
            ImportColumn::make('parent_sku')
                ->label(__('backend.product_imports.parent_sku'))
                ->rules(['nullable', 'string', 'max:255'])
                ->ignoreBlankState()
                ->helperText(__('backend.product_imports.parent_sku_help'))
                ->example('SKU-PARENT')
                ->fillRecordUsing(fn (): mixed => null),
            ImportColumn::make('name')
                ->label(__('shopper::forms.label.name'))
                ->rules(['nullable', 'string', 'max:255'])
                ->ignoreBlankState()
                ->example('Cotton T-Shirt'),
            ImportColumn::make('slug')
                ->label(__('shopper::forms.label.slug'))
                ->rules(['nullable', 'string', 'max:255'])
                ->ignoreBlankState(),
            ImportColumn::make('summary')
                ->label(__('shopper::forms.label.summary'))
                ->rules(['nullable', 'string'])
                ->ignoreBlankState(),
            ImportColumn::make('description')
                ->label(__('shopper::forms.label.description'))
                ->rules(['nullable', 'string'])
                ->ignoreBlankState(),
            ImportColumn::make('barcode')
                ->label(__('shopper::forms.label.barcode'))
                ->rules(['nullable', 'string', 'max:255'])
                ->ignoreBlankState(),
            ImportColumn::make('type')
                ->label(__('shopper::forms.label.type'))
                ->rules(['nullable', Rule::enum(ProductType::class)])
                ->ignoreBlankState()
                ->example(ProductType::Standard->value),
            ImportColumn::make('is_visible')
                ->label(__('shopper::forms.label.visibility'))
                ->boolean()
                ->rules(['nullable', 'boolean'])
                ->ignoreBlankState(),
            ImportColumn::make('featured')
                ->label(__('shopper::forms.label.featured'))
                ->boolean()
                ->rules(['nullable', 'boolean'])
                ->ignoreBlankState(),
            ImportColumn::make('brand')
                ->label(__('shopper::forms.label.brand'))
                ->rules(['nullable', 'string', 'max:255'])
                ->ignoreBlankState()
                ->fillRecordUsing(function (Product $record, mixed $state): void {
                    if (! is_string($state) || blank($state)) {
                        return;
                    }

                    $brandId = Brand::query()->where('name', $state)->value('id');

                    if ($brandId) {
                        $record->brand_id = $brandId;
                    }
                }),
            ImportColumn::make('price')
                ->label(__('shopper::layout.tables.price'))
                ->numeric()
                ->integer()
                ->rules(['nullable', 'integer', 'min:0'])
                ->helperText(__('backend.product_imports.price_help'))
                ->fillRecordUsing(fn (): mixed => null),
            ImportColumn::make('quantity')
                ->label(__('shopper::forms.label.quantity'))
                ->numeric()
                ->integer()
                ->rules(['nullable', 'integer', 'min:0'])
                ->fillRecordUsing(fn (): mixed => null),
            ImportColumn::make('attributes')
                ->label(__('backend.product_imports.attributes'))
                ->rules(['nullable', 'string'])
                ->ignoreBlankState()
                ->helperText(__('backend.product_imports.attributes_help'))
                ->example('Color=Red | Size=M')
                ->fillRecordUsing(fn (): mixed => null),
            ImportColumn::make('published_at')
                ->label(__('shopper::forms.label.published_at'))
                ->rules(['nullable', 'date'])
                ->ignoreBlankState(),
        ];
    }

    public function resolveRecord(): ?Product
    {
        $sku = $this->data['sku'] ?? null;

        if (! is_string($sku) || blank($sku)) {
            throw ValidationException::withMessages([
                'sku' => __('validation.required', ['attribute' => 'sku']),
            ]);
        }

        $parentSku = $this->parentSku();

        if ($parentSku !== null) {
            $this->importVariant($sku, $parentSku);

            return null;
        }

        $existingVariant = ProductVariant::query()->where('sku', $sku)->first();

        if ($existingVariant instanceof ProductVariant) {
            $existingVariant->loadMissing('product');
            app(SyncImportedVariantAction::class)->handle($existingVariant->product, $this->data, $existingVariant);

            return null;
        }

        $existing = Product::query()->where('sku', $sku)->first();

        if ($existing instanceof Product) {
            return $existing;
        }

        $this->queuePending($sku);

        return null;
    }

    protected function afterSave(): void
    {
        $product = $this->record;

        if (! $product instanceof Product) {
            return;
        }

        app(ApplyImportedProductDataAction::class)->handle($product, $this->data);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = __('backend.product_imports.import_completed', [
            'count' => Number::format($import->successful_rows),
        ]);

        $pendingCount = PendingProductImport::query()
            ->pending()
            ->where('updated_at', '>=', $import->created_at)
            ->count();

        if ($pendingCount > 0) {
            $body .= ' '.__('backend.product_imports.import_queued', [
                'count' => Number::format($pendingCount),
            ]);
        }

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.__('backend.product_imports.import_failed', [
                'count' => Number::format($failedRowsCount),
            ]);
        }

        return $body;
    }

    private function parentSku(): ?string
    {
        $parentSku = $this->data['parent_sku'] ?? null;

        if (! is_string($parentSku) || blank($parentSku)) {
            return null;
        }

        return $parentSku;
    }

    private function importVariant(string $sku, string $parentSku): void
    {
        $parent = Product::query()->where('sku', $parentSku)->first();

        if (! $parent instanceof Product) {
            $this->queuePending($sku);

            return;
        }

        $variant = ProductVariant::query()->where('sku', $sku)->first();

        if ($variant instanceof ProductVariant && $variant->product_id !== $parent->id) {
            throw ValidationException::withMessages([
                'sku' => __('backend.product_imports.variant_sku_belongs_to_other_product'),
            ]);
        }

        app(SyncImportedVariantAction::class)->handle(
            $parent,
            $this->data,
            $variant instanceof ProductVariant ? $variant : null,
        );
    }

    private function queuePending(string $sku): void
    {
        $name = $this->data['name'] ?? null;

        if (! is_string($name) || blank($name)) {
            throw ValidationException::withMessages([
                'name' => __('validation.required', ['attribute' => 'name']),
            ]);
        }

        PendingProductImport::query()->updateOrCreate(
            ['sku' => $sku],
            [
                'name' => $name,
                'payload' => $this->data,
                'status' => PendingProductImportStatus::Pending,
                'product_id' => null,
                'approved_by' => null,
                'approved_at' => null,
            ],
        );
    }
}
