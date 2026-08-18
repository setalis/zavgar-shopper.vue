<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PendingProductImportStatus;
use App\Models\PendingProductImport;
use Illuminate\Database\Eloquent\Factories\Factory;
use Shopper\Core\Enum\ProductType;

/**
 * @extends Factory<PendingProductImport>
 */
final class PendingProductImportFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $sku = fake()->unique()->bothify('SKU-####');
        $name = fake()->words(3, true);

        return [
            'sku' => $sku,
            'name' => $name,
            'payload' => [
                'sku' => $sku,
                'name' => $name,
                'type' => ProductType::Standard->value,
                'is_visible' => true,
                'featured' => false,
            ],
            'status' => PendingProductImportStatus::Pending,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => PendingProductImportStatus::Approved,
            'approved_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => PendingProductImportStatus::Rejected,
        ]);
    }
}
