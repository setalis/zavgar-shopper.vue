<?php

declare(strict_types=1);

namespace App\Livewire\Shopper\Components\Products\Form;

use App\Models\Product;
use App\Support\CatalogEnglishFields;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Shopper\Actions\Store\Product\UpdateProductAction;
use Shopper\Livewire\Components\Products\Form\Edit as BaseEdit;

final class Edit extends BaseEdit
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
        $schema = parent::form($schema);

        return $schema->components([
            ...$schema->getComponents(),
            CatalogEnglishFields::section(['name', 'summary', 'description']),
        ]);
    }

    public function store(): void
    {
        $english = is_array($this->data['english'] ?? null) ? $this->data['english'] : [];

        $this->authorize('edit_products');

        $this->validate();

        $this->product = app()->call(UpdateProductAction::class, [
            'values' => CatalogEnglishFields::withoutEnglish($this->form->getState()),
            'product' => $this->product,
        ]);

        $this->form->model($this->product)->saveRelationships();

        if ($this->product instanceof Product) {
            $this->product->saveCatalogTranslation('en', $english);
        }

        $this->dispatch('product.updated');

        Notification::make()
            ->title(__('shopper::notifications.update', ['item' => __('shopper::pages/products.single')]))
            ->success()
            ->send();
    }
}
