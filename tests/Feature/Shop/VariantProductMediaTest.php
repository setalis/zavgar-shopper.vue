<?php

declare(strict_types=1);

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Shopper\Core\Models\Currency;
use Shopper\Core\Models\Inventory;
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

test('variant thumbnail falls back to the first gallery image when no thumbnail is set', function (): void {
    $product = Product::factory()->variant()->create([
        'name' => 'Gallery only variant',
        'slug' => 'gallery-only-variant',
    ]);

    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'name' => 'Blue',
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

    $first = UploadedFile::fake()->image('variant-first.jpg');
    $second = UploadedFile::fake()->image('variant-second.jpg');

    $variant
        ->addMedia($first->getRealPath())
        ->usingFileName('variant-first.jpg')
        ->toMediaCollection('uploads', 'public');

    $variant
        ->addMedia($second->getRealPath())
        ->usingFileName('variant-second.jpg')
        ->toMediaCollection('uploads', 'public');

    $this->get(route('shop.product', $product))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shop/product')
            ->has('product.variants', 1)
            ->where('product.variants.0.thumbnail', fn ($url): bool => is_string($url) && str_contains($url, 'variant-first'))
            ->has('product.variants.0.images', 1)
            ->where('product.variants.0.images.0.url', fn ($url): bool => is_string($url) && str_contains($url, 'variant-second'))
        );
});

test('product gallery images omit uploads that duplicate the thumbnail file name', function (): void {
    $product = Product::factory()->standard()->create([
        'name' => 'Duplicated cover',
        'slug' => 'duplicated-cover',
    ]);

    $cover = UploadedFile::fake()->image('cover.jpg');
    $coverCopy = UploadedFile::fake()->image('cover.jpg');
    $extra = UploadedFile::fake()->image('detail.jpg');

    $product
        ->addMedia($cover->getRealPath())
        ->usingFileName('cover.jpg')
        ->toMediaCollection('thumbnail', 'public');

    $product
        ->addMedia($coverCopy->getRealPath())
        ->usingFileName('cover.jpg')
        ->toMediaCollection('uploads', 'public');

    $product
        ->addMedia($extra->getRealPath())
        ->usingFileName('detail.jpg')
        ->toMediaCollection('uploads', 'public');

    $this->get(route('shop.product', $product))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shop/product')
            ->where('product.thumbnail', fn ($url): bool => is_string($url) && str_contains($url, 'cover'))
            ->has('product.images', 1)
            ->where('product.images.0.url', fn ($url): bool => is_string($url) && str_contains($url, 'detail'))
        );
});

test('product gallery images omit uploads that share the thumbnail original name', function (): void {
    $product = Product::factory()->standard()->create([
        'name' => 'Hashed cover duplicate',
        'slug' => 'hashed-cover-duplicate',
    ]);

    $cover = UploadedFile::fake()->image('cover.jpg');
    $coverCopy = UploadedFile::fake()->image('cover.jpg');
    $extra = UploadedFile::fake()->image('detail.jpg');

    $product
        ->addMedia($cover->getRealPath())
        ->usingName('72040-5-b')
        ->usingFileName('01THUMBHASH.png')
        ->toMediaCollection('thumbnail', 'public');

    $product
        ->addMedia($coverCopy->getRealPath())
        ->usingName('72040-5-b')
        ->usingFileName('01UPLOADHASH.png')
        ->toMediaCollection('uploads', 'public');

    $product
        ->addMedia($extra->getRealPath())
        ->usingName('72040-5-detail')
        ->usingFileName('01DETAILHASH.png')
        ->toMediaCollection('uploads', 'public');

    $this->get(route('shop.product', $product))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shop/product')
            ->where('product.thumbnail', fn ($url): bool => is_string($url) && str_contains($url, '01THUMBHASH'))
            ->has('product.images', 1)
            ->where('product.images.0.url', fn ($url): bool => is_string($url) && str_contains($url, '01DETAILHASH'))
        );
});

test('cart line uses the first gallery image when a variant has no thumbnail', function (): void {
    $product = Product::factory()->variant()->create([
        'name' => 'Cart gallery variant',
        'slug' => 'cart-gallery-variant',
    ]);

    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'name' => 'Green',
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

    $inventory = Inventory::factory()->create([
        'is_default' => true,
        'code' => 'cart-gallery-wh',
    ]);

    $variant->mutateStock($inventory->id, 5);

    $gallery = UploadedFile::fake()->image('cart-variant-gallery.jpg');

    $variant
        ->addMedia($gallery->getRealPath())
        ->usingFileName('cart-variant-gallery.jpg')
        ->toMediaCollection('uploads', 'public');

    $this->post(route('shop.cart.add'), [
        'product_id' => $product->id,
        'variant_id' => $variant->id,
        'quantity' => 1,
    ])->assertRedirect();

    $this->get(route('shop.cart'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shop/cart')
            ->has('cart.lines', 1)
            ->where('cart.lines.0.purchasable.thumbnail', fn ($url): bool => is_string($url) && str_contains($url, 'cart-variant-gallery'))
        );
});
