<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\Models\Brand;
use App\Models\Category;
use Illuminate\Support\Collection;
use Shopper\Core\Enum\FieldType;
use Shopper\Core\Models\Attribute;
use Shopper\Core\Models\AttributeProduct;
use Shopper\Core\Models\AttributeValue;

final class BuildCategoryAttributeFilters
{
    public const string BRAND_SLUG = 'brand';

    /**
     * @var list<FieldType>
     */
    private const array FILTERABLE_TYPES = [
        FieldType::Checkbox,
        FieldType::ColorPicker,
        FieldType::Number,
        FieldType::Select,
        FieldType::Text,
    ];

    /**
     * @return list<array{id: int, name: string, slug: string, type: string, values: list<array{key: string, label: string}>}>
     */
    public function handle(Category $category): array
    {
        $attributeFilters = $this->attributeFacets($category);
        $brandFilter = $this->brandFacet($category);

        if ($brandFilter === null) {
            return $attributeFilters;
        }

        return [$brandFilter, ...$attributeFilters];
    }

    /**
     * @return list<array{id: int, name: string, slug: string, type: string, values: list<array{key: string, label: string}>}>
     */
    private function attributeFacets(Category $category): array
    {
        $rows = AttributeProduct::query()
            ->with(['attribute', 'value'])
            ->whereHas(
                'attribute',
                fn ($query) => $query
                    ->enabled()
                    ->isFilterable()
                    ->whereIn('type', self::FILTERABLE_TYPES),
            )
            ->whereHas(
                'product',
                fn ($query) => $query
                    ->scopes('publish')
                    ->whereHas('categories', fn ($categories) => $categories->where('id', $category->id)),
            )
            ->get();

        return $rows
            ->groupBy('attribute_id')
            ->map(fn (Collection $group): ?array => $this->mapAttributeGroup($group))
            ->filter()
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->all();
    }

    /**
     * @return array{id: int, name: string, slug: string, type: string, values: list<array{key: string, label: string}>}|null
     */
    private function brandFacet(Category $category): ?array
    {
        $values = Brand::query()
            ->enabled()
            ->whereHas(
                'products',
                fn ($query) => $query
                    ->scopes('publish')
                    ->whereHas('categories', fn ($categories) => $categories->where('id', $category->id)),
            )
            ->orderBy('name')
            ->get(['id', 'name', 'slug'])
            ->map(fn (Brand $brand): ?array => $this->mapBrandValue($brand))
            ->filter()
            ->values()
            ->all();

        if ($values === []) {
            return null;
        }

        return [
            'id' => 0,
            'name' => 'Brand',
            'slug' => self::BRAND_SLUG,
            'type' => 'select',
            'values' => $values,
        ];
    }

    /**
     * @return array{key: string, label: string}|null
     */
    private function mapBrandValue(Brand $brand): ?array
    {
        if (! filled($brand->slug) || ! filled($brand->name)) {
            return null;
        }

        return [
            'key' => $brand->slug,
            'label' => $brand->name,
        ];
    }

    /**
     * @param  Collection<int, AttributeProduct>  $group
     * @return array{id: int, name: string, slug: string, type: string, values: list<array{key: string, label: string}>}|null
     */
    private function mapAttributeGroup(Collection $group): ?array
    {
        $first = $group->first();
        $attribute = $first?->attribute;

        if (! $attribute instanceof Attribute) {
            return null;
        }

        $values = $group
            ->map(fn (AttributeProduct $row): ?array => $this->mapValue($row))
            ->filter()
            ->unique('key')
            ->sortBy([
                fn (array $value): int => $value['position'],
                fn (array $value): string => mb_strtolower($value['label']),
            ])
            ->map(fn (array $value): array => [
                'key' => $value['key'],
                'label' => $value['label'],
            ])
            ->values()
            ->all();

        if ($values === []) {
            return null;
        }

        return [
            'id' => $attribute->id,
            'name' => $attribute->name,
            'slug' => $attribute->slug,
            'type' => $attribute->type->value,
            'values' => $values,
        ];
    }

    /**
     * @return array{key: string, label: string, position: int}|null
     */
    private function mapValue(AttributeProduct $row): ?array
    {
        $attributeValue = $row->value;

        if ($attributeValue instanceof AttributeValue && filled($attributeValue->key)) {
            return [
                'key' => $attributeValue->key,
                'label' => filled($attributeValue->value) ? $attributeValue->value : $attributeValue->key,
                'position' => $attributeValue->position,
            ];
        }

        $customValue = $row->attribute_custom_value;

        if (! filled($customValue)) {
            return null;
        }

        return [
            'key' => $customValue,
            'label' => $customValue,
            'position' => PHP_INT_MAX,
        ];
    }
}
