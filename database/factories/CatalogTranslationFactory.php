<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CatalogTranslation;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CatalogTranslation>
 */
final class CatalogTranslationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'translatable_type' => (new Product)->getMorphClass(),
            'translatable_id' => Product::factory(),
            'locale' => 'en',
            'name' => fake()->words(3, true),
            'summary' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'seo_title' => fake()->sentence(3),
            'seo_description' => fake()->sentence(),
        ];
    }
}
