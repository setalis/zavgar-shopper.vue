<?php

declare(strict_types=1);

namespace App\Livewire\Shopper\Pages\MenuItems;

use App\Actions\FlushStorefrontMenuCache;
use App\Models\MenuItem;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Url;
use Mckenziearts\Icons\Untitledui\Enums\Untitledui;
use Shopper\Livewire\Pages\AbstractPageComponent;
use Shopper\Traits\HandlesAuthorizationExceptions;
use Throwable;

final class Index extends AbstractPageComponent implements HasActions, HasSchemas, HasTable
{
    use HandlesAuthorizationExceptions;
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    #[Url]
    public ?int $parent = null;

    public ?MenuItem $parentItem = null;

    public function mount(): void
    {
        $this->authorize('browse_menu_items');

        if ($this->parent !== null) {
            $this->parentItem = MenuItem::query()->findOrFail($this->parent);
        }
    }

    public function exception(Throwable $e, callable $stopPropagation): void
    {
        if (! $e instanceof AuthorizationException) {
            return;
        }

        Notification::make()
            ->title(__('shopper::notifications.unauthorized.title'))
            ->body($e->getMessage() ?: __('shopper::notifications.unauthorized.body'))
            ->warning()
            ->send();

        $stopPropagation();
    }

    public function table(Table $table): Table
    {
        $user = shopper()->auth()->user();
        $canAdd = $user?->can('add_menu_items') ?? false;
        $canEdit = $user?->can('edit_menu_items') ?? false;
        $canDelete = $user?->can('delete_menu_items') ?? false;

        return $table
            ->query(
                MenuItem::query()
                    ->withCount('children')
                    ->when(
                        $this->parentItem !== null,
                        fn (Builder $query): Builder => $query->where('parent_id', $this->parentItem->id),
                        fn (Builder $query): Builder => $query->whereNull('parent_id'),
                    )
                    ->orderBy('position'),
            )
            ->columns([
                TextColumn::make('title')
                    ->label(__('backend.menu.title'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('target_type')
                    ->label(__('backend.menu.target'))
                    ->badge(),
                TextColumn::make('children_count')
                    ->label(__('backend.menu.children'))
                    ->numeric(),
                ToggleColumn::make('is_enabled')
                    ->label(__('shopper::forms.label.visibility'))
                    ->disabled(fn (): bool => ! $canEdit),
            ])
            ->reorderable('position')
            ->authorizeReorder($canEdit)
            ->defaultSort('position')
            ->afterReordering(function (): void {
                FlushStorefrontMenuCache::flush();
            })
            ->recordActions([
                Action::make('children')
                    ->label(__('backend.menu.children'))
                    ->icon(Untitledui::List)
                    ->iconButton()
                    ->url(fn (MenuItem $record): string => route('shopper.menu.index', ['parent' => $record->id]))
                    ->extraAttributes(['wire:navigate' => true])
                    ->visible(fn (MenuItem $record): bool => $record->canHaveChildren() || $record->children_count > 0),
                Action::make('addChild')
                    ->label(__('backend.menu.add_child'))
                    ->icon(Untitledui::FolderPlus)
                    ->iconButton()
                    ->url(fn (MenuItem $record): string => route('shopper.menu.create', ['parent' => $record->id]))
                    ->extraAttributes(['wire:navigate' => true])
                    ->authorize('add_menu_items')
                    ->visible(fn (MenuItem $record): bool => $canAdd && $record->canHaveChildren()),
                Action::make('edit')
                    ->label(__('shopper::forms.actions.edit'))
                    ->icon(Untitledui::Edit03)
                    ->iconButton()
                    ->url(fn (MenuItem $record): string => route('shopper.menu.edit', $record))
                    ->extraAttributes(['wire:navigate' => true])
                    ->authorize('edit_menu_items')
                    ->visible($canEdit),
                Action::make('delete')
                    ->label(__('shopper::forms.actions.delete'))
                    ->icon(Untitledui::Trash03)
                    ->iconButton()
                    ->modalIcon(Untitledui::Trash03)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (MenuItem $record): void {
                        $record->delete();

                        Notification::make()
                            ->title(__('backend.menu.deleted'))
                            ->success()
                            ->send();
                    })
                    ->authorize('delete_menu_items')
                    ->visible($canDelete),
            ])
            ->groupedBulkActions([
                DeleteBulkAction::make()
                    ->label(__('shopper::forms.actions.delete'))
                    ->icon(Untitledui::Trash03)
                    ->requiresConfirmation()
                    ->action(function (Collection $records): void {
                        $records->each->delete();

                        Notification::make()
                            ->title(__('backend.menu.deleted'))
                            ->success()
                            ->send();
                    })
                    ->authorize('delete_menu_items')
                    ->visible($canDelete)
                    ->deselectRecordsAfterCompletion(),
            ])
            ->emptyStateHeading(
                $this->parentItem !== null
                    ? __('backend.menu.empty_children')
                    : __('backend.menu.empty'),
            );
    }

    public function createUrl(): string
    {
        return route('shopper.menu.create', array_filter([
            'parent' => $this->parent,
        ]));
    }

    public function parentIndexUrl(): ?string
    {
        if ($this->parentItem === null) {
            return null;
        }

        return route('shopper.menu.index', array_filter([
            'parent' => $this->parentItem->parent_id,
        ]));
    }

    public function canAddAtCurrentLevel(): bool
    {
        $user = shopper()->auth()->user();

        if ($user === null || ! $user->can('add_menu_items')) {
            return false;
        }

        return $this->parentItem === null || $this->parentItem->canHaveChildren();
    }

    public function render(): View
    {
        return view('livewire.shopper.pages.menu-items.index')
            ->title(
                $this->parentItem?->title ?? __('backend.menu.menu'),
            );
    }
}
