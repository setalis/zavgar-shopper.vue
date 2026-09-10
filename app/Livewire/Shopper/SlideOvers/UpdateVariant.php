<?php

declare(strict_types=1);

namespace App\Livewire\Shopper\SlideOvers;

use App\Models\ProductVariant;
use App\Support\CatalogEnglishFields;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Illuminate\Support\Arr;
use Shopper\Livewire\SlideOvers\UpdateVariant as BaseUpdateVariant;

final class UpdateVariant extends BaseUpdateVariant
{
    public function mount(): void
    {
        parent::mount();

        if ($this->variant instanceof ProductVariant) {
            $this->form->fill([
                ...$this->data ?? [],
                'english' => $this->variant->catalogTranslationPayload('en'),
            ]);
        }
    }

    public function form(Schema $schema): Schema
    {
        $schema = parent::form($schema);
        $components = $schema->getComponents();
        $name = array_shift($components);

        return $schema->components([
            ...($name !== null ? [$name] : []),
            CatalogEnglishFields::input('name'),
            ...$components,
        ]);
    }

    public function save(): void
    {
        $english = is_array($this->data['english'] ?? null) ? $this->data['english'] : [];

        $this->authorize('edit_product_variants');

        $state = CatalogEnglishFields::withoutEnglish($this->form->getState());
        $values = data_get($state, 'values');

        if ($values && $this->variantAlreadyExist($values)) {
            $this->alert = true;

            return;
        }

        $this->variant->update(Arr::except($state, 'values'));

        if ($values) {
            $this->variant->values()->sync($values);
        }

        if ($this->variant instanceof ProductVariant) {
            $this->variant->saveCatalogTranslation('en', $english);
        }

        Notification::make()
            ->title(__('shopper::pages/products.notifications.variation_update'))
            ->success()
            ->send();

        $this->redirect(
            route('shopper.products.variant', ['product' => $this->product, 'variant' => $this->variant]),
            navigate: true,
        );
    }
}
