<?php

declare(strict_types=1);

use App\Actions\FlushStorefrontCategoryCache;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Cache::flush();
});

test('home page shares enabled root categories with children for storefront navigation', function (): void {
    $electronics = Category::factory()->create([
        'name' => 'Electronics',
        'slug' => 'electronics',
        'is_enabled' => true,
        'parent_id' => null,
        'position' => 1,
    ]);

    $apparel = Category::factory()->create([
        'name' => 'Apparel',
        'slug' => 'apparel',
        'is_enabled' => true,
        'parent_id' => null,
        'position' => 2,
    ]);

    $phones = Category::factory()->create([
        'name' => 'Phones',
        'slug' => 'phones',
        'is_enabled' => true,
        'parent_id' => $electronics->id,
        'position' => 2,
    ]);

    $laptops = Category::factory()->create([
        'name' => 'Laptops',
        'slug' => 'laptops',
        'is_enabled' => true,
        'parent_id' => $electronics->id,
        'position' => 1,
    ]);

    Category::factory()->create([
        'name' => 'Disabled Child',
        'slug' => 'disabled-child',
        'is_enabled' => false,
        'parent_id' => $electronics->id,
        'position' => 0,
    ]);

    Category::factory()->create([
        'name' => 'Disabled',
        'slug' => 'disabled',
        'is_enabled' => false,
        'parent_id' => null,
        'position' => 0,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('shop.nav_categories', 2)
            ->where('shop.nav_categories.0.id', $electronics->id)
            ->where('shop.nav_categories.0.name', 'Electronics')
            ->where('shop.nav_categories.0.slug', 'electronics')
            ->where('shop.nav_categories.0.thumbnail', null)
            ->has('shop.nav_categories.0.children', 2)
            ->where('shop.nav_categories.0.children.0.id', $laptops->id)
            ->where('shop.nav_categories.0.children.0.name', 'Laptops')
            ->where('shop.nav_categories.0.children.1.id', $phones->id)
            ->where('shop.nav_categories.0.children.1.name', 'Phones')
            ->where('shop.nav_categories.1.id', $apparel->id)
            ->has('shop.nav_categories.1.children', 0)
        );
});

test('storefront navigation includes all enabled root categories', function (): void {
    foreach (range(1, 6) as $position) {
        Category::factory()->create([
            'name' => "Category {$position}",
            'slug' => "category-{$position}",
            'is_enabled' => true,
            'parent_id' => null,
            'position' => $position,
        ]);
    }

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('shop.nav_categories', 6)
            ->where('shop.nav_categories.0.slug', 'category-1')
            ->where('shop.nav_categories.5.slug', 'category-6')
        );
});

test('creating a category flushes the storefront navigation cache', function (): void {
    Category::factory()->create([
        'name' => 'Existing',
        'slug' => 'existing',
        'is_enabled' => true,
        'parent_id' => null,
        'position' => 1,
    ]);

    $this->get(route('home'))->assertOk();

    $cacheKey = FlushStorefrontCategoryCache::navKey(app()->getLocale());

    expect(Cache::has($cacheKey))->toBeTrue();

    $created = Category::factory()->create([
        'name' => 'Fresh Category',
        'slug' => 'fresh-category',
        'is_enabled' => true,
        'parent_id' => null,
        'position' => 2,
    ]);

    expect(Cache::has($cacheKey))->toBeFalse();

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('shop.nav_categories', 2)
            ->where('shop.nav_categories.1.id', $created->id)
            ->where('shop.nav_categories.1.name', 'Fresh Category')
        );
});
