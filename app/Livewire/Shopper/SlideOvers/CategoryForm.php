<?php

declare(strict_types=1);

namespace App\Livewire\Shopper\SlideOvers;

use App\Models\Category;
use App\Support\CatalogEnglishFields;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Shopper\Components\Form\SeoField;
use Shopper\Components\Section;
use Shopper\Core\Models\Contracts\Category as CategoryContract;
use Shopper\Livewire\SlideOvers\CategoryForm as BaseCategoryForm;

final class CategoryForm extends BaseCategoryForm
{
    public function mount(?CategoryContract $category = null): void
    {
        parent::mount($category);

        if ($this->category instanceof Category && $this->category->exists) {
            $this->form->fill([
                ...$this->data ?? [],
                'english' => $this->category->catalogTranslationPayload('en'),
            ]);
        }
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('shopper::words.general'))
                    ->collapsible()
                    ->compact()
                    ->schema([
                        TextInput::make('name')
                            ->label(__('shopper::forms.label.name'))
                            ->placeholder('Women, Baby Shoes, MacBook...')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (?Model $record, ?string $state, Set $set, Get $get): void {
                                if ($record?->exists && filled($get('slug'))) {
                                    return;
                                }

                                if ($state) {
                                    $set('slug', Str::slug($state));
                                }
                            }),
                        Hidden::make('slug'),
                        Select::make('parent_id')
                            ->label(__('shopper::forms.label.parent'))
                            ->relationship(
                                name: 'parent',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn (Builder $query) => $query->where('is_enabled', true)
                            )
                            ->getOptionLabelFromRecordUsing(fn (CategoryContract $record) => $record->load('parent')->getLabelOptionName())
                            ->preload()
                            ->searchable()
                            ->optionsLimit(20)
                            ->placeholder(__('shopper::pages/categories.empty_parent')),
                        Toggle::make('is_enabled')
                            ->label(__('shopper::forms.label.visibility'))
                            ->helperText(__('shopper::words.set_visibility', ['name' => __('shopper::pages/categories.single')])),
                        RichEditor::make('description')
                            ->label(__('shopper::forms.label.description'))
                            ->toolbarButtons([
                                ['bold', 'italic', 'link', 'strike', 'underline'],
                                ['bulletList', 'orderedList', 'table', 'attachFiles'],
                                ['undo', 'redo'],
                            ]),
                    ]),
                CatalogEnglishFields::section(['name', 'description', 'seo_title', 'seo_description']),
                Section::make(__('shopper::words.media'))
                    ->collapsible()
                    ->compact()
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('file')
                            ->label(__('shopper::forms.label.image_preview'))
                            ->collection(config('shopper.media.storage.thumbnail_collection'))
                            ->image()
                            ->maxSize(config('shopper.media.max_size.thumbnail')),
                    ]),
                Section::make(__('shopper::words.seo.slug'))
                    ->collapsible()
                    ->compact()
                    ->schema(SeoField::make()),
                Section::make(__('Metadata'))
                    ->collapsible()
                    ->compact()
                    ->schema([
                        KeyValue::make('metadata')
                            ->hiddenLabel()
                            ->reorderable(),
                    ]),
            ])
            ->statePath('data')
            ->model($this->category);
    }

    public function save(): void
    {
        $english = is_array($this->data['english'] ?? null) ? $this->data['english'] : [];

        if ($this->category->id) {
            $this->authorize('edit_categories', $this->category);

            $this->category->update(CatalogEnglishFields::withoutEnglish($this->form->getState()));

            if ($this->category instanceof Category) {
                $this->category->saveCatalogTranslation('en', $english);
            }
        } else {
            $this->authorize('add_categories');

            $category = resolve(Category::class)::query()->create(CatalogEnglishFields::withoutEnglish($this->form->getState()));
            $this->form->model($category)->saveRelationships();

            if ($category instanceof Category) {
                $category->saveCatalogTranslation('en', $english);
            }
        }

        Notification::make()
            ->title(__('shopper::notifications.save', ['item' => __('shopper::pages/categories.single')]))
            ->success()
            ->send();

        $this->redirectRoute(
            name: 'shopper.categories.index',
            navigate: true,
        );
    }
}
