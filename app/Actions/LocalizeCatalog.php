<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\CatalogTranslation;
use App\Support\CatalogFieldMap;
use App\Support\StorefrontLocale;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Enumerable;
use Shopper\Cart\Models\Cart;

final class LocalizeCatalog
{
    public function handle(Model $model): Model
    {
        if (StorefrontLocale::isDefault()) {
            return $model;
        }

        $map = CatalogFieldMap::for($model);
        $translation = $this->translation($model);

        if ($translation === null) {
            return $model;
        }

        foreach ($map as $attribute => $column) {
            $value = $translation->{$column};

            if (filled($value)) {
                $model->setAttribute($attribute, $value);
            }
        }

        return $model;
    }

    public function many(iterable $models): void
    {
        foreach ($models as $model) {
            if ($model instanceof Model) {
                $this->handle($model);
            }
        }
    }

    public function paginator(AbstractPaginator $paginator): AbstractPaginator
    {
        $this->many($paginator->getCollection());

        return $paginator;
    }

    /**
     * @template T of Enumerable
     *
     * @param  T  $collection
     * @return T
     */
    public function collection(Enumerable $collection): Enumerable
    {
        $collection->each(function (mixed $model): void {
            if ($model instanceof Model) {
                $this->localizeModel($model);
            }
        });

        return $collection;
    }

    public function cart(?Cart $cart): void
    {
        if ($cart === null) {
            return;
        }

        $cart->loadMissing(['lines.purchasable']);

        foreach ($cart->lines as $line) {
            if ($line->purchasable instanceof Model) {
                $this->localizeModel($line->purchasable);
            }
        }
    }

    private function localizeModel(Model $model): Model
    {
        if (method_exists($model, 'localizeForStorefront')) {
            return $model->localizeForStorefront();
        }

        return $this->handle($model);
    }

    public function translationFor(Model $model, ?string $locale = null): ?CatalogTranslation
    {
        return $this->translation($model, $locale);
    }

    private function translation(Model $model, ?string $locale = null): ?CatalogTranslation
    {
        $locale ??= StorefrontLocale::current();

        if ($model->relationLoaded('translations')) {
            $match = $model->getRelation('translations')
                ->first(fn (mixed $translation): bool => $translation instanceof CatalogTranslation
                    && $translation->locale === $locale);

            return $match instanceof CatalogTranslation ? $match : null;
        }

        if (method_exists($model, 'translations')) {
            $loaded = $model->translations()
                ->where('locale', $locale)
                ->first();

            return $loaded instanceof CatalogTranslation ? $loaded : null;
        }

        return CatalogTranslation::query()
            ->where('translatable_type', $model->getMorphClass())
            ->where('translatable_id', $model->getKey())
            ->where('locale', $locale)
            ->first();
    }
}
