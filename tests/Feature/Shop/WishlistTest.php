<?php

declare(strict_types=1);

use App\Actions\Wishlist\WishlistManager;
use App\Models\Product;
use App\Models\User;
use App\Models\WishlistItem;
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

test('guests can add and remove products from the session wishlist', function (): void {
    $product = Product::factory()->standard()->create([
        'name' => 'Studio Camera',
        'slug' => 'studio-camera',
    ]);

    $this->post(route('shop.wishlist.store'), [
        'product_id' => $product->id,
    ])->assertRedirect();

    expect(session(WishlistManager::SESSION_KEY))->toBe([$product->id]);

    $this->get(route('shop.wishlist'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('account/wishlist')
            ->has('products.data', 1)
            ->where('products.data.0.id', $product->id)
            ->where('shop.wishlist_count', 1)
            ->where('shop.wishlist_ids.0', $product->id)
        );

    $this->delete(route('shop.wishlist.destroy', $product))->assertRedirect();

    expect(session(WishlistManager::SESSION_KEY))->toBe([]);

    $this->get(route('shop.wishlist'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('account/wishlist')
            ->has('products.data', 0)
            ->where('shop.wishlist_count', 0)
        );
});

test('authenticated users persist wishlist items in the database', function (): void {
    $user = User::factory()->create();
    $product = Product::factory()->standard()->create([
        'name' => 'Desk Lamp',
        'slug' => 'desk-lamp',
    ]);

    $this->actingAs($user)
        ->post(route('shop.wishlist.store'), [
            'product_id' => $product->id,
        ])
        ->assertRedirect();

    $item = WishlistItem::query()
        ->where('user_id', $user->id)
        ->where('product_id', $product->id)
        ->first();

    expect($item)->not->toBeNull();
    $this->assertModelExists($item);

    $this->actingAs($user)
        ->post(route('shop.wishlist.store'), [
            'product_id' => $product->id,
        ])
        ->assertRedirect();

    expect(
        WishlistItem::query()->where('user_id', $user->id)->count(),
    )->toBe(1);

    $this->actingAs($user)
        ->get(route('home'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('shop.wishlist_count', 1)
            ->where('shop.wishlist_ids.0', $product->id)
        );
});

test('unpublished products cannot be added to the wishlist', function (): void {
    $product = Product::factory()->create([
        'name' => 'Hidden Product',
        'slug' => 'hidden-product',
        'is_visible' => false,
    ]);

    $this->post(route('shop.wishlist.store'), [
        'product_id' => $product->id,
    ])->assertNotFound();

    expect(session()->has(WishlistManager::SESSION_KEY))->toBeFalse();
});

test('guest wishlist merges into the account on login', function (): void {
    $user = User::factory()->create();
    $published = Product::factory()->standard()->create([
        'name' => 'Published Light',
        'slug' => 'published-light',
    ]);
    $unpublished = Product::factory()->create([
        'name' => 'Draft Light',
        'slug' => 'draft-light',
        'is_visible' => false,
    ]);

    $this->withSession([
        WishlistManager::SESSION_KEY => [$published->id, $unpublished->id],
    ])->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect();

    $this->assertAuthenticated();
    expect(session()->has(WishlistManager::SESSION_KEY))->toBeFalse();
    expect(
        WishlistItem::query()
            ->where('user_id', $user->id)
            ->where('product_id', $published->id)
            ->exists(),
    )->toBeTrue();
    expect(
        WishlistItem::query()
            ->where('user_id', $user->id)
            ->where('product_id', $unpublished->id)
            ->exists(),
    )->toBeFalse();
});

test('guest wishlist merges into the account on registration', function (): void {
    $product = Product::factory()->standard()->create([
        'name' => 'Travel Mug',
        'slug' => 'travel-mug',
    ]);

    $this->withSession([
        WishlistManager::SESSION_KEY => [$product->id],
    ])->post(route('register.store'), [
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'wishlist@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertRedirect();

    $this->assertAuthenticated();

    $user = auth()->user();

    expect(session()->has(WishlistManager::SESSION_KEY))->toBeFalse();
    expect(
        WishlistItem::query()
            ->where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->exists(),
    )->toBeTrue();
});

test('a user cannot see another users wishlist', function (): void {
    $owner = User::factory()->create();
    $viewer = User::factory()->create();
    $product = Product::factory()->standard()->create([
        'name' => 'Owner Only',
        'slug' => 'owner-only',
    ]);

    WishlistItem::factory()->create([
        'user_id' => $owner->id,
        'product_id' => $product->id,
    ]);

    $this->actingAs($viewer)
        ->get(route('shop.wishlist'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('account/wishlist')
            ->has('products.data', 0)
            ->where('shop.wishlist_count', 0)
        );
});
