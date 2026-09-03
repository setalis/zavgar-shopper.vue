<?php

declare(strict_types=1);

namespace App\Livewire\Shopper\Pages\HomepageBanners;

use App\Models\HomepageBanner;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
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

    public function mount(): void
    {
        $this->authorize('browse_homepage_banners');
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
        $canEdit = $user?->can('edit_homepage_banners') ?? false;
        $canDelete = $user?->can('delete_homepage_banners') ?? false;

        return $table
            ->query(HomepageBanner::query()->orderBy('position'))
            ->columns([
                SpatieMediaLibraryImageColumn::make('preview')
                    ->collection(HomepageBanner::MEDIA_BACKGROUND)
                    ->circular()
                    ->defaultImageUrl(shopper_fallback_url())
                    ->grow(false),
                TextColumn::make('title')
                    ->label(__('backend.banners.title'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('size')
                    ->label(__('backend.banners.size'))
                    ->badge(),
                TextColumn::make('cta_type')
                    ->label(__('backend.banners.cta'))
                    ->badge(),
                ToggleColumn::make('is_enabled')
                    ->label(__('shopper::forms.label.visibility'))
                    ->disabled(fn (): bool => ! $canEdit),
            ])
            ->reorderable('position')
            ->authorizeReorder($canEdit)
            ->defaultSort('position')
            ->recordActions([
                Action::make('edit')
                    ->label(__('shopper::forms.actions.edit'))
                    ->icon(Untitledui::Edit03)
                    ->iconButton()
                    ->url(
                        fn (HomepageBanner $record): string => route(
                            'shopper.banners.edit',
                            ['banner' => $record],
                        ),
                    )
                    ->extraAttributes(['wire:navigate' => true])
                    ->authorize('edit_homepage_banners')
                    ->visible($canEdit),
                Action::make('delete')
                    ->label(__('shopper::forms.actions.delete'))
                    ->icon(Untitledui::Trash03)
                    ->iconButton()
                    ->modalIcon(Untitledui::Trash03)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (HomepageBanner $record): void {
                        $record->delete();

                        Notification::make()
                            ->title(__('backend.banners.deleted'))
                            ->success()
                            ->send();
                    })
                    ->authorize('delete_homepage_banners')
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
                            ->title(__('backend.banners.deleted'))
                            ->success()
                            ->send();
                    })
                    ->authorize('delete_homepage_banners')
                    ->visible($canDelete)
                    ->deselectRecordsAfterCompletion(),
            ])
            ->emptyStateHeading(__('backend.banners.empty'));
    }

    public function render(): View
    {
        return view('livewire.shopper.pages.homepage-banners.index')
            ->title(__('backend.banners.menu'));
    }
}
