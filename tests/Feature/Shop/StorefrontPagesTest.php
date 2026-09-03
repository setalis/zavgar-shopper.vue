<?php

declare(strict_types=1);

use App\Enums\HomepageBannerBackgroundType;
use App\Enums\HomepageBannerColor;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Collection;
use App\Models\HomepageBanner;
use App\Models\Product;
use App\Models\User;
use App\Support\TailwindTint;
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
            ->has('bentoBanners', 0)
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

test('home page includes enabled bento banners in position order', function (): void {
    $second = HomepageBanner::factory()->create([
        'title' => 'Second card',
        'position' => 2,
        'cta_url' => '/shop',
        'button_text' => 'Shop',
    ]);

    $first = HomepageBanner::factory()->create([
        'title' => 'First card',
        'position' => 1,
        'cta_url' => '/contact',
        'button_text' => 'Contact',
    ]);

    HomepageBanner::factory()->disabled()->create([
        'title' => 'Hidden card',
        'position' => 0,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shop/home')
            ->has('bentoBanners', 2)
            ->where('bentoBanners.0.id', $first->id)
            ->where('bentoBanners.0.title', 'First card')
            ->where('bentoBanners.0.href', '/contact')
            ->where('bentoBanners.1.id', $second->id)
            ->where('bentoBanners.1.href', '/shop')
        );
});

test('home page resolves bento banner hrefs for catalog targets', function (): void {
    $category = Category::factory()->create([
        'name' => 'Oils',
        'slug' => 'oils',
        'is_enabled' => true,
    ]);

    $product = Product::factory()->standard()->create([
        'name' => 'Motor Oil',
        'slug' => 'motor-oil',
    ]);

    $collection = Collection::factory()->create([
        'name' => 'Summer Kit',
        'slug' => 'summer-kit',
        'type' => CollectionType::Manual,
        'published_at' => now()->subDay(),
    ]);

    $brand = Brand::factory()->create([
        'name' => 'Castrol',
        'slug' => 'castrol',
        'is_enabled' => true,
    ]);

    HomepageBanner::factory()->forCategory($category)->create([
        'title' => 'Category banner',
        'button_text' => 'Browse',
        'position' => 1,
    ]);

    HomepageBanner::factory()->forProduct($product)->create([
        'title' => 'Product banner',
        'button_text' => 'View',
        'position' => 2,
    ]);

    HomepageBanner::factory()->forCollection($collection)->create([
        'title' => 'Collection banner',
        'button_text' => 'Open',
        'position' => 3,
    ]);

    HomepageBanner::factory()->forBrand($brand)->create([
        'title' => 'Brand banner',
        'button_text' => 'Brand',
        'position' => 4,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shop/home')
            ->has('bentoBanners', 4)
            ->where('bentoBanners.0.href', route('shop.category', $category))
            ->where('bentoBanners.1.href', route('shop.product', $product))
            ->where('bentoBanners.2.href', route('shop.collection', $collection))
            ->where('bentoBanners.3.href', route('shop.brand', $brand))
        );
});

test('home page hides bento banner buttons when the catalog target is unavailable', function (): void {
    $category = Category::factory()->create([
        'name' => 'Hidden Oils',
        'slug' => 'hidden-oils',
        'is_enabled' => false,
    ]);

    $product = Product::factory()->create([
        'name' => 'Draft Oil',
        'slug' => 'draft-oil',
        'is_visible' => false,
        'published_at' => now(),
    ]);

    HomepageBanner::factory()->forCategory($category)->create([
        'title' => 'Disabled category',
        'button_text' => 'Browse',
        'position' => 1,
    ]);

    HomepageBanner::factory()->forProduct($product)->create([
        'title' => 'Draft product',
        'button_text' => 'View',
        'position' => 2,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shop/home')
            ->has('bentoBanners', 2)
            ->where('bentoBanners.0.href', null)
            ->where('bentoBanners.0.button_text', null)
            ->where('bentoBanners.1.href', null)
            ->where('bentoBanners.1.button_text', null)
        );
});

test('home page includes independent fill and overlay gradients', function (): void {
    HomepageBanner::factory()->create([
        'title' => 'Tinted photo',
        'background_type' => HomepageBannerBackgroundType::Image,
        'gradient' => TailwindTint::of(HomepageBannerColor::Purple),
        'overlay_gradient' => TailwindTint::of(HomepageBannerColor::Orange),
        'button_text' => 'Shop',
        'cta_url' => '/shop',
        'position' => 1,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shop/home')
            ->has('bentoBanners', 1)
            ->where('bentoBanners.0.background_type', HomepageBannerBackgroundType::Image->value)
            ->where('bentoBanners.0.gradient.token', TailwindTint::of(HomepageBannerColor::Purple)->value())
            ->where('bentoBanners.0.overlay_gradient.token', TailwindTint::of(HomepageBannerColor::Orange)->value())
        );
});

test('home page omits gradients when none are selected', function (): void {
    HomepageBanner::factory()->create([
        'title' => 'Plain card',
        'gradient' => null,
        'overlay_gradient' => null,
        'button_text' => 'Shop',
        'cta_url' => '/shop',
        'position' => 1,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shop/home')
            ->where('bentoBanners.0.gradient', null)
            ->where('bentoBanners.0.overlay_gradient', null)
        );
});
