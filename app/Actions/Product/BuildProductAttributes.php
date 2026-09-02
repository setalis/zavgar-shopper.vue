<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\Models\Product;
use Illuminate\Support\Collection;
use Shopper\Core\Enum\FieldType;
use Shopper\Core\Models\Attribute;
use Shopper\Core\Models\AttributeProduct;

final class BuildProductAttributes
{
    /**
     * @return list<array{id: int, name: string, slug: string, type: string, value: string}>
     */
    public function handle(Product $product): array
    {
        $variantAttributeIds = $this->variantAttributeIds($product);

        $rows = AttributeProduct::query()
            ->with(['attribute', 'value'])
            ->where('product_id', $product->id)
            ->whereHas('attribute', fn ($query) => $query->where('is_enabled', true))
            ->when(
                $variantAttributeIds !== [],
                fn ($query) => $query->whereNotIn('attribute_id', $variantAttributeIds),
            )
            ->orderBy('attribute_id')
            ->get();

        return $rows
            ->groupBy('attribute_id')
            ->map(fn (Collection $group): ?array => $this->mapAttributeGroup($group))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return list<int>
     */
    private function variantAttributeIds(Product $product): array
    {
        if (! $product->canUseVariants()) {
            return [];
        }

        $product->loadMissing('variants.values');

        return $product->variants
            ->flatMap(fn ($variant) => $variant->values->pluck('attribute_id'))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, AttributeProduct>  $group
     * @return array{id: int, name: string, slug: string, type: string, value: string}|null
     */
    private function mapAttributeGroup(Collection $group): ?array
    {
        $first = $group->first();
        $attribute = $first?->attribute;

        if (! $attribute instanceof Attribute) {
            return null;
        }

        $value = $this->formatValue($attribute, $group);

        if ($value === '') {
            return null;
        }

        return [
            'id' => $attribute->id,
            'name' => $attribute->name,
            'slug' => $attribute->slug,
            'type' => $attribute->type->value,
            'value' => $value,
        ];
    }

    /**
     * @param  Collection<int, AttributeProduct>  $group
     */
    private function formatValue(Attribute $attribute, Collection $group): string
    {
        $values = $group
            ->map(fn (AttributeProduct $row): ?string => $row->real_value)
            ->filter(fn (?string $value): bool => filled($value))
            ->unique()
            ->values();

        if ($values->isEmpty()) {
            return '';
        }

        $joined = $values->implode(', ');

        if ($attribute->type === FieldType::RichText) {
            return str($joined)->sanitizeHtml()->toString();
        }

        return $joined;
    }
}
