<?php

declare(strict_types=1);

namespace App\Support;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Arr;

final class CatalogEnglishFields
{
    /**
     * @param  array<string, mixed>  $state
     * @return array<string, mixed>
     */
    public static function withoutEnglish(array $state): array
    {
        return Arr::except($state, ['english']);
    }

    /**
     * @param  list<string>  $fields
     */
    public static function section(array $fields, bool $richDescription = true): Section
    {
        return Section::make(__('backend.catalog.english'))
            ->schema(self::inputs($fields, $richDescription))
            ->compact()
            ->dehydrated(false)
            ->columnSpanFull();
    }

    /**
     * @param  list<string>  $fields
     * @return list<Component>
     */
    public static function inputs(array $fields, bool $richDescription = true): array
    {
        return array_map(
            fn (string $field): Component => self::input($field, $richDescription),
            $fields,
        );
    }

    public static function input(string $field, bool $richDescription = true, bool $dehydrated = false): Component
    {
        $name = "english.{$field}";
        $label = __('backend.catalog.fields.'.$field);

        return match ($field) {
            'description' => $richDescription
                ? RichEditor::make($name)
                    ->label($label)
                    ->dehydrated($dehydrated)
                    ->columnSpanFull()
                : Textarea::make($name)
                    ->label($label)
                    ->dehydrated($dehydrated)
                    ->rows(3)
                    ->columnSpanFull(),
            'summary', 'seo_description' => Textarea::make($name)
                ->label($label)
                ->dehydrated($dehydrated)
                ->rows(3)
                ->columnSpanFull(),
            default => TextInput::make($name)
                ->label($label)
                ->dehydrated($dehydrated)
                ->maxLength(255),
        };
    }
}
