<?php

declare(strict_types=1);

namespace App\Livewire\Shopper\Pages\Collection;

use App\Models\Collection;
use App\Support\CatalogEnglishFields;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Shopper\Livewire\Pages\Collection\Edit as BaseEdit;

final class Edit extends BaseEdit
{
    public function mount(): void
    {
        parent::mount();

        if ($this->collection instanceof Collection) {
            $this->form->fill([
                ...$this->data ?? [],
                'english' => $this->collection->catalogTranslationPayload('en'),
            ]);
        }
    }

    public function form(Schema $schema): Schema
    {
        $schema = parent::form($schema);

        return $schema->components([
            ...$schema->getComponents(),
            CatalogEnglishFields::section(['name', 'description', 'seo_title', 'seo_description']),
        ]);
    }

    public function store(): void
    {
        $english = is_array($this->data['english'] ?? null) ? $this->data['english'] : [];

        $this->authorize('edit_collections');

        $this->collection->update(CatalogEnglishFields::withoutEnglish($this->form->getState()));

        if ($this->collection instanceof Collection) {
            $this->collection->saveCatalogTranslation('en', $english);
        }

        Notification::make()
            ->title(__('shopper::notifications.update', ['item' => __('shopper::pages/collections.single')]))
            ->success()
            ->send();
    }
}
