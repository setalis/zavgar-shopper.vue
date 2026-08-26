<?php

declare(strict_types=1);

use App\Models\Brand;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Shopper\Core\Enum\CollectionType;
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

test('home page renders storefront home with featured and latest products', function (): void {
    $featured = Product::factory()->standard()->create([
        'name' => 'Featured Camera',
        'slug' => 'featured-camera',
        'featured' => true,
    ]);

    $latest = Product::factory()->standard()->create([
        'name' => 'Latest Speaker',
        'slug' => 'latest-speaker',
        'featured' => false,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shop/home')
            ->has('featuredProducts', 1)
            ->where('featuredProducts.0.id', $featured->id)
            ->has('latestProducts', 2)
            ->has('categories')
            ->has('featuredCollections')
            ->has('shop.cart_count')
        );
});

test('shop index still paginates published products with filters', function (): void {
    $product = Product::factory()->standard()->create([
        'name' => 'Studio Headphones',
        'slug' => 'studio-headphones',
    ]);

    $this->get(route('shop.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shop/index')
            ->has('products.data', 1)
            ->where('products.data.0.id', $product->id)
            ->where('filters.search', '')
            ->where('filters.category', null)
            ->where('filters.sort', 'latest')
        );
});

test('cart page renders with cart and cart context props', function (): void {
    $this->get(route('shop.cart'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shop/cart')
            ->has('cart')
            ->has('cartContext')
            ->where('shop.cart_count', 0)
        );
});

test('categories index renders enabled root categories', function (): void {
    $root = Category::factory()->create([
        'name' => 'Audio',
        'slug' => 'audio',
        'is_enabled' => true,
        'parent_id' => null,
        'position' => 1,
    ]);

    Category::factory()->create([
        'name' => 'Headphones',
        'slug' => 'headphones',
        'is_enabled' => true,
        'parent_id' => $root->id,
        'position' => 1,
    ]);

    $this->get(route('shop.categories'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shop/categories')
            ->has('categories', 1)
            ->where('categories.0.id', $root->id)
            ->has('shop.cart_count')
        );
});

test('category page lists published products with sort filters', function (): void {
    $category = Category::factory()->create([
        'name' => 'Cameras',
        'slug' => 'cameras',
        'is_enabled' => true,
        'parent_id' => null,
    ]);

    $product = Product::factory()->standard()->create([
        'name' => 'Mirrorless Body',
        'slug' => 'mirrorless-body',
    ]);

    $product->categories()->attach($category);

    $this->get(route('shop.category', $category))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shop/category')
            ->where('category.id', $category->id)
            ->has('products.data', 1)
            ->where('products.data.0.id', $product->id)
            ->where('filters.sort', 'latest')
            ->has('shop.cart_count')
        );
});

test('collection page lists attached published products with sort filters', function (): void {
    $collection = Collection::factory()->create([
        'name' => 'Summer Edit',
        'slug' => 'summer-edit',
        'type' => CollectionType::Manual,
        'published_at' => now()->subDay(),
    ]);

    $product = Product::factory()->standard()->create([
        'name' => 'Linen Shirt',
        'slug' => 'linen-shirt',
    ]);

    $collection->products()->attach($product);

    $this->get(route('shop.collection', $collection))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shop/collection')
            ->where('collection.id', $collection->id)
            ->has('products.data', 1)
            ->where('products.data.0.id', $product->id)
            ->where('filters.sort', 'latest')
            ->has('shop.cart_count')
        );
});

test('unpublished collection pages are not found', function (): void {
    $collection = Collection::factory()->create([
        'name' => 'Draft Edit',
        'slug' => 'draft-edit',
        'type' => CollectionType::Manual,
        'published_at' => now()->addDay(),
    ]);

    $this->get(route('shop.collection', $collection))->assertNotFound();
});

test('brand page still lists published brand products', function (): void {
    $brand = Brand::factory()->create([
        'name' => 'Northwind',
        'slug' => 'northwind',
        'is_enabled' => true,
    ]);

    $product = Product::factory()->standard()->create([
        'name' => 'Northwind Lamp',
        'slug' => 'northwind-lamp',
        'brand_id' => $brand->id,
    ]);

    $this->get(route('shop.brand', $brand))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shop/brand')
            ->where('brand.id', $brand->id)
            ->has('products.data', 1)
            ->where('products.data.0.id', $product->id)
            ->has('shop.cart_count')
        );
});

test('search page keeps query and matching products', function (): void {
    $product = Product::factory()->standard()->create([
        'name' => 'Studio Microphone',
        'slug' => 'studio-microphone',
        'sku' => 'MIC-9001',
    ]);

    $this->get(route('shop.search', ['q' => 'Microphone']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shop/search')
            ->where('query', 'Microphone')
            ->has('products.data', 1)
            ->where('products.data.0.id', $product->id)
            ->has('shop.cart_count')
        );
});

test('product page renders a published product', function (): void {
    $product = Product::factory()->standard()->create([
        'name' => 'Field Recorder',
        'slug' => 'field-recorder',
    ]);

    $this->get(route('shop.product', $product))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shop/product')
            ->where('product.id', $product->id)
            ->where('product.slug', 'field-recorder')
            ->has('shop.cart_count')
        );
});

test('checkout redirects authenticated users with an empty cart to the cart page', function (): void {
    $this->actingAs(User::factory()->create())
        ->get(route('shop.checkout.index'))
        ->assertRedirect(route('shop.cart'));
});
