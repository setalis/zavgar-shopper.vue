<?php

declare(strict_types=1);

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Shopper\Core\Models\Currency;
use Shopper\Core\Models\Price;
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

    $this->currency = $currency;
});

function createVariantProductWithPrices(Currency $currency, array $amounts): Product
{
    $product = Product::factory()->variant()->create([
        'featured' => true,
        'name' => 'Variant product',
        'slug' => 'variant-product',
    ]);

    foreach ($amounts as $index => $amount) {
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'name' => "Variant {$index}",
            'position' => $index + 1,
        ]);

        Price::query()->create([
            'priceable_type' => 'variant',
            'priceable_id' => $variant->id,
            'amount' => $amount,
            'compare_amount' => null,
            'cost_amount' => null,
            'currency_id' => $currency->id,
        ]);
    }

    return $product->fresh();
}

test('variant product storefront price uses the minimum variant amount', function (): void {
    $product = createVariantProductWithPrices($this->currency, [125000, 50000, 99000]);

    $priced = Product::query()
        ->select('id', 'name', 'slug', 'brand_id')
        ->withCurrentPrices()
        ->findOrFail($product->id);

    expect($priced->storefront_price)->toMatchArray([
        'amount' => 50000,
        'compare_amount' => null,
        'from' => true,
    ]);
});

test('home page includes storefront price from for featured variant products', function (): void {
    createVariantProductWithPrices($this->currency, [80000, 45000]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shop/home')
            ->has('featuredProducts', 1)
            ->where('featuredProducts.0.storefront_price.amount', 45000)
            ->where('featuredProducts.0.storefront_price.from', true)
        );
});

test('product page includes storefront price from when no variant is selected', function (): void {
    $product = createVariantProductWithPrices($this->currency, [30000, 70000]);

    $this->get(route('shop.product', $product))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shop/product')
            ->where('product.storefront_price.amount', 30000)
            ->where('product.storefront_price.from', true)
        );
});

test('standard product storefront price is not marked as from', function (): void {
    $product = Product::factory()->standard()->create([
        'featured' => true,
        'name' => 'Standard product',
        'slug' => 'standard-product',
    ]);

    Price::query()->create([
        'priceable_type' => 'product',
        'priceable_id' => $product->id,
        'amount' => 15000,
        'compare_amount' => null,
        'cost_amount' => null,
        'currency_id' => $this->currency->id,
    ]);

    $priced = Product::query()->withCurrentPrices()->findOrFail($product->id);

    expect($priced->storefront_price)->toMatchArray([
        'amount' => 15000,
        'compare_amount' => null,
        'from' => false,
    ]);
});
