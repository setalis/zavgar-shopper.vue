<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\MenuItemTargetType;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Collection;
use App\Models\MenuItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MenuItem>
 */
final class MenuItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->words(2, true),
            'target_type' => MenuItemTargetType::Url,
            'url' => '/shop',
            'is_enabled' => true,
            'position' => 0,
        ];
    }

    public function disabled(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_enabled' => false,
        ]);
    }

    public function childOf(MenuItem $parent): static
    {
        return $this->state(fn (array $attributes): array => [
            'parent_id' => $parent->id,
        ]);
    }

    public function forBrand(?Brand $brand = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'target_type' => MenuItemTargetType::Brand,
            'url' => null,
            'brand_id' => $brand?->id ?? Brand::factory(),
            'category_id' => null,
            'collection_id' => null,
            'product_id' => null,
        ]);
    }

    public function forCategory(?Category $category = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'target_type' => MenuItemTargetType::Category,
            'url' => null,
            'brand_id' => null,
            'category_id' => $category?->id ?? Category::factory(),
            'collection_id' => null,
            'product_id' => null,
        ]);
    }

    public function forCollection(?Collection $collection = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'target_type' => MenuItemTargetType::Collection,
            'url' => null,
            'brand_id' => null,
            'category_id' => null,
            'collection_id' => $collection?->id ?? Collection::factory(),
            'product_id' => null,
        ]);
    }

    public function forProduct(?Product $product = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'target_type' => MenuItemTargetType::Product,
            'url' => null,
            'brand_id' => null,
            'category_id' => null,
            'collection_id' => null,
            'product_id' => $product?->id ?? Product::factory()->standard(),
        ]);
    }
}
