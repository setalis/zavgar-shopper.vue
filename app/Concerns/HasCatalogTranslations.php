<?php

declare(strict_types=1);

namespace App\Concerns;

use App\Actions\LocalizeCatalog;
use App\Models\CatalogTranslation;
use App\Support\StorefrontLocale;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasCatalogTranslations
{
    /**
     * @return MorphMany<CatalogTranslation, $this>
     */
    public function translations(): MorphMany
    {
        return $this->morphMany(CatalogTranslation::class, 'translatable');
    }

    /**
     * @return array<string, string>
     */
    public function catalogTranslationMap(): array
    {
        return [
            'name' => 'name',
            'summary' => 'summary',
            'description' => 'description',
            'seo_title' => 'seo_title',
            'seo_description' => 'seo_description',
        ];
    }

    public function catalogTranslation(?string $locale = null): ?CatalogTranslation
    {
        $locale ??= StorefrontLocale::current();

        if ($this->relationLoaded('translations')) {
            $match = $this->translations->firstWhere('locale', $locale);

            return $match instanceof CatalogTranslation ? $match : null;
        }

        $translation = $this->translations()->where('locale', $locale)->first();

        return $translation instanceof CatalogTranslation ? $translation : null;
    }

    /**
     * @return array<string, mixed>
     */
    public function catalogTranslationPayload(?string $locale = null): array
    {
        $locale ??= 'en';
        $translation = $this->catalogTranslation($locale);
        $payload = [];

        foreach ($this->catalogTranslationMap() as $attribute => $column) {
            $payload[$attribute] = $translation?->{$column};
        }

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function saveCatalogTranslation(string $locale, array $payload): void
    {
        $mapped = [];

        foreach ($this->catalogTranslationMap() as $attribute => $column) {
            if (array_key_exists($attribute, $payload)) {
                $mapped[$column] = $payload[$attribute];
            }
        }

        CatalogTranslation::syncFor($this, $locale, $mapped);
        $this->unsetRelation('translations');
    }

    /**
     * @param  Builder<covariant Model>  $query
     * @return Builder<covariant Model>
     */
    public function scopeWithStorefrontTranslations(Builder $query): Builder
    {
        if (StorefrontLocale::isDefault()) {
            return $query;
        }

        return $query->with([
            'translations' => fn ($translations) => $translations
                ->where('locale', StorefrontLocale::current()),
        ]);
    }

    public function localizeForStorefront(): static
    {
        resolve(LocalizeCatalog::class)->handle($this);

        return $this;
    }

    /**
     * @param  Builder<covariant Model>  $query
     * @return Builder<covariant Model>
     */
    public function scopeOrderByLocalizedName(Builder $query): Builder
    {
        $table = $query->getModel()->getTable();

        if (StorefrontLocale::isDefault()) {
            return $query->orderBy("{$table}.name");
        }

        $translations = (new CatalogTranslation)->getTable();
        $type = $query->getModel()->getMorphClass();
        $locale = StorefrontLocale::current();

        return $query->orderByRaw(
            "COALESCE((select {$translations}.name from {$translations} where {$translations}.translatable_type = ? and {$translations}.translatable_id = {$table}.id and {$translations}.locale = ? limit 1), {$table}.name)",
            [$type, $locale],
        );
    }
}
