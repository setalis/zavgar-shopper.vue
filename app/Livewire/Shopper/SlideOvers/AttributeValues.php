<?php

declare(strict_types=1);

namespace App\Livewire\Shopper\SlideOvers;

use App\Models\CatalogTranslation;
use App\Support\CatalogEnglishFields;
use App\Support\CatalogFieldMap;
use Filament\Actions\Action;
use Filament\Schemas\Components\Component;
use Filament\Support\Enums\Width;
use Filament\Tables\Table;
use Mckenziearts\Icons\Untitledui\Enums\Untitledui;
use Shopper\Core\Models\AttributeValue;
use Shopper\Livewire\SlideOvers\AttributeValues as BaseAttributeValues;

final class AttributeValues extends BaseAttributeValues
{
    /**
     * @return array<array-key, Component>
     */
    public function formSchema(): array
    {
        return [
            ...parent::formSchema(),
            CatalogEnglishFields::input('value', dehydrated: true),
        ];
    }

    public function table(Table $table): Table
    {
        $table = parent::table($table);

        return $table
            ->recordActions([
                Action::make('edit')
                    ->authorize('edit_attributes')
                    ->icon(Untitledui::Edit03)
                    ->iconButton()
                    ->modalHeading(__('shopper::forms.actions.edit'))
                    ->modalWidth(Width::ExtraLarge)
                    ->fillForm(fn (AttributeValue $record): array => [
                        'key' => $record->key,
                        'value' => $record->value,
                        'english' => CatalogTranslation::payloadFor(
                            $record,
                            'en',
                            CatalogFieldMap::for($record),
                        ),
                    ])
                    ->schema($this->formSchema())
                    ->action(function (array $data, AttributeValue $record): void {
                        $record->update([
                            'key' => mb_strtolower($data['key']),
                            'value' => $data['value'],
                        ]);

                        CatalogTranslation::syncFor($record, 'en', [
                            'value' => $data['english']['value'] ?? null,
                        ]);

                        $this->dispatch('$refresh');
                    }),
                Action::make('delete')
                    ->authorize('delete_attributes')
                    ->icon(Untitledui::Trash03)
                    ->color('danger')
                    ->iconButton()
                    ->requiresConfirmation()
                    ->action(fn (AttributeValue $record) => $record->delete()),
            ])
            ->headerActions([
                Action::make('add')
                    ->label(__('shopper::forms.actions.add_label', ['label' => __('shopper::forms.label.value')]))
                    ->badge()
                    ->modalHeading(__('shopper::modals.attributes.new_value', ['attribute' => $this->attribute->name]))
                    ->modalWidth(Width::ExtraLarge)
                    ->schema($this->formSchema())
                    ->action(function (array $data): void {
                        $value = $this->attribute->values()->create([
                            'key' => mb_strtolower($data['key']),
                            'value' => $data['value'],
                        ]);

                        CatalogTranslation::syncFor($value, 'en', [
                            'value' => $data['english']['value'] ?? null,
                        ]);

                        $this->dispatch('$refresh');
                    }),
            ]);
    }
}
