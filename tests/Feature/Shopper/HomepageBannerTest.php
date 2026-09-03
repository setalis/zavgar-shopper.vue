<?php

declare(strict_types=1);

use App\Enums\HomepageBannerBackgroundType;
use App\Enums\HomepageBannerColor;
use App\Enums\HomepageBannerCtaType;
use App\Enums\HomepageBannerShade;
use App\Enums\HomepageBannerSize;
use App\Livewire\Shopper\Pages\HomepageBanners\Edit;
use App\Livewire\Shopper\Pages\HomepageBanners\Index;
use App\Models\Category;
use App\Models\HomepageBanner;
use App\Models\User;
use App\Sidebar\HomepageBannersSidebar;
use App\Support\TailwindTint;
use Database\Seeders\HomepageBannerPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Shopper\Database\Seeders\AuthTableSeeder;
use Shopper\Models\Permission;
use Shopper\Sidebar\Contracts\Builder\Menu;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(AuthTableSeeder::class);
    $this->seed(HomepageBannerPermissionsSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole(config('shopper.admin.roles.admin'));
});

test('guests cannot browse homepage banners', function (): void {
    $this->get(route('shopper.banners.index'))->assertRedirect();
});

test('homepage banners sidebar does not throw when the permission is missing', function (): void {
    Permission::query()
        ->where('name', 'like', '%homepage_banners')
        ->delete();

    app()[PermissionRegistrar::class]->forgetCachedPermissions();

    $this->actingAs($this->admin);

    $menu = app(Menu::class);

    expect(fn () => (new HomepageBannersSidebar)->extendWith($menu))
        ->not->toThrow(PermissionDoesNotExist::class);
});

test('users without permission cannot browse homepage banners', function (): void {
    $user = User::factory()->create();

    expect($user->can('browse_homepage_banners'))->toBeFalse();

    $this->actingAs($user)
        ->get(route('shopper.banners.index'))
        ->assertRedirect();
});

test('admins can browse homepage banners', function (): void {
    HomepageBanner::factory()->create([
        'title' => 'Desk setup',
    ]);

    Livewire::actingAs($this->admin)
        ->test(Index::class)
        ->assertSuccessful()
        ->assertSee('Desk setup');
});

test('admins can create a homepage banner with a url target', function (): void {
    Livewire::actingAs($this->admin)
        ->test(Edit::class)
        ->set('data.title', 'Hero banner')
        ->set('data.eyebrow', 'New')
        ->set('data.description', 'Shop the new range')
        ->set('data.button_text', 'Shop now')
        ->set('data.size', HomepageBannerSize::Large->value)
        ->set('data.background_type', HomepageBannerBackgroundType::Gradient->value)
        ->set('data.gradient', TailwindTint::of(HomepageBannerColor::Blue)->value())
        ->set('data.cta_type', HomepageBannerCtaType::Url->value)
        ->set('data.cta_url', '/shop')
        ->set('data.is_enabled', true)
        ->call('store')
        ->assertHasNoErrors();

    $banner = HomepageBanner::query()->first();

    expect($banner)->not->toBeNull()
        ->and($banner->title)->toBe('Hero banner')
        ->and($banner->cta_type)->toBe(HomepageBannerCtaType::Url)
        ->and($banner->cta_url)->toBe('/shop')
        ->and($banner->href())->toBe('/shop');
});

test('category is required when the button target is a category', function (): void {
    Livewire::actingAs($this->admin)
        ->test(Edit::class)
        ->set('data.title', 'Category banner')
        ->set('data.size', HomepageBannerSize::Medium->value)
        ->set('data.background_type', HomepageBannerBackgroundType::Gradient->value)
        ->set('data.gradient', TailwindTint::of(HomepageBannerColor::Teal)->value())
        ->set('data.cta_type', HomepageBannerCtaType::Category->value)
        ->set('data.category_id', null)
        ->call('store')
        ->assertHasErrors(['data.category_id']);
});

test('product is required when the button target is a product', function (): void {
    Livewire::actingAs($this->admin)
        ->test(Edit::class)
        ->set('data.title', 'Product banner')
        ->set('data.size', HomepageBannerSize::Medium->value)
        ->set('data.background_type', HomepageBannerBackgroundType::Gradient->value)
        ->set('data.gradient', TailwindTint::of(HomepageBannerColor::Orange)->value())
        ->set('data.cta_type', HomepageBannerCtaType::Product->value)
        ->set('data.product_id', null)
        ->call('store')
        ->assertHasErrors(['data.product_id']);
});

test('collection is required when the button target is a collection', function (): void {
    Livewire::actingAs($this->admin)
        ->test(Edit::class)
        ->set('data.title', 'Collection banner')
        ->set('data.size', HomepageBannerSize::Medium->value)
        ->set('data.background_type', HomepageBannerBackgroundType::Gradient->value)
        ->set('data.gradient', TailwindTint::of(HomepageBannerColor::Green)->value())
        ->set('data.cta_type', HomepageBannerCtaType::Collection->value)
        ->set('data.collection_id', null)
        ->call('store')
        ->assertHasErrors(['data.collection_id']);
});

