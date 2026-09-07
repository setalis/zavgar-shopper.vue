<?php

declare(strict_types=1);

use App\Models\Brand;
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

test('brands index lists enabled brands and hides disabled ones', function (): void {
    $acme = Brand::factory()->create([
        'name' => 'Acme',
        'slug' => 'acme',
        'is_enabled' => true,
    ]);

    Brand::factory()->create([
        'name' => 'Hidden',
        'slug' => 'hidden',
        'is_enabled' => false,
    ]);

    $this->get(route('shop.brands'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shop/brands')
            ->has('brands.data', 1)
            ->where('brands.data.0.id', $acme->id)
            ->where('brands.data.0.name', 'Acme')
            ->where('brands.data.0.products_count', 0)
            ->where('filters.q', '')
            ->where('filters.letter', null)
            ->where('filters.sort', 'name')
            ->where('filters.with_products', false)
            ->where('availableLetters', ['A'])
        );
});

test('brands index can search by name', function (): void {
    Brand::factory()->create([
        'name' => 'Acme Tools',
        'slug' => 'acme-tools',
        'is_enabled' => true,
    ]);

    Brand::factory()->create([
        'name' => 'Bosch',
        'slug' => 'bosch',
        'is_enabled' => true,
    ]);

    $this->get(route('shop.brands', ['q' => 'acme']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shop/brands')
            ->has('brands.data', 1)
            ->where('brands.data.0.slug', 'acme-tools')
            ->where('filters.q', 'acme')
            ->where('availableLetters', ['A'])
        );
});

test('brands index can filter by letter', function (): void {
    Brand::factory()->create([
        'name' => 'Acme',
        'slug' => 'acme',
        'is_enabled' => true,
    ]);

    Brand::factory()->create([
        'name' => 'Bosch',
        'slug' => 'bosch',
        'is_enabled' => true,
    ]);

    $this->get(route('shop.brands', ['letter' => 'B']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('brands.data', 1)
            ->where('brands.data.0.slug', 'bosch')
            ->where('filters.letter', 'B')
            ->where('availableLetters', ['A', 'B'])
        );
});

test('brands index can hide brands without published products', function (): void {
    $withProducts = Brand::factory()->create([
        'name' => 'Acme',
        'slug' => 'acme',
        'is_enabled' => true,
    ]);

    Brand::factory()->create([
        'name' => 'Empty Brand',
        'slug' => 'empty-brand',
        'is_enabled' => true,
    ]);

    Product::factory()->standard()->create([
        'name' => 'Acme Widget',
        'slug' => 'acme-widget',
        'brand_id' => $withProducts->id,
    ]);

    Product::factory()->create([
        'name' => 'Draft Widget',
        'slug' => 'draft-widget',
        'brand_id' => $withProducts->id,
        'is_visible' => false,
        'published_at' => now(),
    ]);

    $this->get(route('shop.brands', ['with_products' => 1]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('brands.data', 1)
            ->where('brands.data.0.slug', 'acme')
            ->where('brands.data.0.products_count', 1)
            ->where('filters.with_products', true)
        );
});

test('brands index can sort by published product count', function (): void {
    $few = Brand::factory()->create([
        'name' => 'Acme',
        'slug' => 'acme',
        'is_enabled' => true,
    ]);

    $many = Brand::factory()->create([
        'name' => 'Bosch',
        'slug' => 'bosch',
        'is_enabled' => true,
    ]);

    Product::factory()->standard()->create([
        'name' => 'Acme Widget',
        'slug' => 'acme-widget',
        'brand_id' => $few->id,
    ]);

    Product::factory()->standard()->count(2)->create([
        'brand_id' => $many->id,
    ]);

    $this->get(route('shop.brands', ['sort' => 'products']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('brands.data', 2)
            ->where('brands.data.0.id', $many->id)
            ->where('brands.data.1.id', $few->id)
            ->where('filters.sort', 'products')
        );
});
