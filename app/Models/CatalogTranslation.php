<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\CatalogTranslationObserver;
use Database\Factories\CatalogTranslationFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[ObservedBy([CatalogTranslationObserver::class])]
final class CatalogTranslation extends Model
{
    /** @use HasFactory<CatalogTranslationFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    public const array COLUMNS = [
        'name',
        'summary',
        'description',
        'seo_title',
        'seo_description',
        'value',
        'eyebrow',
        'highlight',
        'button_text',
    ];

    protected $fillable = [
        'translatable_type',
        'translatable_id',
        'locale',
        'name',
        'summary',
        'description',
        'seo_title',
        'seo_description',
        'value',
        'eyebrow',
        'highlight',
        'button_text',
    ];

    /**
     * @return MorphTo<Model, $this>
     */
    public function translatable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function syncFor(Model $model, string $locale, array $payload): void
    {
        $existing = self::query()
            ->where('translatable_type', $model->getMorphClass())
            ->where('translatable_id', $model->getKey())
            ->where('locale', $locale)
            ->first();

        $values = $existing instanceof self
            ? $existing->only(self::COLUMNS)
            : array_fill_keys(self::COLUMNS, null);

        foreach (self::COLUMNS as $column) {
            if (! array_key_exists($column, $payload)) {
                continue;
            }

            $value = $payload[$column];

            if (! is_string($value) || $value === '') {
                $values[$column] = null;

                continue;
            }

            $values[$column] = $value;
        }

        $hasContent = collect($values)->contains(fn (mixed $value): bool => filled($value));

        if (! $hasContent) {
            $existing?->delete();

            return;
        }

        self::query()->updateOrCreate(
            [
                'translatable_type' => $model->getMorphClass(),
                'translatable_id' => $model->getKey(),
                'locale' => $locale,
            ],
            $values,
        );
    }

    /**
     * @param  array<string, string>  $map
     * @return array<string, mixed>
     */
    public static function payloadFor(Model $model, string $locale, array $map): array
    {
        $translation = self::query()
            ->where('translatable_type', $model->getMorphClass())
            ->where('translatable_id', $model->getKey())
            ->where('locale', $locale)
            ->first();

        $payload = [];

        foreach ($map as $attribute => $column) {
            $payload[$attribute] = $translation?->{$column};
        }

        return $payload;
    }
}
