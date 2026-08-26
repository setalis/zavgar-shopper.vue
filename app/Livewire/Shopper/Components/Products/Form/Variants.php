<?php

declare(strict_types=1);

namespace App\Livewire\Shopper\Components\Products\Form;

use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Table;
use Shopper\Core\Models\Contracts\ProductVariant;
use Shopper\Livewire\Components\Products\Form\Variants as BaseVariants;

final class Variants extends BaseVariants
{
    public function table(Table $table): Table
    {
        $table = parent::table($table);

        $columns = collect($table->getColumns())
            ->map(function ($column) {
                if ($column->getName() !== 'thumbnail') {
                    return $column;
                }

                return ImageColumn::make('thumbnail')
                    ->label(__('shopper::forms.label.thumbnail'))
                    ->getStateUsing(
                        fn (ProductVariant $record): ?string => $record->thumbnail,
                    )
                    ->circular();
            })
            ->all();

        return $table->columns($columns);
    }
}
