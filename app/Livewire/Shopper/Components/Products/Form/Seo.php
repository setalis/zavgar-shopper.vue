<?php

declare(strict_types=1);

namespace App\Livewire\Shopper\Components\Products\Form;

use App\Models\Product;
use App\Support\CatalogEnglishFields;
use Filament\Forms\Components\KeyValue;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Schema;
use Shopper\Components\Form\SeoField;
use Shopper\Livewire\Components\Products\Form\Seo as BaseSeo;

final class Seo extends BaseSeo
{
    public function mount(): void
    {
        parent::mount();

        $this->form->fill([
            ...$this->data ?? [],
            'english' => $this->product instanceof Product
                ? $this->product->catalogTranslationPayload('en')
                : [],
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()
                    ->schema(SeoField::make()),
                CatalogEnglishFields::section(['seo_title', 'seo_description']),
                KeyValue::make('metadata')
                    ->reorderable(),
            ])
            ->statePath('data')
            ->model($this->product);
    }

    public function store(): void
    {
        $english = is_array($this->data['english'] ?? null) ? $this->data['english'] : [];

        $this->authorize('edit_products');

        $this->product->update(CatalogEnglishFields::withoutEnglish($this->form->getState()));

        if ($this->product instanceof Product) {
            $this->product->saveCatalogTranslation('en', $english);
        }

        $this->dispatch('product.updated');

        Notification::make()
            ->body(__('shopper::pages/products.notifications.seo_update'))
            ->success()
            ->send();
    }
}
