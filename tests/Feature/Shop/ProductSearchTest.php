<?php

declare(strict_types=1);

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Shopper\Core\Models\Currency;
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

test('search page finds a published product by sku', function (): void {
    $product = Product::factory()->standard()->create([
        'name' => 'Wireless Headphones',
        'slug' => 'wireless-headphones',
        'sku' => 'SKU-1001',
    ]);

    Product::factory()->standard()->create([
        'name' => 'Desk Lamp',
        'slug' => 'desk-lamp',
        'sku' => 'SKU-2002',
    ]);

    $this->get(route('shop.search', ['q' => 'SKU-1001']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shop/search')
            ->where('query', 'SKU-1001')
            ->has('products.data', 1)
            ->where('products.data.0.id', $product->id)
        );
});

test('search page finds a variant product by variant sku', function (): void {
    $product = Product::factory()->variant()->create([
        'name' => 'Winter Jacket',
        'slug' => 'winter-jacket',
        'sku' => 'SKU-PARENT',
    ]);

    ProductVariant::factory()->create([
        'product_id' => $product->id,
        'name' => 'Red',
        'sku' => 'SKU-JACKET-RED',
        'position' => 1,
    ]);

    Product::factory()->standard()->create([
        'name' => 'Other Product',
        'slug' => 'other-product',
        'sku' => 'SKU-OTHER',
    ]);

    $this->get(route('shop.search', ['q' => 'SKU-JACKET-RED']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shop/search')
            ->has('products.data', 1)
            ->where('products.data.0.id', $product->id)
            ->where('products.data.0.name', 'Winter Jacket')
        );
});

test('search page still finds products by name', function (): void {
    $product = Product::factory()->standard()->create([
        'name' => 'Wireless Headphones',
        'slug' => 'wireless-headphones',
        'sku' => 'SKU-1001',
    ]);

    Product::factory()->standard()->create([
        'name' => 'Desk Lamp',
        'slug' => 'desk-lamp',
        'sku' => 'SKU-2002',
    ]);

    $this->get(route('shop.search', ['q' => 'Headphones']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shop/search')
            ->has('products.data', 1)
            ->where('products.data.0.id', $product->id)
        );
});

test('search page does not return unpublished products matching the sku', function (): void {
    Product::factory()->create([
        'name' => 'Draft Widget',
        'slug' => 'draft-widget',
        'sku' => 'SKU-DRAFT-99',
        'is_visible' => false,
        'published_at' => now(),
    ]);

    $this->get(route('shop.search', ['q' => 'SKU-DRAFT-99']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shop/search')
            ->has('products.data', 0)
        );
});

test('search suggestions find a published product by sku', function (): void {
    $product = Product::factory()->standard()->create([
        'name' => 'Wireless Headphones',
        'slug' => 'wireless-headphones',
        'sku' => 'SKU-1001',
    ]);

    Product::factory()->standard()->create([
        'name' => 'Desk Lamp',
        'slug' => 'desk-lamp',
        'sku' => 'SKU-2002',
    ]);

    $this->getJson(route('shop.search.suggest', ['q' => 'SKU-1001']))
        ->assertSuccessful()
        ->assertJsonCount(1, 'products')
        ->assertJsonPath('products.0.id', $product->id)
        ->assertJsonPath('products.0.name', 'Wireless Headphones')
        ->assertJsonPath('products.0.slug', 'wireless-headphones')
        ->assertJsonPath('products.0.sku', 'SKU-1001');
});

test('search suggestions find a variant product by variant sku', function (): void {
    $product = Product::factory()->variant()->create([
        'name' => 'Winter Jacket',
        'slug' => 'winter-jacket',
        'sku' => 'SKU-PARENT',
    ]);

    ProductVariant::factory()->create([
        'product_id' => $product->id,
        'name' => 'Red',
        'sku' => 'SKU-JACKET-RED',
        'position' => 1,
    ]);

    Product::factory()->standard()->create([
        'name' => 'Other Product',
        'slug' => 'other-product',
        'sku' => 'SKU-OTHER',
    ]);

    $this->getJson(route('shop.search.suggest', ['q' => 'SKU-JACKET-RED']))
        ->assertSuccessful()
        ->assertJsonCount(1, 'products')
        ->assertJsonPath('products.0.id', $product->id)
        ->assertJsonPath('products.0.name', 'Winter Jacket');
});

test('search suggestions find products by name', function (): void {
    $product = Product::factory()->standard()->create([
        'name' => 'Wireless Headphones',
        'slug' => 'wireless-headphones',
        'sku' => 'SKU-1001',
    ]);

    Product::factory()->standard()->create([
        'name' => 'Desk Lamp',
        'slug' => 'desk-lamp',
        'sku' => 'SKU-2002',
    ]);

    $this->getJson(route('shop.search.suggest', ['q' => 'Headphones']))
        ->assertSuccessful()
        ->assertJsonCount(1, 'products')
        ->assertJsonPath('products.0.id', $product->id);
});

test('search suggestions do not return unpublished products', function (): void {
    Product::factory()->create([
        'name' => 'Draft Widget',
        'slug' => 'draft-widget',
        'sku' => 'SKU-DRAFT-99',
        'is_visible' => false,
        'published_at' => now(),
    ]);

    $this->getJson(route('shop.search.suggest', ['q' => 'SKU-DRAFT-99']))
        ->assertSuccessful()
        ->assertJsonCount(0, 'products');
});

test('search suggestions reject queries shorter than three characters', function (): void {
    $this->getJson(route('shop.search.suggest', ['q' => 'ab']))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['q']);
});

test('search suggestions return only the expected product keys', function (): void {
    Product::factory()->standard()->create([
        'name' => 'Wireless Headphones',
        'slug' => 'wireless-headphones',
        'sku' => 'SKU-1001',
    ]);

    $product = $this->getJson(route('shop.search.suggest', ['q' => 'Headphones']))
        ->assertSuccessful()
        ->json('products.0');

    expect(array_keys($product))->toEqualCanonicalizing([
        'id',
        'name',
        'slug',
        'sku',
        'thumbnail',
        'storefront_price',
    ]);
});

test('search suggestions return at most eight products', function (): void {
    foreach (range(1, 9) as $index) {
        Product::factory()->standard()->create([
            'name' => "Widget {$index}",
            'slug' => "widget-{$index}",
            'sku' => "SKU-W-{$index}",
        ]);
    }

    $this->getJson(route('shop.search.suggest', ['q' => 'Widget']))
        ->assertSuccessful()
        ->assertJsonCount(8, 'products');
});

test('shop catalog finds a published product by sku', function (): void {
    $product = Product::factory()->standard()->create([
        'name' => 'Wireless Headphones',
        'slug' => 'wireless-headphones',
        'sku' => 'SKU-1001',
    ]);

    Product::factory()->standard()->create([
        'name' => 'Desk Lamp',
        'slug' => 'desk-lamp',
        'sku' => 'SKU-2002',
    ]);

    $this->get(route('shop.index', ['search' => 'SKU-1001']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shop/index')
            ->where('filters.search', 'SKU-1001')
            ->has('products.data', 1)
            ->where('products.data.0.id', $product->id)
        );
});
