<?php

declare(strict_types=1);

namespace App\Livewire\Shopper\Pages\Product;

use App\Actions\Product\ApprovePendingProductImportAction;
use App\Enums\PendingProductImportStatus;
use App\Models\PendingProductImport;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;
use Mckenziearts\Icons\Untitledui\Enums\Untitledui;
use Shopper\Livewire\Pages\AbstractPageComponent;
use Shopper\Traits\HandlesAuthorizationExceptions;

final class PendingImports extends AbstractPageComponent implements HasActions, HasSchemas, HasTable
{
    use HandlesAuthorizationExceptions;
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    public function mount(): void
    {
        $this->authorize('browse_products');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                PendingProductImport::query()
                    ->pending()
                    ->latest()
            )
            ->columns([
                TextColumn::make('sku')
                    ->label(__('shopper::layout.tables.sku'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label(__('shopper::forms.label.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('payload.type')
                    ->label(__('shopper::forms.label.type'))
                    ->placeholder('-'),
                TextColumn::make('payload.price')
                    ->label(__('shopper::layout.tables.price'))
                    ->placeholder('-'),
                TextColumn::make('created_at')
                    ->label(__('shopper::forms.label.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label(__('backend.product_imports.approve'))
                    ->icon(Untitledui::Check)
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading(__('backend.product_imports.approve_heading'))
                    ->modalDescription(__('backend.product_imports.approve_description'))
                    ->action(function (PendingProductImport $record): void {
                        $this->approveRecord($record);
                    })
                    ->authorize('add_products')
                    ->visible(shopper()->auth()->user()->can('add_products')),
                Action::make('reject')
                    ->label(__('backend.product_imports.reject'))
                    ->icon(Untitledui::X)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (PendingProductImport $record): void {
                        $record->update([
                            'status' => PendingProductImportStatus::Rejected,
                        ]);

                        Notification::make()
                            ->title(__('backend.product_imports.rejected'))
                            ->success()
                            ->send();
                    })
                    ->authorize('edit_products')
                    ->visible(shopper()->auth()->user()->can('edit_products')),
            ])
            ->groupedBulkActions([
                BulkAction::make('approve')
                    ->label(__('backend.product_imports.approve'))
                    ->icon(Untitledui::Check)
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Collection $records): void {
                        $records->each(fn (PendingProductImport $record) => $this->approveRecord($record, notify: false));

                        Notification::make()
                            ->title(__('backend.product_imports.approved_bulk'))
                            ->success()
                            ->send();
                    })
                    ->authorize('add_products')
                    ->visible(shopper()->auth()->user()->can('add_products'))
                    ->deselectRecordsAfterCompletion(),
            ])
            ->emptyStateHeading(__('backend.product_imports.empty'));
    }

    public function render(): View
    {
        return view('livewire.shopper.pages.product.pending-imports')
            ->title(__('backend.product_imports.menu'));
    }

    private function approveRecord(PendingProductImport $record, bool $notify = true): void
    {
        $user = shopper()->auth()->user();

        if (! $user instanceof User) {
            return;
        }

        try {
            $product = app(ApprovePendingProductImportAction::class)->handle($record, $user);
        } catch (ValidationException $exception) {
            Notification::make()
                ->title(collect($exception->errors())->flatten()->first() ?: $exception->getMessage())
                ->danger()
                ->send();

            return;
        }

        if ($notify) {
            Notification::make()
                ->title(__('backend.product_imports.approved', ['name' => $product->name]))
                ->success()
                ->send();
        }
    }
}
