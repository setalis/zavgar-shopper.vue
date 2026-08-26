<?php

declare(strict_types=1);

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Shopper\Core\Models\Currency;
use Shopper\Core\Models\Inventory;
use Shopper\Core\Models\Setting;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $currency = Currency::query()->create([
        'name' => 'US Dollar',
        'code' => 'USD',
        'symbol' => '$',
        'format' => '$1,234.56',
    ]);

    Setting::query()->create([
        'key' => 'default_currency_id',
        'display_name' => 'Currency',
        'value' => $currency->id,
        'locked' => true,
    ]);

    Cache::forget('shopper-setting.default_currency_id');
    Cache::forget('shopper-setting.default_currency');
});

/**
 * @param  list<int>  $quantities
 */
function createVariantProductWithStock(array $quantities): Product
{
    $product = Product::factory()->variant()->create([
        'featured' => true,
        'name' => 'Partial stock variant',
        'slug' => 'partial-stock-variant',
    ]);

    $inventory = Inventory::factory()->create([
        'is_default' => true,
        'code' => 'default-wh',
    ]);

    foreach ($quantities as $index => $quantity) {
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'name' => "Variant {$index}",
            'position' => $index + 1,
        ]);

        if ($quantity > 0) {
            $variant->mutateStock($inventory->id, $quantity);
        }
    }

    return $product->fresh();
}

test('home page reports variant product in stock when some variants have stock', function (): void {
    createVariantProductWithStock([5, 3, 0]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shop/home')
            ->has('featuredProducts', 1)
            ->where('featuredProducts.0.storefront_stock', 8)
        );
});

test('shop index reports variant product in stock when some variants have stock', function (): void {
    createVariantProductWithStock([5, 3, 0]);

    $this->get(route('shop.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shop/index')
            ->has('products.data', 1)
            ->where('products.data.0.storefront_stock', 8)
        );
});

test('standard product storefront stock uses the product inventory', function (): void {
    $product = Product::factory()->standard()->create([
        'featured' => true,
        'name' => 'Simple oil',
        'slug' => 'simple-oil',
    ]);

    $inventory = Inventory::factory()->create([
        'is_default' => true,
        'code' => 'simple-wh',
    ]);

    $product->mutateStock($inventory->id, 4);

    $this->get(route('shop.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shop/index')
            ->where('products.data.0.storefront_stock', 4)
        );
});
