<?php

declare(strict_types=1);

namespace App\Casts;

use App\Support\TailwindTint;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Database\Eloquent\SerializesCastableAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * @implements CastsAttributes<TailwindTint|null, TailwindTint|string|null>
 */
final class TailwindTintCast implements CastsAttributes, SerializesCastableAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?TailwindTint
    {
        return TailwindTint::parse($value);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        return TailwindTint::parse($value)?->value();
    }

    public function serialize(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        return $value instanceof TailwindTint ? $value->value() : TailwindTint::parse($value)?->value();
    }
}
