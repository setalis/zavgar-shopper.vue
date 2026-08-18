<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\ProductVariant;

final class FormatsVariantAttributes
{
    /**
     * @return list<array{name: string, value: string}>
     */
    public function parse(?string $attributes): array
    {
        if (! is_string($attributes) || blank($attributes)) {
            return [];
        }

        $pairs = [];

        foreach (explode('|', $attributes) as $part) {
            $part = trim($part);

            if ($part === '' || ! str_contains($part, '=')) {
                continue;
            }

            [$name, $value] = explode('=', $part, 2);
            $name = trim($name);
            $value = trim($value);

            if ($name === '' || $value === '') {
                continue;
            }

            $pairs[] = [
                'name' => $name,
                'value' => $value,
            ];
        }

        return $pairs;
    }

    public function toString(ProductVariant $variant): string
    {
        $variant->loadMissing('values.attribute');

        return $variant->values
            ->filter(fn ($value): bool => $value->attribute !== null)
            ->map(fn ($value): string => $value->attribute->name.'='.$value->value)
            ->implode(' | ');
    }
}
