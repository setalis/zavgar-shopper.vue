<?php

declare(strict_types=1);

use App\Actions\FlushStorefrontMenuCache;
use App\Enums\MenuItemTargetType;
use App\Livewire\Shopper\Pages\MenuItems\Edit;
use App\Livewire\Shopper\Pages\MenuItems\Index;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\User;
use App\Sidebar\HomepageBannersSidebar;
use Database\Seeders\MenuItemPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Shopper\Database\Seeders\AuthTableSeeder;
use Shopper\Models\Permission;
use Shopper\Sidebar\Contracts\Builder\Menu;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(AuthTableSeeder::class);
    $this->seed(MenuItemPermissionsSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole(config('shopper.admin.roles.admin'));
});

test('guests cannot browse menu items', function (): void {
    $this->get(route('shopper.menu.index'))->assertRedirect();
});

test('menu sidebar does not throw when the permission is missing', function (): void {
    Permission::query()
        ->where('name', 'like', '%menu_items')
        ->delete();

    app()[PermissionRegistrar::class]->forgetCachedPermissions();

    $this->actingAs($this->admin);

    $menu = app(Menu::class);

    expect(fn () => (new HomepageBannersSidebar)->extendWith($menu))
        ->not->toThrow(PermissionDoesNotExist::class);
});

test('users without permission cannot browse menu items', function (): void {
    $user = User::factory()->create();

    expect($user->can('browse_menu_items'))->toBeFalse();

    $this->actingAs($user)
        ->get(route('shopper.menu.index'))
        ->assertRedirect();
});

test('admins can browse root menu items', function (): void {
    $root = MenuItem::factory()->create([
        'title' => 'Shop all',
    ]);

    MenuItem::factory()->childOf($root)->create([
        'title' => 'Hidden child',
    ]);

    Livewire::actingAs($this->admin)
        ->test(Index::class)
        ->assertSuccessful()
        ->assertSee('Shop all')
        ->assertDontSee('Hidden child');
});

test('admins can browse child menu items for a parent', function (): void {
    $root = MenuItem::factory()->create([
        'title' => 'Catalog',
    ]);

    MenuItem::factory()->childOf($root)->create([
        'title' => 'Laptops',
    ]);

    Livewire::actingAs($this->admin)
        ->test(Index::class, ['parent' => $root->id])
        ->assertSuccessful()
        ->assertSee('Laptops');
});

test('admins can create a menu item with a url target', function (): void {
    Livewire::actingAs($this->admin)
        ->test(Edit::class)
        ->set('data.title', 'Contact')
        ->set('data.target_type', MenuItemTargetType::Url->value)
        ->set('data.url', '/contact')
        ->set('data.is_enabled', true)
        ->call('store')
        ->assertHasNoErrors();

    $item = MenuItem::query()->first();

    expect($item)->not->toBeNull()
        ->and($item->title)->toBe('Contact')
        ->and($item->target_type)->toBe(MenuItemTargetType::Url)
        ->and($item->url)->toBe('/contact')
        ->and($item->href())->toBe('/contact')
        ->and($item->parent_id)->toBeNull();
});

test('admins can create a child menu item', function (): void {
    $parent = MenuItem::factory()->create([
        'title' => 'Catalog',
    ]);

    Livewire::actingAs($this->admin)
        ->test(Edit::class, ['parent' => $parent->id])
        ->set('data.title', 'Laptops')
        ->set('data.target_type', MenuItemTargetType::Url->value)
        ->set('data.url', '/shop')
        ->set('data.is_enabled', true)
        ->call('store')
        ->assertHasNoErrors();

    $item = MenuItem::query()->where('parent_id', $parent->id)->first();

    expect($item)->not->toBeNull()
        ->and($item->title)->toBe('Laptops')
        ->and($item->parent_id)->toBe($parent->id);
});

test('url is required when the target is a url', function (): void {
    Livewire::actingAs($this->admin)
        ->test(Edit::class)
        ->set('data.title', 'Broken')
        ->set('data.target_type', MenuItemTargetType::Url->value)
        ->set('data.url', null)
        ->call('store')
        ->assertHasErrors(['data.url']);
});

