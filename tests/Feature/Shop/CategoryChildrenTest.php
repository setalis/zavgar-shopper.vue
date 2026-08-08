<?php

declare(strict_types=1);

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('category page includes enabled child categories ordered by position', function (): void {
    $parent = Category::factory()->create([
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
        'parent_id' => $parent->id,
        'position' => 2,
    ]);

    $laptops = Category::factory()->create([
        'name' => 'Laptops',
        'slug' => 'laptops',
        'is_enabled' => true,
        'parent_id' => $parent->id,
        'position' => 1,
    ]);

    Category::factory()->create([
        'name' => 'Disabled Child',
        'slug' => 'disabled-child',
        'is_enabled' => false,
        'parent_id' => $parent->id,
        'position' => 0,
    ]);

    Category::factory()->create([
        'name' => 'Other Root',
        'slug' => 'other-root',
        'is_enabled' => true,
        'parent_id' => null,
        'position' => 1,
    ]);

    $this->get(route('shop.category', $parent))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shop/category')
            ->has('children', 2)
            ->where('children.0.id', $laptops->id)
            ->where('children.0.name', 'Laptops')
            ->where('children.1.id', $phones->id)
            ->where('children.1.name', 'Phones')
            ->missing('children.2')
        );
});

test('category page returns an empty children list when there are no subcategories', function (): void {
    $category = Category::factory()->create([
        'name' => 'Standalone',
        'slug' => 'standalone',
        'is_enabled' => true,
        'parent_id' => null,
    ]);

    $this->get(route('shop.category', $category))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shop/category')
            ->has('children', 0)
        );
});

test('shop index includes child categories when filtering by a parent category', function (): void {
    $parent = Category::factory()->create([
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
        'parent_id' => $parent->id,
        'position' => 1,
    ]);

    Category::factory()->create([
        'name' => 'Disabled Child',
        'slug' => 'disabled-child',
        'is_enabled' => false,
        'parent_id' => $parent->id,
        'position' => 0,
    ]);

    $this->get(route('shop.index', ['category' => $parent->id]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shop/index')
            ->where('filters.category', $parent->id)
            ->has('children', 1)
            ->where('children.0.id', $motorOil->id)
            ->where('children.0.name', 'Motor oil')
            ->has('categories.0.children', 1)
            ->where('categories.0.children.0.id', $motorOil->id)
        );
});

test('shop index returns empty children when no category filter is set', function (): void {
    $parent = Category::factory()->create([
        'name' => 'Oils',
        'slug' => 'oils',
        'is_enabled' => true,
        'parent_id' => null,
    ]);

    Category::factory()->create([
        'name' => 'Motor oil',
        'slug' => 'motor-oil',
        'is_enabled' => true,
        'parent_id' => $parent->id,
    ]);

    $this->get(route('shop.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shop/index')
            ->where('filters.category', null)
            ->has('children', 0)
        );
});
