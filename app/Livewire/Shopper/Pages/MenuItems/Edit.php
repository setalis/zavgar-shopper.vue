<?php

declare(strict_types=1);

namespace App\Livewire\Shopper\Pages\MenuItems;

use App\Enums\MenuItemTargetType;
use App\Models\Collection;
use App\Models\MenuItem;
use App\Models\Product;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;
use Shopper\Components\Section;
use Shopper\Livewire\Pages\AbstractPageComponent;
use Shopper\Traits\HandlesAuthorizationExceptions;
use Throwable;

/**
 * @property-read Schema $form
 */
final class Edit extends AbstractPageComponent implements HasActions, HasSchemas
{
    use HandlesAuthorizationExceptions;
    use InteractsWithActions;
    use InteractsWithSchemas;

    public MenuItem $menuItem;

    public ?MenuItem $parentItem = null;

    #[Url]
    public ?int $parent = null;

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public function mount(?MenuItem $menuItem = null): void
    {
        if ($menuItem instanceof MenuItem && $menuItem->exists) {
            $this->authorize('edit_menu_items');
            $this->menuItem = $menuItem;
            $this->parent = $menuItem->parent_id;
            $this->parentItem = $menuItem->parent;
            $this->form->fill($menuItem->attributesToArray());

            return;
        }

        $this->authorize('add_menu_items');
        $this->menuItem = new MenuItem;

        if ($this->parent !== null) {
            $this->parentItem = MenuItem::query()->findOrFail($this->parent);

            if (! $this->parentItem->canHaveChildren()) {
                abort(404);
            }
        }

        $this->form->fill([
            'target_type' => MenuItemTargetType::Url->value,
            'is_enabled' => true,
        ]);
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

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()
                    ->schema([
                        Section::make(__('backend.menu.single'))
                            ->compact()
                            ->schema([
                                TextInput::make('title')
                                    ->label(__('backend.menu.title'))
                                    ->required()
                                    ->maxLength(255),
                                Select::make('target_type')
                                    ->label(__('backend.menu.target'))
                                    ->options(MenuItemTargetType::options())
                                    ->native(false)
                                    ->live()
                                    ->required(),
                                TextInput::make('url')
                                    ->label(__('backend.menu.url'))
                                    ->maxLength(2048)
                                    ->required(fn (Get $get): bool => $get('target_type') === MenuItemTargetType::Url->value)
                                    ->visible(fn (Get $get): bool => $get('target_type') === MenuItemTargetType::Url->value),
                                Select::make('brand_id')
                                    ->label(__('backend.menu.brand'))
                                    ->relationship(
                                        'brand',
                                        'name',
                                        fn (Builder $query): Builder => $query->where('is_enabled', true),
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->native(false)
                                    ->required(fn (Get $get): bool => $get('target_type') === MenuItemTargetType::Brand->value)
                                    ->visible(fn (Get $get): bool => $get('target_type') === MenuItemTargetType::Brand->value),
                                Select::make('category_id')
                                    ->label(__('backend.menu.category'))
                                    ->relationship(
                                        'category',
                                        'name',
                                        fn (Builder $query): Builder => $query->where('is_enabled', true),
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->native(false)
                                    ->required(fn (Get $get): bool => $get('target_type') === MenuItemTargetType::Category->value)
                                    ->visible(fn (Get $get): bool => $get('target_type') === MenuItemTargetType::Category->value),
                                Select::make('collection_id')
                                    ->label(__('backend.menu.collection'))
                                    ->relationship('collection', 'name')
                                    ->getOptionLabelFromRecordUsing(
                                        fn (Collection $record): string => $record->name,
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->native(false)
                                    ->required(fn (Get $get): bool => $get('target_type') === MenuItemTargetType::Collection->value)
                                    ->visible(fn (Get $get): bool => $get('target_type') === MenuItemTargetType::Collection->value),
                                Select::make('product_id')
                                    ->label(__('backend.menu.product'))
                                    ->getSearchResultsUsing(
                                        fn (string $search): array => Product::query()
                                            ->scopes('publish')
                                            ->where('name', 'like', "%{$search}%")
                                            ->limit(10)
                                            ->pluck('name', 'id')
                                            ->all(),
                                    )
                                    ->getOptionLabelUsing(
                                        fn (mixed $value): ?string => filled($value)
                                            ? Product::query()->where('id', $value)->value('name')
                                            : null,
                                    )
                                    ->searchable()
                                    ->native(false)
                                    ->required(fn (Get $get): bool => $get('target_type') === MenuItemTargetType::Product->value)
                                    ->visible(fn (Get $get): bool => $get('target_type') === MenuItemTargetType::Product->value),
                            ]),
                    ])
                    ->columnSpan(['lg' => 2]),
                Group::make()
                    ->schema([
                        Section::make(__('shopper::forms.label.visibility'))
                            ->compact()
                            ->schema([
                                Toggle::make('is_enabled')
                                    ->label(__('backend.menu.visibility'))
                                    ->default(true),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3)
            ->statePath('data')
            ->model($this->menuItem);
    }

    public function store(): void
    {
        $creating = ! $this->menuItem->exists;

        $this->authorize($creating ? 'add_menu_items' : 'edit_menu_items');

        $data = $this->payload($this->form->getState());

        if ($creating) {
            $data['parent_id'] = $this->parentItem?->id;
            $data['position'] = (int) MenuItem::query()
                ->when(
                    $this->parentItem !== null,
                    fn (Builder $query): Builder => $query->where('parent_id', $this->parentItem->id),
                    fn (Builder $query): Builder => $query->whereNull('parent_id'),
                )
                ->max('position') + 1;

            $this->menuItem = MenuItem::create($data);

            Notification::make()
                ->title(__('backend.menu.created'))
                ->success()
                ->send();

            $this->redirect(route('shopper.menu.edit', $this->menuItem), navigate: true);

            return;
        }

        $this->menuItem->update($data);

        Notification::make()
            ->title(__('backend.menu.updated'))
            ->success()
            ->send();
    }

    public function indexUrl(): string
    {
        return route('shopper.menu.index', array_filter([
            'parent' => $this->parentItem?->id ?? $this->parent,
        ]));
    }

    public function render(): View
    {
        return view('livewire.shopper.pages.menu-items.edit')
            ->title(
                $this->menuItem->exists
                    ? __('backend.menu.edit')
                    : ($this->parentItem !== null ? __('backend.menu.create_child') : __('backend.menu.create')),
            );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function payload(array $data): array
    {
        $targetType = $data['target_type'] instanceof MenuItemTargetType
            ? $data['target_type']
            : MenuItemTargetType::from((string) $data['target_type']);

        $data['url'] = $targetType === MenuItemTargetType::Url ? ($data['url'] ?? null) : null;
        $data['brand_id'] = $targetType === MenuItemTargetType::Brand ? ($data['brand_id'] ?? null) : null;
        $data['category_id'] = $targetType === MenuItemTargetType::Category ? ($data['category_id'] ?? null) : null;
        $data['collection_id'] = $targetType === MenuItemTargetType::Collection ? ($data['collection_id'] ?? null) : null;
        $data['product_id'] = $targetType === MenuItemTargetType::Product ? ($data['product_id'] ?? null) : null;

        return $data;
    }
}
