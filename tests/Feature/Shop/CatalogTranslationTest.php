<?php

declare(strict_types=1);

use App\Enums\HomepageBannerCtaType;
use App\Enums\HomepageBannerPlacement;
use App\Models\CatalogTranslation;
use App\Models\Category;
use App\Models\Collection;
use App\Models\HomepageBanner;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Support\StorefrontLocale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Shopper\Core\Enum\CollectionType;
use Shopper\Core\Enum\FieldType;
use Shopper\Core\Models\Attribute;
use Shopper\Core\Models\AttributeProduct;
use Shopper\Core\Models\AttributeValue;
use Shopper\Core\Models\Currency;
use Shopper\Core\Models\Inventory;
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

test('default storefront urls have no locale prefix', function (): void {
    $product = Product::factory()->standard()->create([
        'name' => 'Навушники',
        'slug' => 'navushnyky',
    ]);

    expect(route('shop.product', $product, false))->toBe('/shop/navushnyky')
        ->and(route('home', absolute: false))->toBe('/');
});

test('english product urls include the locale prefix', function (): void {
    $product = Product::factory()->standard()->create([
        'name' => 'Навушники',
        'slug' => 'navushnyky',
    ]);

    StorefrontLocale::applyUrlDefaults('en');

    expect(route('shop.product', $product, false))->toBe('/en/shop/navushnyky');
});

test('english storefront shows translated product fields with ukrainian fallback', function (): void {
    $product = Product::factory()->standard()->create([
        'name' => 'Навушники',
        'slug' => 'navushnyky',
        'summary' => 'Короткий опис',
        'description' => '<p>Опис українською</p>',
    ]);

    CatalogTranslation::syncFor($product, 'en', [
        'name' => 'Headphones',
        'description' => '<p>English description</p>',
    ]);

    $this->get(route('shop.product', $product))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('locale', 'uk')
            ->where('default_locale', 'uk')
            ->where('product.name', 'Навушники')
            ->where('product.summary', 'Короткий опис')
        );

    $this->get('/en/shop/navushnyky')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('locale', 'en')
            ->where('product.name', 'Headphones')
            ->where('product.summary', 'Короткий опис')
            ->where('product.description', '<p>English description</p>')
            ->has('hreflang')
            ->where('locale_urls.uk', '/shop/navushnyky')
            ->where('locale_urls.en', '/en/shop/navushnyky')
        );
});

test('ukrainian prefixed catalog urls redirect to the unprefixed path', function (): void {
    $product = Product::factory()->standard()->create([
        'name' => 'Навушники',
        'slug' => 'navushnyky',
    ]);

    $this->get('/uk/shop/navushnyky')
        ->assertRedirect('/shop/navushnyky');
});

test('english prefix does not break the default shop index', function (): void {
    Product::factory()->standard()->create([
        'name' => 'Навушники',
        'slug' => 'navushnyky',
    ]);

    $this->get('/shop')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shop/index')
            ->where('locale', 'uk')
            ->where('products.data.0.name', 'Навушники')
        );

    $this->get('/en/shop')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shop/index')
            ->where('locale', 'en')
        );
});

test('search finds an english product name on the english storefront', function (): void {
    $product = Product::factory()->standard()->create([
        'name' => 'Навушники',
        'slug' => 'navushnyky',
        'sku' => 'SKU-HEAD',
    ]);

    CatalogTranslation::syncFor($product, 'en', [
        'name' => 'Wireless Headphones',
    ]);

    $this->get(route('shop.search', ['q' => 'Wireless']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('products.data', 0)
        );

    $this->get('/en/search?q=Wireless')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('products.data', 1)
            ->where('products.data.0.id', $product->id)
            ->where('products.data.0.name', 'Wireless Headphones')
        );
});

test('category collection and nav overlay english names', function (): void {
    $category = Category::factory()->create([
        'name' => 'Аудіо',
        'slug' => 'audio',
        'is_enabled' => true,
        'parent_id' => null,
    ]);

    CatalogTranslation::syncFor($category, 'en', [
        'name' => 'Audio',
    ]);

    $collection = Collection::factory()->create([
        'name' => 'Літня',
        'slug' => 'litnia',
        'type' => CollectionType::Manual,
        'published_at' => now()->subDay(),
    ]);

    CatalogTranslation::syncFor($collection, 'en', [
        'name' => 'Summer',
    ]);

    $product = Product::factory()->standard()->create([
        'name' => 'Колонка',
        'slug' => 'kolonka',
    ]);
    $product->categories()->attach($category);
    $collection->products()->attach($product);

    $this->get(route('shop.category', $category))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('category.name', 'Аудіо')
            ->where('shop.nav_categories.0.name', 'Аудіо')
        );

    $this->get('/en/categories/audio')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('category.name', 'Audio')
            ->where('shop.nav_categories.0.name', 'Audio')
        );

    $this->get('/en/collections/litnia')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('collection.name', 'Summer')
        );
});

