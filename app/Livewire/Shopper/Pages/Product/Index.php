<?php

declare(strict_types=1);

namespace App\Livewire\Shopper\Pages\Product;

use App\Filament\Actions\SpreadsheetImportAction;
use App\Filament\Exports\Jobs\PrepareProductCsvExport;
use App\Filament\Exports\ProductExporter;
use App\Filament\Imports\ProductImporter;
use App\Models\PendingProductImport;
use Filament\Actions\Action;
use Filament\Actions\ExportAction;
use Filament\Tables\Table;
use Shopper\Livewire\Pages\Product\Index as BaseIndex;

final class Index extends BaseIndex
{
    public function table(Table $table): Table
    {
        return parent::table($table)
            ->headerActions([
                Action::make('pendingImports')
                    ->label(__('backend.product_imports.menu'))
                    ->icon('phosphor-clock')
                    ->color('gray')
                    ->url(fn (): string => route('shopper.products.pending-imports'))
                    ->badge(function (): ?string {
                        $count = PendingProductImport::query()->pending()->count();

                        return $count > 0 ? (string) $count : null;
                    })
                    ->badgeColor('warning')
                    ->authorize('browse_products')
                    ->visible(shopper()->auth()->user()->can('browse_products')),
                ExportAction::make()
                    ->exporter(ProductExporter::class)
                    ->job(PrepareProductCsvExport::class)
                    ->authorize('browse_products')
                    ->visible(shopper()->auth()->user()->can('browse_products')),
                SpreadsheetImportAction::make()
                    ->importer(ProductImporter::class)
                    ->authorize('edit_products')
                    ->visible(shopper()->auth()->user()->can('edit_products')),
            ]);
    }
}
