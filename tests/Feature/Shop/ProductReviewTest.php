<?php

declare(strict_types=1);

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Shopper\Core\Models\Currency;
use Shopper\Core\Models\Review;
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

function createPublishedProduct(array $attributes = []): Product
{
    return Product::factory()->standard()->create(array_merge([
        'name' => 'Reviewable Product',
        'slug' => 'reviewable-product',
    ], $attributes));
}

function createProductReview(Product $product, User $user, array $attributes = []): Review
{
    return Review::query()->create(array_merge([
        'rating' => 5,
        'title' => 'Great product',
        'content' => 'Really enjoyed using this.',
        'approved' => false,
        'is_recommended' => true,
        'reviewrateable_type' => $product->getMorphClass(),
        'reviewrateable_id' => $product->id,
        'author_type' => $user->getMorphClass(),
        'author_id' => $user->id,
    ], $attributes));
}

test('guests are redirected when submitting a product review', function (): void {
    $product = createPublishedProduct();

    $this->post(route('shop.product.reviews.store', $product), [
        'rating' => 5,
        'title' => 'Nice',
        'content' => 'Works well.',
    ])->assertRedirect(route('login'));
});

test('authenticated users can submit a product review for moderation', function (): void {
    $product = createPublishedProduct();
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('shop.product.reviews.store', $product), [
            'rating' => 4,
            'title' => 'Solid buy',
            'content' => 'Good value for money.',
            'is_recommended' => true,
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas(shopper_table('reviews'), [
        'reviewrateable_id' => $product->id,
        'reviewrateable_type' => $product->getMorphClass(),
        'author_id' => $user->id,
        'author_type' => $user->getMorphClass(),
        'rating' => 4,
        'title' => 'Solid buy',
        'content' => 'Good value for money.',
        'approved' => false,
        'is_recommended' => true,
    ]);
});

test('users cannot submit a second review for the same product', function (): void {
    $product = createPublishedProduct();
    $user = User::factory()->create();

    createProductReview($product, $user);

    $this->actingAs($user)
        ->from(route('shop.product', $product))
        ->post(route('shop.product.reviews.store', $product), [
            'rating' => 3,
            'title' => 'Another try',
            'content' => 'Trying again.',
        ])
        ->assertRedirect(route('shop.product', $product))
        ->assertSessionHasErrors('review');

    expect(Review::query()->where('reviewrateable_id', $product->id)->count())->toBe(1);
});

test('product review rating is validated', function (): void {
    $product = createPublishedProduct();
    $user = User::factory()->create();

    $this->actingAs($user)
        ->from(route('shop.product', $product))
        ->post(route('shop.product.reviews.store', $product), [
            'rating' => 0,
            'title' => 'Invalid',
            'content' => 'Should fail.',
        ])
        ->assertRedirect(route('shop.product', $product))
        ->assertSessionHasErrors('rating');
});

test('product page only includes approved reviews', function (): void {
    $product = createPublishedProduct();
    $approvedAuthor = User::factory()->create([
        'first_name' => 'Anna',
        'last_name' => 'Approved',
    ]);
    $pendingAuthor = User::factory()->create([
        'first_name' => 'Pete',
        'last_name' => 'Pending',
    ]);

    createProductReview($product, $approvedAuthor, [
        'approved' => true,
        'rating' => 5,
        'title' => 'Visible review',
    ]);

    createProductReview($product, $pendingAuthor, [
        'approved' => false,
        'rating' => 1,
        'title' => 'Hidden review',
    ]);

    $this->get(route('shop.product', $product))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shop/product')
            ->has('product.reviews', 1)
            ->where('product.reviews.0.title', 'Visible review')
            ->where('product.reviews.0.rating', 5)
            ->where('canReview', false)
        );
});

test('authenticated users who have not reviewed yet can review the product', function (): void {
    $product = createPublishedProduct();
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('shop.product', $product))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shop/product')
            ->where('canReview', true)
        );
});

test('product page sets canReview to false after user has submitted a review', function (): void {
    $product = createPublishedProduct();
    $user = User::factory()->create();

    createProductReview($product, $user);

    $this->actingAs($user)
        ->get(route('shop.product', $product))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shop/product')
            ->where('canReview', false)
        );
});
