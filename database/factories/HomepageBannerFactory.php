<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\HomepageBannerBackgroundType;
use App\Enums\HomepageBannerColor;
use App\Enums\HomepageBannerCtaType;
use App\Enums\HomepageBannerSize;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Collection;
use App\Models\HomepageBanner;
use App\Models\Product;
use App\Support\TailwindTint;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HomepageBanner>
 */
final class HomepageBannerFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'eyebrow' => fake()->words(2, true),
            'title' => fake()->sentence(4),
            'description' => fake()->sentence(12),
            'button_text' => fake()->words(2, true),
            'size' => HomepageBannerSize::Medium,
            'background_type' => HomepageBannerBackgroundType::Gradient,
            'gradient' => TailwindTint::of(HomepageBannerColor::Blue),
            'overlay_gradient' => null,
            'cta_type' => HomepageBannerCtaType::Url,
            'cta_url' => '/shop',
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

    public function large(): static
    {
        return $this->state(fn (array $attributes): array => [
            'size' => HomepageBannerSize::Large,
        ]);
    }

    public function forCategory(?Category $category = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'cta_type' => HomepageBannerCtaType::Category,
            'cta_url' => null,
            'category_id' => $category?->id ?? Category::factory(),
            'product_id' => null,
            'collection_id' => null,
            'brand_id' => null,
        ]);
    }

    public function forProduct(?Product $product = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'cta_type' => HomepageBannerCtaType::Product,
            'cta_url' => null,
            'category_id' => null,
            'product_id' => $product?->id ?? Product::factory()->standard(),
            'collection_id' => null,
            'brand_id' => null,
        ]);
    }

    public function forCollection(?Collection $collection = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'cta_type' => HomepageBannerCtaType::Collection,
            'cta_url' => null,
            'category_id' => null,
            'product_id' => null,
            'collection_id' => $collection?->id ?? Collection::factory(),
            'brand_id' => null,
        ]);
    }

    public function forBrand(?Brand $brand = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'cta_type' => HomepageBannerCtaType::Brand,
            'cta_url' => null,
            'category_id' => null,
            'product_id' => null,
            'collection_id' => null,
            'brand_id' => $brand?->id ?? Brand::factory(),
        ]);
    }
}
