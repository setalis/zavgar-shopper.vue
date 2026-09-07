<?php

declare(strict_types=1);

use App\Actions\FlushStorefrontCategoryCache;
use App\Actions\FlushStorefrontMenuCache;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Collection;
use App\Models\MenuItem;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Shopper\Core\Enum\CollectionType;

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

test('home page shares enabled menu items with nested children', function (): void {
    $root = MenuItem::factory()->create([
        'title' => 'Catalog',
        'url' => '/shop',
        'position' => 1,
    ]);

    $child = MenuItem::factory()->childOf($root)->create([
        'title' => 'Laptops',
        'url' => '/categories/laptops',
        'position' => 1,
    ]);

    MenuItem::factory()->childOf($child)->create([
        'title' => 'Gaming',
        'url' => '/categories/gaming',
        'position' => 1,
    ]);

    MenuItem::factory()->create([
        'title' => 'Contact',
        'url' => '/contact',
        'position' => 2,
    ]);

    MenuItem::factory()->disabled()->create([
        'title' => 'Hidden',
        'url' => '/hidden',
        'position' => 3,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('shop.nav_menu', 2)
            ->where('shop.nav_menu.0.id', $root->id)
            ->where('shop.nav_menu.0.title', 'Catalog')
            ->where('shop.nav_menu.0.href', '/shop')
            ->has('shop.nav_menu.0.children', 1)
            ->where('shop.nav_menu.0.children.0.title', 'Laptops')
            ->has('shop.nav_menu.0.children.0.children', 1)
            ->where('shop.nav_menu.0.children.0.children.0.title', 'Gaming')
            ->where('shop.nav_menu.0.children.0.children.0.href', '/categories/gaming')
            ->has('shop.nav_menu.0.children.0.children.0.children', 0)
            ->where('shop.nav_menu.1.title', 'Contact')
            ->has('shop.nav_menu.1.children', 0)
        );
});

test('storefront menu hides items with unavailable targets', function (): void {
    $disabledCategory = Category::factory()->create([
        'name' => 'Hidden Oils',
        'slug' => 'hidden-oils',
        'is_enabled' => false,
    ]);

    $unpublishedProduct = Product::factory()->create([
        'name' => 'Draft Oil',
        'slug' => 'draft-oil',
        'is_visible' => false,
        'published_at' => now(),
    ]);

    $unpublishedCollection = Collection::factory()->create([
        'name' => 'Draft Kit',
        'slug' => 'draft-kit',
        'type' => CollectionType::Manual,
        'published_at' => now()->addDay(),
    ]);

    $disabledBrand = Brand::factory()->create([
        'name' => 'Hidden Brand',
        'slug' => 'hidden-brand',
        'is_enabled' => false,
    ]);

    MenuItem::factory()->forCategory($disabledCategory)->create([
        'title' => 'Disabled category',
        'position' => 1,
    ]);

    MenuItem::factory()->forProduct($unpublishedProduct)->create([
        'title' => 'Draft product',
        'position' => 2,
    ]);

    MenuItem::factory()->forCollection($unpublishedCollection)->create([
        'title' => 'Draft collection',
        'position' => 3,
    ]);

    MenuItem::factory()->forBrand($disabledBrand)->create([
        'title' => 'Disabled brand',
        'position' => 4,
    ]);

    MenuItem::factory()->create([
        'title' => 'Contact',
        'url' => '/contact',
        'position' => 5,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('shop.nav_menu', 1)
            ->where('shop.nav_menu.0.title', 'Contact')
            ->where('shop.nav_menu.0.href', '/contact')
        );
});

test('creating a menu item flushes the storefront menu cache', function (): void {
    MenuItem::factory()->create([
        'title' => 'Existing',
        'url' => '/shop',
        'position' => 1,
    ]);

    $this->get(route('home'))->assertOk();

    $cacheKey = FlushStorefrontMenuCache::navKey(app()->getLocale());

    expect(Cache::has($cacheKey))->toBeTrue();

    $created = MenuItem::factory()->create([
        'title' => 'Fresh item',
        'url' => '/contact',
        'position' => 2,
    ]);

    expect(Cache::has($cacheKey))->toBeFalse();

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('shop.nav_menu', 2)
            ->where('shop.nav_menu.1.id', $created->id)
            ->where('shop.nav_menu.1.title', 'Fresh item')
        );
});

test('home page shares an empty menu when no items are enabled', function (): void {
    MenuItem::factory()->disabled()->create([
        'title' => 'Hidden',
        'url' => '/hidden',
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('shop.nav_menu', 0)
        );
});