test('admins can create a homepage banner targeting a category', function (): void {
    $category = Category::factory()->create([
        'name' => 'Filters',
        'slug' => 'filters',
        'is_enabled' => true,
    ]);

    Livewire::actingAs($this->admin)
        ->test(Edit::class)
        ->set('data.title', 'Filters banner')
        ->set('data.button_text', 'Browse')
        ->set('data.size', HomepageBannerSize::Small->value)
        ->set('data.background_type', HomepageBannerBackgroundType::Gradient->value)
        ->set('data.gradient', TailwindTint::of(HomepageBannerColor::Purple)->value())
        ->set('data.cta_type', HomepageBannerCtaType::Category->value)
        ->set('data.category_id', $category->id)
        ->set('data.is_enabled', true)
        ->call('store')
        ->assertHasNoErrors();

    $banner = HomepageBanner::query()->first();

    expect($banner)->not->toBeNull()
        ->and($banner->category_id)->toBe($category->id)
        ->and($banner->cta_url)->toBeNull()
        ->and($banner->href())->toBe(route('shop.category', $category));
});

test('admins can update a homepage banner', function (): void {
    $banner = HomepageBanner::factory()->create([
        'title' => 'Old title',
        'button_text' => 'Old',
        'cta_url' => '/shop',
    ]);

    Livewire::actingAs($this->admin)
        ->test(Edit::class, ['banner' => $banner])
        ->set('data.title', 'Updated title')
        ->set('data.button_text', 'Go')
        ->set('data.cta_url', '/categories')
        ->call('store')
        ->assertHasNoErrors();

    $banner->refresh();

    expect($banner->title)->toBe('Updated title')
        ->and($banner->button_text)->toBe('Go')
        ->and($banner->cta_url)->toBe('/categories');
});

test('admins can save a gradient tint on an image banner', function (): void {
    $banner = HomepageBanner::factory()->create([
        'title' => 'Photo banner',
        'background_type' => HomepageBannerBackgroundType::Image,
        'gradient' => TailwindTint::of(HomepageBannerColor::Blue),
        'cta_url' => '/shop',
    ]);

    $banner
        ->addMedia(UploadedFile::fake()->image('hero.jpg'))
        ->toMediaCollection(HomepageBanner::MEDIA_BACKGROUND);

    Livewire::actingAs($this->admin)
        ->test(Edit::class, ['banner' => $banner])
        ->set('data.gradient', '')
        ->set('data.overlay_gradient', TailwindTint::of(HomepageBannerColor::Orange)->value())
        ->set('data.background_type', HomepageBannerBackgroundType::Image->value)
        ->call('store')
        ->assertHasNoErrors();

    $banner->refresh();

    expect($banner->background_type)->toBe(HomepageBannerBackgroundType::Image)
        ->and($banner->gradient)->toBeNull()
        ->and($banner->overlay_gradient)->toEqual(TailwindTint::of(HomepageBannerColor::Orange));
});

test('admins can save a banner without fill or overlay gradients', function (): void {
    Livewire::actingAs($this->admin)
        ->test(Edit::class)
        ->set('data.title', 'Plain banner')
        ->set('data.size', HomepageBannerSize::Medium->value)
        ->set('data.background_type', HomepageBannerBackgroundType::Gradient->value)
        ->set('data.gradient', '')
        ->set('data.overlay_gradient', '')
        ->set('data.cta_type', HomepageBannerCtaType::Url->value)
        ->set('data.cta_url', '/shop')
        ->set('data.is_enabled', true)
        ->call('store')
        ->assertHasNoErrors();

    $banner = HomepageBanner::query()->first();

    expect($banner)->not->toBeNull()
        ->and($banner->gradient)->toBeNull()
        ->and($banner->overlay_gradient)->toBeNull();
});

test('admins can save independent fill and overlay gradients', function (): void {
    $banner = HomepageBanner::factory()->create([
        'title' => 'Split tints',
        'background_type' => HomepageBannerBackgroundType::Image,
        'gradient' => TailwindTint::of(HomepageBannerColor::Blue),
        'overlay_gradient' => null,
        'cta_url' => '/shop',
    ]);

    $banner
        ->addMedia(UploadedFile::fake()->image('hero.jpg'))
        ->toMediaCollection(HomepageBanner::MEDIA_BACKGROUND);

    Livewire::actingAs($this->admin)
        ->test(Edit::class, ['banner' => $banner])
        ->set('data.gradient', TailwindTint::of(HomepageBannerColor::Purple, HomepageBannerShade::Shade700)->value())
        ->set('data.overlay_gradient', TailwindTint::of(HomepageBannerColor::Teal, HomepageBannerShade::Shade50)->value())
        ->call('store')
        ->assertHasNoErrors();

    $banner->refresh();

    expect($banner->gradient)->toEqual(TailwindTint::of(HomepageBannerColor::Purple, HomepageBannerShade::Shade700))
        ->and($banner->overlay_gradient)->toEqual(TailwindTint::of(HomepageBannerColor::Teal, HomepageBannerShade::Shade50));
});

test('the banner form hydrates a stored tailwind tint', function (): void {
    $tint = TailwindTint::of(HomepageBannerColor::Sky, HomepageBannerShade::Shade700);
    $banner = HomepageBanner::factory()->create([
        'gradient' => $tint,
        'cta_url' => '/shop',
    ]);

    Livewire::actingAs($this->admin)
        ->test(Edit::class, ['banner' => $banner])
        ->assertSet('data.gradient', $tint->value());
});

test('admins can delete a homepage banner from the index', function (): void {
    $banner = HomepageBanner::factory()->create([
        'title' => 'Remove me',
    ]);

    Livewire::actingAs($this->admin)
        ->test(Index::class)
        ->callTableAction('delete', $banner)
        ->assertHasNoErrors();

    expect(HomepageBanner::query()->find($banner->id))->toBeNull();
});
