<?php

declare(strict_types=1);

use App\Livewire\Shopper\Components\Products\Form\Edit as ProductEditForm;
use App\Livewire\Shopper\Pages\HomepageBanners\Edit as BannerEdit;
use App\Livewire\Shopper\SlideOvers\AttributeForm;
use App\Livewire\Shopper\SlideOvers\CategoryForm;
use App\Models\CatalogTranslation;
use App\Models\Category;
use App\Models\HomepageBanner;
use App\Models\Product;
use App\Models\User;
use App\Support\CatalogFieldMap;
use Database\Seeders\HomepageBannerPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Shopper\Core\Models\Attribute;
use Shopper\Database\Seeders\AuthTableSeeder;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(AuthTableSeeder::class);
    $this->seed(HomepageBannerPermissionsSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole(config('shopper.admin.roles.admin'));
});

test('saving english banner copy does not overwrite ukrainian fields', function (): void {
    $banner = HomepageBanner::factory()->create([
        'title' => 'Літній розпродаж',
        'eyebrow' => 'Новинка',
        'button_text' => 'Купити',
        'cta_url' => '/shop',
    ]);

    Livewire::actingAs($this->admin)
        ->test(BannerEdit::class, ['banner' => $banner])
        ->set('data.english.title', 'Summer sale')
        ->set('data.english.eyebrow', 'New')
        ->set('data.english.button_text', 'Shop now')
        ->call('store')
        ->assertHasNoErrors();

    $banner->refresh();

    expect($banner->title)->toBe('Літній розпродаж')
        ->and($banner->eyebrow)->toBe('Новинка')
        ->and($banner->button_text)->toBe('Купити');

    $translation = CatalogTranslation::query()
        ->where('translatable_type', $banner->getMorphClass())
        ->where('translatable_id', $banner->id)
        ->where('locale', 'en')
        ->first();

    expect($translation)->not->toBeNull()
        ->and($translation->name)->toBe('Summer sale')
        ->and($translation->eyebrow)->toBe('New')
        ->and($translation->button_text)->toBe('Shop now');
});

test('saving english product fields does not overwrite ukrainian name', function (): void {
    $product = Product::factory()->standard()->create([
        'name' => 'Навушники',
        'slug' => 'navushnyky',
        'summary' => 'Короткий опис',
    ]);

    Livewire::actingAs($this->admin)
        ->test(ProductEditForm::class, ['product' => $product])
        ->set('data.english.name', 'Headphones')
        ->set('data.english.summary', 'Short summary')
        ->call('store')
        ->assertHasNoErrors();

    $product->refresh();

    expect($product->name)->toBe('Навушники')
        ->and($product->summary)->toBe('Короткий опис');

    expect($product->catalogTranslationPayload('en')['name'])->toBe('Headphones')
        ->and($product->catalogTranslationPayload('en')['summary'])->toBe('Short summary');
});

test('saving english category fields does not overwrite ukrainian name', function (): void {
    $category = Category::factory()->create([
        'name' => 'Аудіо',
        'slug' => 'audio',
        'is_enabled' => true,
        'parent_id' => null,
    ]);

    Livewire::actingAs($this->admin)
        ->test(CategoryForm::class, ['category' => $category])
        ->set('data.english.name', 'Audio')
        ->call('save')
        ->assertHasNoErrors();

    $category->refresh();

    expect($category->name)->toBe('Аудіо')
        ->and($category->catalogTranslationPayload('en')['name'])->toBe('Audio');
});

test('saving english attribute name does not overwrite ukrainian name', function (): void {
    $attribute = Attribute::factory()->create([
        'name' => 'Колір',
        'slug' => 'kolir',
    ]);

    Livewire::actingAs($this->admin)
        ->test(AttributeForm::class, ['attributeId' => $attribute->id])
        ->set('data.english.name', 'Color')
        ->call('store')
        ->assertHasNoErrors();

    $attribute->refresh();

    expect($attribute->name)->toBe('Колір');

    $payload = CatalogTranslation::payloadFor($attribute, 'en', CatalogFieldMap::for($attribute));

    expect($payload['name'])->toBe('Color');
});
