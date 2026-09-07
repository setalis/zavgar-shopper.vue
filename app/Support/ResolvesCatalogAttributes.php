<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Validation\ValidationException;
use Shopper\Core\Models\Attribute;
use Shopper\Core\Models\AttributeValue;

final class ResolvesCatalogAttributes
{
    public function attribute(string $name): Attribute
    {
        $attribute = Attribute::query()
            ->where(function ($query) use ($name): void {
                $query->where('name', $name)
                    ->orWhere('slug', str()->slug($name));
            })
            ->first();

        if (! $attribute instanceof Attribute) {
            throw ValidationException::withMessages([
                'attributes' => __('backend.product_imports.unknown_attribute', ['name' => $name]),
            ]);
        }

        return $attribute;
    }

    public function value(Attribute $attribute, string $value): AttributeValue
    {
        $attributeValue = AttributeValue::query()
            ->where('attribute_id', $attribute->id)
            ->where(function ($query) use ($value): void {
                $query->where('value', $value)
                    ->orWhere('key', str()->slug($value));
            })
            ->first();

        if ($attributeValue instanceof AttributeValue) {
            return $attributeValue;
        }

        return AttributeValue::create([
            'attribute_id' => $attribute->id,
            'key' => str()->slug($value),
            'value' => $value,
            'position' => (int) $attribute->values()->max('position') + 1,
        ]);
    }
}
