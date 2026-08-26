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

test('product page includes variant weight and volume specs', function (): void {
    $product = Product::factory()->variant()->create([
        'name' => 'Variant specs product',
        'slug' => 'variant-specs-product',
        'weight_value' => 1.0,
        'weight_unit' => 'kg',
        'volume_value' => 10.0,
        'volume_unit' => 'l',
    ]);

    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'name' => 'Large pack',
        'position' => 1,
        'weight_value' => 2.5,
        'weight_unit' => 'kg',
        'volume_value' => 25.0,
        'volume_unit' => 'l',
        'width_value' => 30.0,
        'height_value' => 20.0,
        'depth_value' => 10.0,
        'width_unit' => 'cm',
    ]);

    Price::query()->create([
        'priceable_type' => 'variant',
        'priceable_id' => $variant->id,
        'amount' => 15000,
        'compare_amount' => null,
        'cost_amount' => null,
        'currency_id' => $this->currency->id,
    ]);

    $this->get(route('shop.product', $product))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shop/product')
            ->has('product.variants', 1)
            ->where('product.variants.0.id', $variant->id)
            ->where('product.variants.0.weight_value', '2.50')
            ->where('product.variants.0.weight_unit', 'kg')
            ->where('product.variants.0.volume_value', '25.00')
            ->where('product.variants.0.volume_unit', 'l')
            ->where('product.variants.0.width_value', '30.00')
            ->where('product.variants.0.height_value', '20.00')
            ->where('product.variants.0.depth_value', '10.00')
        );
});