test('category is required when the target is a category', function (): void {
    Livewire::actingAs($this->admin)
        ->test(Edit::class)
        ->set('data.title', 'Category item')
        ->set('data.target_type', MenuItemTargetType::Category->value)
        ->set('data.category_id', null)
        ->call('store')
        ->assertHasErrors(['data.category_id']);
});

test('admins can create a menu item targeting a category', function (): void {
    $category = Category::factory()->create([
        'name' => 'Filters',
        'slug' => 'filters',
        'is_enabled' => true,
    ]);

    Livewire::actingAs($this->admin)
        ->test(Edit::class)
        ->set('data.title', 'Filters')
        ->set('data.target_type', MenuItemTargetType::Category->value)
        ->set('data.category_id', $category->id)
        ->set('data.is_enabled', true)
        ->call('store')
        ->assertHasNoErrors();

    $item = MenuItem::query()->first();

    expect($item)->not->toBeNull()
        ->and($item->category_id)->toBe($category->id)
        ->and($item->url)->toBeNull()
        ->and($item->href())->toBe(route('shop.category', $category));
});

test('admins can update a menu item', function (): void {
    $item = MenuItem::factory()->create([
        'title' => 'Old title',
        'url' => '/shop',
    ]);

    Livewire::actingAs($this->admin)
        ->test(Edit::class, ['menuItem' => $item])
        ->set('data.title', 'Updated title')
        ->set('data.url', '/contact')
        ->call('store')
        ->assertHasNoErrors();

    $item->refresh();

    expect($item->title)->toBe('Updated title')
        ->and($item->url)->toBe('/contact');
});

test('admins can delete a menu item from the index', function (): void {
    $item = MenuItem::factory()->create([
        'title' => 'Remove me',
    ]);

    Livewire::actingAs($this->admin)
        ->test(Index::class)
        ->callTableAction('delete', $item)
        ->assertHasNoErrors();

    expect(MenuItem::query()->find($item->id))->toBeNull();
});

test('deleting a parent menu item deletes its children', function (): void {
    $parent = MenuItem::factory()->create([
        'title' => 'Parent',
    ]);

    $child = MenuItem::factory()->childOf($parent)->create([
        'title' => 'Child',
    ]);

    $parent->delete();

    expect(MenuItem::query()->find($child->id))->toBeNull();
});

test('grandchild menu items cannot receive children', function (): void {
    $root = MenuItem::factory()->create();
    $child = MenuItem::factory()->childOf($root)->create();
    $grandchild = MenuItem::factory()->childOf($child)->create();

    expect($grandchild->canHaveChildren())->toBeFalse();

    $this->withoutMiddleware()
        ->actingAs($this->admin)
        ->get(route('shopper.menu.create', ['parent' => $grandchild->id]))
        ->assertNotFound();
});

test('reordering menu items updates the storefront menu order', function (): void {
    $first = MenuItem::factory()->create([
        'title' => 'First',
        'url' => '/first',
        'position' => 1,
    ]);

    $second = MenuItem::factory()->create([
        'title' => 'Second',
        'url' => '/second',
        'position' => 2,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('shop.nav_menu.0.id', $first->id)
            ->where('shop.nav_menu.1.id', $second->id)
        );

    expect(Cache::has(FlushStorefrontMenuCache::navKey(app()->getLocale())))->toBeTrue();

    Livewire::actingAs($this->admin)
        ->test(Index::class)
        ->call('reorderTable', [$second->id, $first->id])
        ->assertHasNoErrors();

    expect($first->fresh()->position)->toBe(2)
        ->and($second->fresh()->position)->toBe(1)
        ->and(Cache::has(FlushStorefrontMenuCache::navKey(app()->getLocale())))->toBeFalse();

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('shop.nav_menu.0.id', $second->id)
            ->where('shop.nav_menu.1.id', $first->id)
        );
});
