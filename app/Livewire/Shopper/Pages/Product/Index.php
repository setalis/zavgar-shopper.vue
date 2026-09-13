<?php

declare(strict_types=1);

namespace App\Livewire\Shopper\Pages\Product;

use App\Actions\Product\DuplicateProductToDraftAction;
use App\Filament\Actions\SpreadsheetImportAction;
use App\Filament\Exports\Jobs\PrepareProductCsvExport;
use App\Filament\Exports\ProductExporter;
use App\Filament\Imports\ProductImporter;
use App\Models\Category;
use App\Models\PendingProductImport;
use App\Models\Product;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\ExportAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Mckenziearts\Icons\Untitledui\Enums\Untitledui;
use Shopper\Core\Events\Products\ProductDeleted;
use Shopper\Feature;
use Shopper\Livewire\Pages\Product\Index as BaseIndex;

final class Index extends BaseIndex
{
    public function table(Table $table): Table
    {
        $table = parent::table($table)
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['categories.parent']))
            ->pushFilters([
                SelectFilter::make('categories')
                    ->label(__('shopper::pages/categories.menu'))
                    ->relationship(
                        'categories',
                        'name',
                        fn (Builder $query): Builder => $query->with('parent')->orderBy('name'),
                    )
                    ->getOptionLabelFromRecordUsing(
                        fn (Category $record): string => $record->getLabelOptionName(),
                    )
                    ->searchable()
                    ->preload()
                    ->multiple()
                    ->visible(Feature::enabled('category')),
            ]);

        return $table
            ->columns($this->columnsWithCategory($table))
            ->recordActions([
                ActionGroup::make([
                    Action::make('edit')
                        ->label(__('shopper::forms.actions.edit'))
                        ->icon(Untitledui::Edit03)
                        ->color('primary')
                        ->action(fn (Product $record) => $this->redirectRoute(
                            name: 'shopper.products.edit',
                            parameters: ['product' => $record],
                            navigate: true
                        ))
                        ->authorize('edit_products')
                        ->visible(shopper()->auth()->user()->can('edit_products')),
                    Action::make('copyToDraft')
                        ->label(__('backend.products.copy_to_draft'))
                        ->icon(Untitledui::Copy03)
                        ->color('gray')
                        ->authorize('add_products')
                        ->visible(shopper()->auth()->user()->can('add_products'))
                        ->action(function (Product $record): void {
                            $draft = app(DuplicateProductToDraftAction::class)->handle($record);

                            Notification::make()
                                ->title(__('shopper::pages/products.notifications.replicated'))
                                ->success()
                                ->send();

                            $this->redirectRoute(
                                name: 'shopper.products.edit',
                                parameters: ['product' => $draft],
                                navigate: true
                            );
                        }),
                    Action::make(__('shopper::forms.actions.delete'))
                        ->icon(Untitledui::Trash03)
                        ->modalIcon(Untitledui::Trash03)
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (Product $record): void {
                            event(new ProductDeleted($record));

                            $record->delete();
                        })
                        ->authorize('delete_products')
                        ->visible(shopper()->auth()->user()->can('delete_products')),
                ])
                    ->tooltip('Actions'),
            ])
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

    /**
     * @return list<Column>
     */
    private function columnsWithCategory(Table $table): array
    {
        $categoryColumn = TextColumn::make('categories.name')
            ->label(__('shopper::pages/categories.menu'))
            ->state(fn (Product $record): array => $record->categories
                ->map(fn (Category $category): string => $category->getLabelOptionName())
                ->filter()
                ->unique()
                ->values()
                ->all())
            ->listWithLineBreaks()
            ->toggleable()
            ->hidden(! Feature::enabled('category'));

        $columns = [];
        $inserted = false;

        foreach ($table->getColumns() as $name => $column) {
            $columns[] = $column;

            if ($name === 'brand.name') {
                $columns[] = $categoryColumn;
                $inserted = true;
            }
        }

        if (! $inserted) {
            $columns[] = $categoryColumn;
        }

        return $columns;
    }
}
