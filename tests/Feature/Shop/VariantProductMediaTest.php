<?php

declare(strict_types=1);

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Shopper\Core\Models\Currency;
use Shopper\Core\Models\Price;
use Shopper\Core\Models\Setting;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Storage::fake('public');

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

test('product page includes variant thumbnail and images for storefront media', function (): void {
    $product = Product::factory()->variant()->create([
        'name' => 'Variant media product',
        'slug' => 'variant-media-product',
    ]);

    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'name' => 'Red',
        'position' => 1,
    ]);

    Price::query()->create([
        'priceable_type' => 'variant',
        'priceable_id' => $variant->id,
        'amount' => 25000,
        'compare_amount' => null,
        'cost_amount' => null,
        'currency_id' => $this->currency->id,
    ]);

    $thumbnail = UploadedFile::fake()->image('variant-thumb.jpg');
    $gallery = UploadedFile::fake()->image('variant-gallery.jpg');

    $variant
        ->addMedia($thumbnail->getRealPath())
        ->usingFileName('variant-thumb.jpg')
        ->toMediaCollection('thumbnail', 'public');

    $variant
        ->addMedia($gallery->getRealPath())
        ->usingFileName('variant-gallery.jpg')
        ->toMediaCollection('uploads', 'public');

    $this->get(route('shop.product', $product))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shop/product')
            ->has('product.variants', 1)
            ->where('product.variants.0.id', $variant->id)
            ->where('product.variants.0.thumbnail', fn ($url): bool => is_string($url) && str_contains($url, 'variant-thumb'))
            ->has('product.variants.0.images', 1)
            ->where('product.variants.0.images.0.url', fn ($url): bool => is_string($url) && str_contains($url, 'variant-gallery'))
        );
});
