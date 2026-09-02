<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Product;
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

test('home page counts unique products across the full category branch', function (): void {
    $electronics = Category::factory()->create([
        'name' => 'Electronics',
        'slug' => 'electronics',
        'is_enabled' => true,
        'parent_id' => null,
        'position' => 1,
    ]);

    $phones = Category::factory()->create([
        'name' => 'Phones',
        'slug' => 'phones',
        'is_enabled' => true,
        'parent_id' => $electronics->id,
        'position' => 1,
    ]);

    $android = Category::factory()->create([
        'name' => 'Android',
        'slug' => 'android',
        'is_enabled' => true,
        'parent_id' => $phones->id,
        'position' => 1,
    ]);

    $apparel = Category::factory()->create([
        'name' => 'Apparel',
        'slug' => 'apparel',
        'is_enabled' => true,
        'parent_id' => null,
        'position' => 2,
    ]);

    $lighting = Category::factory()->create([
        'name' => 'Lighting',
        'slug' => 'lighting',
        'is_enabled' => true,
        'parent_id' => null,
        'position' => 3,
    ]);

    $inParent = Product::factory()->standard()->create([
        'name' => 'USB Hub',
        'slug' => 'usb-hub',
    ]);
    $inChild = Product::factory()->standard()->create([
        'name' => 'Feature Phone',
        'slug' => 'feature-phone',
    ]);
    $inGrandchild = Product::factory()->standard()->create([
        'name' => 'Pixel Phone',
        'slug' => 'pixel-phone',
    ]);
    $inApparel = Product::factory()->standard()->create([
        'name' => 'Denim Jacket',
        'slug' => 'denim-jacket',
    ]);

    $inParent->categories()->attach($electronics);
    $inParent->categories()->attach($phones);
    $inChild->categories()->attach($phones);
    $inGrandchild->categories()->attach($android);
    $inApparel->categories()->attach($apparel);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shop/home')
            ->has('categories', 3)
            ->where('categories.0.id', $electronics->id)
            ->where('categories.0.products_count', 3)
            ->where('categories.1.id', $apparel->id)
            ->where('categories.1.products_count', 1)
            ->where('categories.2.id', $lighting->id)
            ->where('categories.2.products_count', 0)
        );
});

test('categories index counts unique products across the full category branch', function (): void {
    $oils = Category::factory()->create([
        'name' => 'Oils',
        'slug' => 'oils',
        'is_enabled' => true,
        'parent_id' => null,
        'position' => 1,
    ]);

    $motorOil = Category::factory()->create([
        'name' => 'Motor oil',
        'slug' => 'motor-oil',
        'is_enabled' => true,
        'parent_id' => $oils->id,
        'position' => 1,
    ]);

    $inParent = Product::factory()->standard()->create([
        'name' => 'Gear Oil',
        'slug' => 'gear-oil',
    ]);
    $inChild = Product::factory()->standard()->create([
        'name' => '5W-30',
        'slug' => '5w-30',
    ]);

    $inParent->categories()->attach($oils);
    $inChild->categories()->attach($motorOil);

    $this->get(route('shop.categories'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shop/categories')
            ->has('categories', 1)
            ->where('categories.0.id', $oils->id)
            ->where('categories.0.products_count', 2)
        );
});

test('category page child tiles count products in each child branch', function (): void {
    $electronics = Category::factory()->create([
        'name' => 'Electronics',
        'slug' => 'electronics',
        'is_enabled' => true,
        'parent_id' => null,
        'position' => 1,
    ]);

    $phones = Category::factory()->create([
        'name' => 'Phones',
        'slug' => 'phones',
        'is_enabled' => true,
        'parent_id' => $electronics->id,
        'position' => 1,
    ]);

    $android = Category::factory()->create([
        'name' => 'Android',
        'slug' => 'android',
        'is_enabled' => true,
        'parent_id' => $phones->id,
        'position' => 1,
    ]);

    $inPhones = Product::factory()->standard()->create([
        'name' => 'Feature Phone',
        'slug' => 'feature-phone',
    ]);
    $inAndroid = Product::factory()->standard()->create([
        'name' => 'Pixel Phone',
        'slug' => 'pixel-phone',
    ]);

    $inPhones->categories()->attach($phones);
    $inAndroid->categories()->attach($android);

    $this->get(route('shop.category', $electronics))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shop/category')
            ->has('children', 1)
            ->where('children.0.id', $phones->id)
            ->where('children.0.products_count', 2)
        );
});
