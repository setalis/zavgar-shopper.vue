<?php

declare(strict_types=1);

use App\Models\Brand;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Shopper\Core\Enum\CollectionType;
use Shopper\Core\Enum\FieldType;
use Shopper\Core\Models\Attribute;
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

/**
 * @param  array<string, mixed>  $attributes
 */
function storefrontPriceFilterProduct(Currency $currency, int $amount, array $attributes = []): Product
{
    $product = Product::factory()->standard()->create($attributes);

    Price::query()->create([
        'priceable_type' => 'product',
        'priceable_id' => $product->id,
        'amount' => $amount,
        'compare_amount' => null,
        'cost_amount' => null,
        'currency_id' => $currency->id,
    ]);

    return $product->fresh();
}

/**
 * @param  list<int>  $amounts
 * @param  array<string, mixed>  $attributes
 */
function storefrontPriceFilterVariantProduct(Currency $currency, array $amounts, array $attributes = []): Product
{
    $product = Product::factory()->variant()->create($attributes);

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

test('shop index filters products by storefront price range', function (): void {
    $cheap = storefrontPriceFilterProduct($this->currency, 1000, [
        'name' => 'Cheap Lamp',
        'slug' => 'cheap-lamp',
        'created_at' => now()->subDay(),
    ]);
    storefrontPriceFilterProduct($this->currency, 5000, [
        'name' => 'Expensive Lamp',
        'slug' => 'expensive-lamp',
    ]);

    $this->get(route('shop.index', ['price_min' => 500, 'price_max' => 2000]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shop/index')
            ->has('products.data', 1)
            ->where('products.data.0.id', $cheap->id)
            ->where('priceRange.min', 1000)
            ->where('priceRange.max', 5000)
            ->where('filters.price_min', 500)
            ->where('filters.price_max', 2000)
        );
});

test('category page filters products by storefront price range', function (): void {
    $category = Category::factory()->create([
        'name' => 'Lamps',
        'slug' => 'lamps',
        'is_enabled' => true,
        'parent_id' => null,
    ]);

    $cheap = storefrontPriceFilterProduct($this->currency, 1000, [
        'name' => 'Cheap Lamp',
        'slug' => 'cheap-lamp',
        'created_at' => now()->subDay(),
    ]);
    $cheap->categories()->attach($category);

    $expensive = storefrontPriceFilterProduct($this->currency, 5000, [
        'name' => 'Expensive Lamp',
        'slug' => 'expensive-lamp',
    ]);
    $expensive->categories()->attach($category);

    $this->get(route('shop.category', [
        'category' => $category,
        'price_min' => 500,
        'price_max' => 2000,
    ]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shop/category')
            ->has('products.data', 1)
            ->where('products.data.0.id', $cheap->id)
            ->where('priceRange.min', 1000)
            ->where('priceRange.max', 5000)
            ->where('filters.price_min', 500)
            ->where('filters.price_max', 2000)
        );
});

test('price filter uses the minimum variant amount for variant products', function (): void {
    $product = storefrontPriceFilterVariantProduct($this->currency, [125000, 50000], [
        'name' => 'Jacket',
        'slug' => 'jacket',
    ]);

    $this->get(route('shop.index', ['price_max' => 60000]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('products.data', 1)
            ->where('products.data.0.id', $product->id)
            ->where('priceRange.min', 50000)
            ->where('priceRange.max', 50000)
        );

    $this->get(route('shop.index', ['price_min' => 60000]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('products.data', 0)
        );
});

test('category price bounds ignore products outside the category', function (): void {
    $cameras = Category::factory()->create([
        'name' => 'Cameras',
        'slug' => 'cameras',
        'is_enabled' => true,
        'parent_id' => null,
    ]);
    $audio = Category::factory()->create([
        'name' => 'Audio',
        'slug' => 'audio',
        'is_enabled' => true,
        'parent_id' => null,
    ]);

    $inCategory = storefrontPriceFilterProduct($this->currency, 3000, [
        'name' => 'Body',
        'slug' => 'body',
    ]);
    $inCategory->categories()->attach($cameras);

    $alsoInCategory = storefrontPriceFilterProduct($this->currency, 9000, [
        'name' => 'Lens',
        'slug' => 'lens',
    ]);
    $alsoInCategory->categories()->attach($cameras);

    $elsewhere = storefrontPriceFilterProduct($this->currency, 100, [
        'name' => 'Cable',
        'slug' => 'cable',
    ]);
    $elsewhere->categories()->attach($audio);

    $this->get(route('shop.category', $cameras))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('products.data', 2)
            ->where('priceRange.min', 3000)
            ->where('priceRange.max', 9000)
            ->where('filters.price_min', null)
            ->where('filters.price_max', null)
        );
});

test('category attribute and price filters are combined with AND', function (): void {
    $category = Category::factory()->create([
        'name' => 'Fabrics',
        'slug' => 'fabrics',
        'is_enabled' => true,
        'parent_id' => null,
    ]);

    $material = Attribute::factory()->create([
        'name' => 'Material',
        'slug' => 'material',
        'type' => FieldType::Text,
        'is_enabled' => true,
        'is_filterable' => true,
    ]);

    $cheapCotton = storefrontPriceFilterProduct($this->currency, 1000, [
        'name' => 'Cotton Shirt',
        'slug' => 'cotton-shirt',
        'created_at' => now()->subDay(),
    ]);
    $cheapCotton->categories()->attach($category);
    $cheapCotton->options()->attach($material->id, [
        'attribute_custom_value' => 'Cotton',
    ]);

    $expensiveCotton = storefrontPriceFilterProduct($this->currency, 5000, [
        'name' => 'Premium Cotton',
        'slug' => 'premium-cotton',
    ]);
    $expensiveCotton->categories()->attach($category);
    $expensiveCotton->options()->attach($material->id, [
        'attribute_custom_value' => 'Cotton',
    ]);

    $cheapSilk = storefrontPriceFilterProduct($this->currency, 1200, [
        'name' => 'Silk Shirt',
        'slug' => 'silk-shirt',
    ]);
    $cheapSilk->categories()->attach($category);
    $cheapSilk->options()->attach($material->id, [
        'attribute_custom_value' => 'Silk',
    ]);

    $this->get(route('shop.category', [
        'category' => $category,
        'attrs' => ['material' => ['Cotton']],
        'price_max' => 2000,
    ]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('products.data', 1)
            ->where('products.data.0.id', $cheapCotton->id)
            ->where('filters.attrs.material.0', 'Cotton')
            ->where('filters.price_max', 2000)
        );
});

test('brand page filters products by storefront price range', function (): void {
    $brand = Brand::factory()->create([
        'name' => 'Acme',
        'slug' => 'acme',
        'is_enabled' => true,
    ]);

    $cheap = storefrontPriceFilterProduct($this->currency, 1000, [
        'name' => 'Acme Budget',
        'slug' => 'acme-budget',
        'brand_id' => $brand->id,
        'created_at' => now()->subDay(),
    ]);
    storefrontPriceFilterProduct($this->currency, 5000, [
        'name' => 'Acme Premium',
        'slug' => 'acme-premium',
        'brand_id' => $brand->id,
    ]);

    $this->get(route('shop.brand', [
        'brand' => $brand,
        'price_min' => 500,
        'price_max' => 2000,
    ]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shop/brand')
            ->has('products.data', 1)
            ->where('products.data.0.id', $cheap->id)
            ->where('priceRange.min', 1000)
            ->where('priceRange.max', 5000)
        );
});

test('collection page filters products by storefront price range', function (): void {
    $collection = Collection::factory()->create([
        'name' => 'Summer Edit',
        'slug' => 'summer-edit',
        'type' => CollectionType::Manual,
        'published_at' => now()->subDay(),
    ]);

    $cheap = storefrontPriceFilterProduct($this->currency, 1000, [
        'name' => 'Linen Shirt',
        'slug' => 'linen-shirt',
        'created_at' => now()->subDay(),
    ]);
    $expensive = storefrontPriceFilterProduct($this->currency, 5000, [
        'name' => 'Silk Shirt',
        'slug' => 'silk-shirt',
    ]);
    $collection->products()->attach([$cheap->id, $expensive->id]);

    $this->get(route('shop.collection', [
        'collection' => $collection,
        'price_min' => 500,
        'price_max' => 2000,
    ]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shop/collection')
            ->has('products.data', 1)
            ->where('products.data.0.id', $cheap->id)
            ->where('priceRange.min', 1000)
            ->where('priceRange.max', 5000)
        );
});

test('search page filters products by storefront price range', function (): void {
    $cheap = storefrontPriceFilterProduct($this->currency, 1000, [
        'name' => 'Studio Mic Cheap',
        'slug' => 'studio-mic-cheap',
        'created_at' => now()->subDay(),
    ]);
    storefrontPriceFilterProduct($this->currency, 5000, [
        'name' => 'Studio Mic Pro',
        'slug' => 'studio-mic-pro',
    ]);

    $this->get(route('shop.search', [
        'q' => 'Studio',
        'price_min' => 500,
        'price_max' => 2000,
    ]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shop/search')
            ->has('products.data', 1)
            ->where('products.data.0.id', $cheap->id)
            ->where('priceRange.min', 1000)
            ->where('priceRange.max', 5000)
        );
});

test('price_min greater than price_max is swapped', function (): void {
    $cheap = storefrontPriceFilterProduct($this->currency, 1000, [
        'name' => 'Cheap Lamp',
        'slug' => 'cheap-lamp',
        'created_at' => now()->subDay(),
    ]);
    storefrontPriceFilterProduct($this->currency, 5000, [
        'name' => 'Expensive Lamp',
        'slug' => 'expensive-lamp',
    ]);

    $this->get(route('shop.index', ['price_min' => 2000, 'price_max' => 500]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('products.data', 1)
            ->where('products.data.0.id', $cheap->id)
            ->where('filters.price_min', 500)
            ->where('filters.price_max', 2000)
        );
});

test('invalid price_min is rejected', function (): void {
    $this->get(route('shop.index', ['price_min' => 'abc']))
        ->assertRedirect()
        ->assertSessionHasErrors('price_min');

    $category = Category::factory()->create([
        'name' => 'Lamps',
        'slug' => 'lamps',
        'is_enabled' => true,
        'parent_id' => null,
    ]);

    $this->get(route('shop.category', [
        'category' => $category,
        'price_min' => -1,
    ]))
        ->assertRedirect()
        ->assertSessionHasErrors('price_min');
});
