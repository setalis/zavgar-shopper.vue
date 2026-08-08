<?php

declare(strict_types=1);

namespace App\Livewire\Shopper\SlideOvers;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Shopper\Components\Separator;
use Shopper\Core\Enum\FieldType;
use Shopper\Core\Models\Attribute;
use Shopper\Livewire\SlideOvers\AttributeForm as BaseAttributeForm;

class AttributeForm extends BaseAttributeForm
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('shopper::forms.label.name'))
                    ->required()
                    ->live(onBlur: true)
                    ->maxLength(75)
                    ->afterStateUpdated(function (?Model $record, ?string $state, Set $set, Get $get): void {
                        if ($record?->exists && filled($get('slug'))) {
                            return;
                        }

                        if ($state) {
                            $set('slug', Str::slug($state));
                        }
                    }),
                TextInput::make('slug')
                    ->label(__('shopper::forms.label.slug'))
                    ->disabled()
                    ->dehydrated()
                    ->required()
                    ->maxLength(255)
                    ->unique(table: Attribute::class, column: 'slug', ignoreRecord: true),
                Select::make('type')
                    ->label(__('shopper::forms.label.type'))
                    ->options(FieldType::options())
                    ->required()
                    ->native(false),
                TextInput::make('icon')
                    ->label(__('shopper::forms.label.icon'))
                    ->placeholder('phosphor-package')
                    ->helperText('Phosphor icon name, e.g. phosphor-package, phosphor-tag'),
                Textarea::make('description')
                    ->label(__('shopper::forms.label.description'))
                    ->hint(__('shopper::words.characters', ['number' => 100]))
                    ->maxLength(100)
                    ->rows(3),
                Toggle::make('is_enabled')
                    ->label(__('shopper::forms.actions.enable'))
                    ->onColor('success')
                    ->helperText(__('shopper::pages/attributes.attribute_visibility')),
                Separator::make(),
                Checkbox::make('is_searchable')
                    ->label(__('shopper::forms.label.is_searchable'))
                    ->helperText(__('shopper::pages/attributes.searchable_description')),
                Checkbox::make('is_filterable')
                    ->label(__('shopper::forms.label.is_filterable'))
                    ->helperText(__('shopper::pages/attributes.filtrable_description')),
            ])
            ->statePath('data')
            ->model($this->attribute);
    }
}
