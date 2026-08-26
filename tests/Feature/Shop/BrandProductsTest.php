<?php

declare(strict_types=1);

use App\Models\Brand;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Shopper\Core\Models\Currency;
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
});

test('brand page lists published products of the brand', function (): void {
    $brand = Brand::factory()->create([
        'name' => 'Acme',
        'slug' => 'acme',
        'is_enabled' => true,
    ]);

    $otherBrand = Brand::factory()->create([
        'name' => 'Other',
        'slug' => 'other',
        'is_enabled' => true,
    ]);

    $product = Product::factory()->standard()->create([
        'name' => 'Acme Widget',
        'slug' => 'acme-widget',
        'brand_id' => $brand->id,
    ]);

    Product::factory()->standard()->create([
        'name' => 'Other Widget',
        'slug' => 'other-widget',
        'brand_id' => $otherBrand->id,
    ]);

    Product::factory()->create([
        'name' => 'Draft Widget',
        'slug' => 'draft-widget',
        'brand_id' => $brand->id,
        'is_visible' => false,
        'published_at' => now(),
    ]);

    $this->get(route('shop.brand', $brand))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shop/brand')
            ->where('brand.id', $brand->id)
            ->where('brand.name', 'Acme')
            ->where('brand.thumbnail', null)
            ->has('products.data', 1)
            ->where('products.data.0.id', $product->id)
            ->where('products.data.0.brand.name', 'Acme')
        );
});

test('brand page includes the brand logo when a thumbnail is attached', function (): void {
    $brand = Brand::factory()->create([
        'name' => 'Acme',
        'slug' => 'acme',
        'is_enabled' => true,
    ]);

    $logo = UploadedFile::fake()->image('acme-logo.png');

    $brand
        ->addMedia($logo->getRealPath())
        ->usingFileName('acme-logo.png')
        ->toMediaCollection('thumbnail', 'public');

    $this->get(route('shop.brand', $brand))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shop/brand')
            ->where('brand.thumbnail', fn ($url): bool => is_string($url) && str_contains($url, 'acme-logo'))
        );
});

test('disabled brand page returns not found', function (): void {
    $brand = Brand::factory()->create([
        'name' => 'Hidden',
        'slug' => 'hidden',
        'is_enabled' => false,
    ]);

    $this->get(route('shop.brand', $brand))->assertNotFound();
});

test('shop catalog includes brand name and logo on product cards', function (): void {
    $brand = Brand::factory()->create([
        'name' => 'Acme',
        'slug' => 'acme',
        'is_enabled' => true,
    ]);

    $logo = UploadedFile::fake()->image('acme-logo.png');

    $brand
        ->addMedia($logo->getRealPath())
        ->usingFileName('acme-logo.png')
        ->toMediaCollection('thumbnail', 'public');

    $product = Product::factory()->standard()->create([
        'name' => 'Acme Widget',
        'slug' => 'acme-widget',
        'brand_id' => $brand->id,
    ]);

    $this->get(route('shop.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shop/index')
            ->has('products.data', 1)
            ->where('products.data.0.id', $product->id)
            ->where('products.data.0.brand.name', 'Acme')
            ->where('products.data.0.brand.slug', 'acme')
            ->where('products.data.0.brand.thumbnail', fn ($url): bool => is_string($url) && str_contains($url, 'acme-logo'))
        );
});

test('product page includes brand logo when a thumbnail is attached', function (): void {
    $brand = Brand::factory()->create([
        'name' => 'Acme',
        'slug' => 'acme',
        'is_enabled' => true,
    ]);

    $logo = UploadedFile::fake()->image('acme-logo.png');

    $brand
        ->addMedia($logo->getRealPath())
        ->usingFileName('acme-logo.png')
        ->toMediaCollection('thumbnail', 'public');

    $product = Product::factory()->standard()->create([
        'name' => 'Acme Widget',
        'slug' => 'acme-widget',
        'brand_id' => $brand->id,
    ]);

    $this->get(route('shop.product', $product))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shop/product')
            ->where('product.brand.name', 'Acme')
            ->where('product.brand.thumbnail', fn ($url): bool => is_string($url) && str_contains($url, 'acme-logo'))
        );
});