test('english storefront localizes specs filters and variant option labels', function (): void {
    $category = Category::factory()->create([
        'name' => 'Одяг',
        'slug' => 'odyag',
        'is_enabled' => true,
        'parent_id' => null,
    ]);

    $product = Product::factory()->variant()->create([
        'name' => 'Сорочка',
        'slug' => 'sorochka',
        'brand_id' => null,
    ]);
    $product->categories()->attach($category);

    $color = Attribute::factory()->create([
        'name' => 'Колір',
        'slug' => 'kolir',
        'type' => FieldType::Select,
        'is_enabled' => true,
        'is_filterable' => true,
    ]);
    $red = AttributeValue::factory()->create([
        'attribute_id' => $color->id,
        'key' => 'red',
        'value' => 'Червоний',
        'position' => 1,
    ]);

    $material = Attribute::factory()->create([
        'name' => 'Матеріал',
        'slug' => 'material',
        'type' => FieldType::Text,
        'is_enabled' => true,
        'is_filterable' => true,
    ]);

    $product->options()->attach($color->id, [
        'attribute_value_id' => $red->id,
    ]);
    $product->options()->attach($material->id, [
        'attribute_custom_value' => 'Бавовна',
    ]);

    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'name' => 'Червоний',
        'position' => 1,
    ]);
    $variant->values()->attach($red->id);

    Price::query()->create([
        'priceable_type' => 'variant',
        'priceable_id' => $variant->id,
        'amount' => 15000,
        'compare_amount' => null,
        'cost_amount' => null,
        'currency_id' => $this->currency->id,
    ]);

    CatalogTranslation::syncFor($color, 'en', ['name' => 'Color']);
    CatalogTranslation::syncFor($red, 'en', ['value' => 'Red']);
    CatalogTranslation::syncFor($material, 'en', ['name' => 'Material']);

    $customSpec = AttributeProduct::query()
        ->where('product_id', $product->id)
        ->where('attribute_id', $material->id)
        ->first();

    CatalogTranslation::syncFor($customSpec, 'en', [
        'value' => 'Cotton',
    ]);

    $this->get('/en/shop/sorochka')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('variantOptions.productOptions.0.name', 'Color')
            ->where('variantOptions.productOptions.0.values.0.value', 'Red')
            ->where('variantOptions.productOptions.0.values.0.key', 'red')
            ->where('productAttributes.0.name', 'Material')
            ->where('productAttributes.0.value', 'Cotton')
        );

    $this->get('/en/categories/odyag')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('attributeFilters.0.name', 'Color')
            ->where('attributeFilters.0.values.0.key', 'red')
            ->where('attributeFilters.0.values.0.label', 'Red')
        );
});

test('cart uses an explicit english variant name or joins translated values', function (): void {
    $product = Product::factory()->variant()->create([
        'name' => 'Сорочка',
        'slug' => 'sorochka',
        'allow_backorder' => true,
    ]);

    $color = Attribute::factory()->create([
        'name' => 'Колір',
        'slug' => 'kolir',
        'type' => FieldType::Select,
        'is_enabled' => true,
    ]);
    $red = AttributeValue::factory()->create([
        'attribute_id' => $color->id,
        'key' => 'red',
        'value' => 'Червоний',
        'position' => 1,
    ]);

    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'name' => 'Червоний',
        'position' => 1,
        'allow_backorder' => true,
    ]);
    $variant->values()->attach($red->id);

    Price::query()->create([
        'priceable_type' => 'variant',
        'priceable_id' => $variant->id,
        'amount' => 15000,
        'compare_amount' => null,
        'cost_amount' => null,
        'currency_id' => $this->currency->id,
    ]);

    $inventory = Inventory::factory()->create([
        'is_default' => true,
        'code' => 'i18n-wh',
    ]);
    $variant->mutateStock($inventory->id, 5);

    CatalogTranslation::syncFor($red, 'en', ['value' => 'Red']);

    $this->post('/en/cart', [
        'product_id' => $product->id,
        'variant_id' => $variant->id,
        'quantity' => 1,
    ])->assertRedirect();

    $this->get('/en/cart')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('cart.lines.0.purchasable.name', 'Red')
        );

    CatalogTranslation::syncFor($variant, 'en', ['name' => 'Crimson']);

    $this->get('/en/cart')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('cart.lines.0.purchasable.name', 'Crimson')
        );
});

test('homepage banners overlay english copy and prefix product ctas', function (): void {
    $product = Product::factory()->standard()->create([
        'name' => 'Колонка',
        'slug' => 'kolonka',
    ]);

    $banner = HomepageBanner::factory()->create([
        'title' => 'Літній розпродаж',
        'eyebrow' => 'Новинка',
        'button_text' => 'Купити',
        'placement' => HomepageBannerPlacement::Bento,
        'cta_type' => HomepageBannerCtaType::Product,
        'product_id' => $product->id,
        'is_enabled' => true,
    ]);

    CatalogTranslation::syncFor($banner, 'en', [
        'name' => 'Summer sale',
        'eyebrow' => 'New',
        'button_text' => 'Shop now',
    ]);

    $this->get('/')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('bentoBanners.0.title', 'Літній розпродаж')
            ->where('bentoBanners.0.button_text', 'Купити')
        );

    $this->get('/en')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('bentoBanners.0.title', 'Summer sale')
            ->where('bentoBanners.0.eyebrow', 'New')
            ->where('bentoBanners.0.button_text', 'Shop now')
            ->where('bentoBanners.0.href', fn ($href): bool => is_string($href) && str_contains($href, '/en/shop/kolonka'))
        );
});
