<?php

declare(strict_types=1);

namespace App\Http\Requests\Shop;

use Illuminate\Http\Request;

final class StorefrontPriceFilter
{
    /**
     * @return array<string, list<string>>
     */
    public static function rules(): array
    {
        return [
            'price_min' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'price_max' => ['sometimes', 'nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array{min: int|null, max: int|null}
     */
    public static function fromRequest(Request $request): array
    {
        return self::normalized($request->validate(self::rules()));
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array{min: int|null, max: int|null}
     */
    public static function normalized(array $validated): array
    {
        $min = self::intOrNull($validated['price_min'] ?? null);
        $max = self::intOrNull($validated['price_max'] ?? null);

        if ($min !== null && $max !== null && $min > $max) {
            return ['min' => $max, 'max' => $min];
        }

        return ['min' => $min, 'max' => $max];
    }

    private static function intOrNull(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }
}
