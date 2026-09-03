<?php

declare(strict_types=1);

namespace App\Livewire\Shopper\Pages\HomepageBanners;

use App\Enums\HomepageBannerBackgroundType;
use App\Enums\HomepageBannerCtaType;
use App\Enums\HomepageBannerSize;
use App\Models\Collection;
use App\Models\HomepageBanner;
use App\Models\Product;
use App\Support\TailwindTint;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
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

    public HomepageBanner $banner;

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public function mount(?HomepageBanner $banner = null): void
    {
        if ($banner instanceof HomepageBanner && $banner->exists) {
            $this->authorize('edit_homepage_banners');
            $this->banner = $banner;
            $this->form->fill($banner->attributesToArray());

            return;
        }

        $this->authorize('add_homepage_banners');
        $this->banner = new HomepageBanner;
        $this->form->fill([
            'size' => HomepageBannerSize::Medium->value,
            'background_type' => HomepageBannerBackgroundType::Gradient->value,
            'gradient' => null,
            'overlay_gradient' => null,
            'cta_type' => HomepageBannerCtaType::Url->value,
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
                        Section::make(__('backend.banners.single'))
                            ->compact()
                            ->schema([
                                TextInput::make('eyebrow')
                                    ->label(__('backend.banners.eyebrow'))
                                    ->maxLength(255),
                                TextInput::make('title')
                                    ->label(__('backend.banners.title'))
                                    ->required()
                                    ->maxLength(255),
                                Textarea::make('description')
                                    ->label(__('backend.banners.description'))
                                    ->rows(3)
                                    ->maxLength(2000),
                                TextInput::make('button_text')
                                    ->label(__('backend.banners.button_text'))
                                    ->maxLength(255),
                                Select::make('size')
                                    ->label(__('backend.banners.size'))
                                    ->options(HomepageBannerSize::options())
                                    ->native(false)
                                    ->required(),
                            ]),
                        Section::make(__('backend.banners.cta'))
                            ->compact()
                            ->schema([
                                Select::make('cta_type')
                                    ->label(__('backend.banners.cta'))
                                    ->options(HomepageBannerCtaType::options())
                                    ->native(false)
                                    ->live()
                                    ->required(),
                                TextInput::make('cta_url')
                                    ->label(__('backend.banners.cta_url'))
                                    ->maxLength(2048)
                                    ->required(fn (Get $get): bool => $get('cta_type') === HomepageBannerCtaType::Url->value)
                                    ->visible(fn (Get $get): bool => $get('cta_type') === HomepageBannerCtaType::Url->value),
                                Select::make('category_id')
                                    ->label(__('backend.banners.cta_category'))
                                    ->relationship(
                                        'category',
                                        'name',
                                        fn (Builder $query): Builder => $query->where('is_enabled', true),
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->native(false)
                                    ->required(fn (Get $get): bool => $get('cta_type') === HomepageBannerCtaType::Category->value)
                                    ->visible(fn (Get $get): bool => $get('cta_type') === HomepageBannerCtaType::Category->value),
                                Select::make('product_id')
                                    ->label(__('backend.banners.cta_product'))
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
                                    ->required(fn (Get $get): bool => $get('cta_type') === HomepageBannerCtaType::Product->value)
                                    ->visible(fn (Get $get): bool => $get('cta_type') === HomepageBannerCtaType::Product->value),
                                Select::make('collection_id')
                                    ->label(__('backend.banners.cta_collection'))
                                    ->relationship('collection', 'name')
                                    ->getOptionLabelFromRecordUsing(
                                        fn (Collection $record): string => $record->name,
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->native(false)
                                    ->required(fn (Get $get): bool => $get('cta_type') === HomepageBannerCtaType::Collection->value)
                                    ->visible(fn (Get $get): bool => $get('cta_type') === HomepageBannerCtaType::Collection->value),
                                Select::make('brand_id')
                                    ->label(__('backend.banners.cta_brand'))
                                    ->relationship(
                                        'brand',
                                        'name',
                                        fn (Builder $query): Builder => $query->where('is_enabled', true),
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->native(false)
                                    ->required(fn (Get $get): bool => $get('cta_type') === HomepageBannerCtaType::Brand->value)
                                    ->visible(fn (Get $get): bool => $get('cta_type') === HomepageBannerCtaType::Brand->value),
                            ]),
                    ])
                    ->columnSpan(['lg' => 2]),
                Group::make()
                    ->schema([
                        Section::make(__('backend.banners.background'))
                            ->compact()
                            ->schema([
                                Radio::make('background_type')
                                    ->label(__('backend.banners.background'))
                                    ->options(HomepageBannerBackgroundType::options())
                                    ->inline()
                                    ->live()
                                    ->required(),
                                GradientSwatchPicker::make('gradient')
                                    ->label(__('backend.banners.gradient'))
                                    ->helperText(__('backend.banners.gradient_help'))
                                    ->nullable(),
                                GradientSwatchPicker::make('overlay_gradient')
                                    ->label(__('backend.banners.overlay_gradient'))
                                    ->helperText(__('backend.banners.overlay_gradient_help'))
                                    ->nullable(),
                                SpatieMediaLibraryFileUpload::make('background')
                                    ->label(__('backend.banners.background_image'))
                                    ->collection(HomepageBanner::MEDIA_BACKGROUND)
                                    ->image()
                                    ->maxSize(config('shopper.media.max_size.images'))
                                    ->required(fn (Get $get): bool => $get('background_type') === HomepageBannerBackgroundType::Image->value
                                        && $this->banner->getFirstMedia(HomepageBanner::MEDIA_BACKGROUND) === null)
                                    ->visible(fn (Get $get): bool => $get('background_type') === HomepageBannerBackgroundType::Image->value),
                                SpatieMediaLibraryFileUpload::make('accent')
                                    ->label(__('backend.banners.accent_image'))
                                    ->collection(HomepageBanner::MEDIA_ACCENT)
                                    ->image()
                                    ->maxSize(config('shopper.media.max_size.images')),
                                Toggle::make('is_enabled')
                                    ->label(__('backend.banners.visibility'))
                                    ->default(true),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3)
            ->statePath('data')
            ->model($this->banner);
    }

    public function store(): void
    {
        $creating = ! $this->banner->exists;

        $this->authorize($creating ? 'add_homepage_banners' : 'edit_homepage_banners');

        $data = $this->payload($this->form->getState());

        if ($creating) {
            $data['position'] = (int) HomepageBanner::query()->max('position') + 1;
            $this->banner = HomepageBanner::create($data);
            $this->form->model($this->banner)->saveRelationships();

            Notification::make()
                ->title(__('backend.banners.created'))
                ->success()
                ->send();

            $this->redirect(route('shopper.banners.edit', $this->banner), navigate: true);

            return;
        }

        $this->banner->update($data);

        Notification::make()
            ->title(__('backend.banners.updated'))
            ->success()
            ->send();
    }

    public function render(): View
    {
        return view('livewire.shopper.pages.homepage-banners.edit')
            ->title(
                $this->banner->exists
                    ? __('backend.banners.edit')
                    : __('backend.banners.create'),
            );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function payload(array $data): array
    {
        $ctaType = $data['cta_type'] instanceof HomepageBannerCtaType
            ? $data['cta_type']
            : HomepageBannerCtaType::from((string) $data['cta_type']);

        $data['cta_url'] = $ctaType === HomepageBannerCtaType::Url ? ($data['cta_url'] ?? null) : null;
        $data['category_id'] = $ctaType === HomepageBannerCtaType::Category ? ($data['category_id'] ?? null) : null;
        $data['product_id'] = $ctaType === HomepageBannerCtaType::Product ? ($data['product_id'] ?? null) : null;
        $data['collection_id'] = $ctaType === HomepageBannerCtaType::Collection ? ($data['collection_id'] ?? null) : null;
        $data['brand_id'] = $ctaType === HomepageBannerCtaType::Brand ? ($data['brand_id'] ?? null) : null;
        $data['gradient'] = $this->normalizeGradient($data['gradient'] ?? null);
        $data['overlay_gradient'] = $this->normalizeGradient($data['overlay_gradient'] ?? null);

        unset($data['background'], $data['accent']);

        return $data;
    }

    private function normalizeGradient(mixed $gradient): ?string
    {
        return TailwindTint::parse($gradient)?->value();
    }
}
