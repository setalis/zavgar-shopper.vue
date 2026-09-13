<?php

declare(strict_types=1);

namespace App\Livewire\Shopper\Components\Products\Form;

use App\Models\Product;
use App\Support\CatalogEnglishFields;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Set;
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

        $this->configureDraftSlugFields($schema);

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

        $values = CatalogEnglishFields::withoutEnglish($this->form->getState());

        if (blank($values['slug'] ?? null)) {
            $values['slug'] = (string) ($values['name'] ?? $this->product->name);
        }

        $this->product = app()->call(UpdateProductAction::class, [
            'values' => $values,
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

    private function configureDraftSlugFields(Schema $schema): void
    {
        foreach ($schema->getFlatFields(withHidden: true) as $field) {
            if (! $field instanceof TextInput) {
                continue;
            }

            if ($field->getName() === 'name') {
                $field
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (?string $state, Set $set): void {
                        if (filled($state) && blank($this->product->slug)) {
                            $set('slug', $this->uniqueDraftSlug($state));
                        }
                    });
            }

            if ($field->getName() === 'slug' && blank($this->product->slug)) {
                $field->required(false);
            }
        }
    }

    private function uniqueDraftSlug(string $name): string
    {
        $slug = str()->slug($name) ?: str()->random(5);
        $original = $slug;
        $counter = 0;

        while (
            Product::query()
                ->where('slug', $slug)
                ->where('id', '!=', $this->product->id)
                ->exists()
        ) {
            $counter++;
            $slug = $original.'-'.$counter;
        }

        return $slug;
    }
}
