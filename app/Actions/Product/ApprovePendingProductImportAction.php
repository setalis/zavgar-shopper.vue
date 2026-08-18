<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\Enums\PendingProductImportStatus;
use App\Models\PendingProductImport;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Shopper\Actions\Store\Product\CreateProductAction;
use Shopper\Core\Enum\ProductType;
use Shopper\Core\Models\Brand;
use Shopper\Core\Models\Contracts\Channel;

final class ApprovePendingProductImportAction
{
    public function __construct(
        private CreateProductAction $createProduct,
        private ApplyImportedProductDataAction $applyImportedProductData,
        private SyncImportedVariantAction $syncImportedVariant,
    ) {}

    public function handle(PendingProductImport $pendingImport, User $approver): Product
    {
        if (! $pendingImport->isPending()) {
            throw ValidationException::withMessages([
                'status' => __('backend.product_imports.already_processed'),
            ]);
        }

        /** @var array<string, mixed> $payload */
        $payload = $pendingImport->payload;
        $parentSku = $payload['parent_sku'] ?? null;

        if (is_string($parentSku) && filled($parentSku)) {
            return $this->approveVariant($pendingImport, $approver, $payload, $parentSku);
        }

        $existing = Product::query()->where('sku', $pendingImport->sku)->first();

        if ($existing instanceof Product || ProductVariant::query()->where('sku', $pendingImport->sku)->exists()) {
            throw ValidationException::withMessages([
                'sku' => __('backend.product_imports.sku_already_exists'),
            ]);
        }

        $product = ($this->createProduct)($this->productAttributes($pendingImport, $payload));

        $this->syncDefaultChannel($product);
        $this->applyImportedProductData->handle($product, $payload);

        $pendingImport->update([
            'status' => PendingProductImportStatus::Approved,
            'product_id' => $product->id,
            'approved_by' => $approver->id,
            'approved_at' => now(),
        ]);

        return $product;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function productAttributes(PendingProductImport $pendingImport, array $payload): array
    {
        $type = ProductType::tryFrom((string) ($payload['type'] ?? '')) ?? ProductType::Standard;

        return [
            'name' => $pendingImport->name,
            'slug' => (string) ($payload['slug'] ?? $pendingImport->name),
            'sku' => $pendingImport->sku,
            'barcode' => Arr::get($payload, 'barcode'),
            'summary' => Arr::get($payload, 'summary'),
            'description' => Arr::get($payload, 'description'),
            'type' => $type,
            'is_visible' => (bool) ($payload['is_visible'] ?? true),
            'featured' => (bool) ($payload['featured'] ?? false),
            'published_at' => $payload['published_at'] ?? now(),
            'brand_id' => $this->resolveBrandId($payload),
            'quantity' => filled($payload['quantity'] ?? null) ? (int) $payload['quantity'] : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function approveVariant(PendingProductImport $pendingImport, User $approver, array $payload, string $parentSku): Product
    {
        $parent = Product::query()->where('sku', $parentSku)->first();

        if (! $parent instanceof Product) {
            throw ValidationException::withMessages([
                'parent_sku' => __('backend.product_imports.parent_not_found'),
            ]);
        }

        $existingVariant = ProductVariant::query()->where('sku', $pendingImport->sku)->first();

        if ($existingVariant instanceof ProductVariant) {
            throw ValidationException::withMessages([
                'sku' => __('backend.product_imports.sku_already_exists'),
            ]);
        }

        $this->syncImportedVariant->handle($parent, [
            ...$payload,
            'sku' => $pendingImport->sku,
            'name' => $pendingImport->name,
        ]);

        $pendingImport->update([
            'status' => PendingProductImportStatus::Approved,
            'product_id' => $parent->id,
            'approved_by' => $approver->id,
            'approved_at' => now(),
        ]);

        return $parent;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function resolveBrandId(array $payload): ?int
    {
        $brandName = $payload['brand'] ?? null;

        if (! is_string($brandName) || blank($brandName)) {
            return null;
        }

        return Brand::query()->where('name', $brandName)->value('id');
    }

    private function syncDefaultChannel(Product $product): void
    {
        $channelId = resolve(Channel::class)::query()
            ->scopes('default')
            ->value('id');

        if ($channelId) {
            $product->channels()->syncWithoutDetaching([$channelId]);
        }
    }
}
