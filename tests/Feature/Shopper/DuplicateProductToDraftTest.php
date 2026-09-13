<?php

declare(strict_types=1);

use App\Livewire\Shopper\Components\Products\Form\Edit as ProductEditForm;
use App\Livewire\Shopper\Pages\Product\Index;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Shopper\Core\Enum\FieldType;
use Shopper\Core\Models\Attribute;
use Shopper\Core\Models\AttributeValue;
use Shopper\Core\Models\Currency;
use Shopper\Core\Models\Price;
use Shopper\Core\Models\Setting;
use Shopper\Database\Seeders\AuthTableSeeder;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(AuthTableSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole(config('shopper.admin.roles.admin'));

    $this->currency = Currency::query()->create([
        'name' => 'US Dollar',
        'code' => 'USD',
        'symbol' => '$',
        'format' => '$1,234.56',
        'is_enabled' => true,
    ]);

    Setting::query()->create([
        'key' => 'default_currency_id',
        'display_name' => 'Currency',
        'value' => $this->currency->id,
        'locked' => true,
    ]);
});

test('admins can copy a product to a draft with attributes categories price and translation', function (): void {
    $category = Category::factory()->create([
        'name' => 'Wine',
        'slug' => 'wine',
        'is_enabled' => true,
        'parent_id' => null,
    ]);

    $product = Product::factory()->standard()->create([
        'name' => 'Cabernet',
        'slug' => 'cabernet',
        'sku' => 'SKU-CABERNET',
        'barcode' => '1234567890123',
        'summary' => 'Rich red wine',
        'is_visible' => true,
    ]);
    $product->categories()->attach($category);
    $product->saveCatalogTranslation('en', [
        'name' => 'Cabernet Sauvignon',
        'summary' => 'English summary',
    ]);

    $origin = Attribute::factory()->create([
        'name' => 'Origin',
        'slug' => 'origin',
        'type' => FieldType::Select,
        'is_enabled' => true,
    ]);
    $material = Attribute::factory()->create([
        'name' => 'Material',
        'slug' => 'material',
        'type' => FieldType::Text,
        'is_enabled' => true,
    ]);
    $ukraine = AttributeValue::factory()->create([
        'attribute_id' => $origin->id,
        'key' => 'ukraine',
        'value' => 'Ukraine',
        'position' => 1,
    ]);

    $product->options()->attach($origin->id, [
        'attribute_value_id' => $ukraine->id,
    ]);
    $product->options()->attach($material->id, [
        'attribute_custom_value' => 'Cotton',
    ]);

    Price::query()->create([
        'priceable_type' => $product->getMorphClass(),
        'priceable_id' => $product->id,
        'amount' => 19900,
        'compare_amount' => 24900,
        'cost_amount' => null,
        'currency_id' => $this->currency->id,
    ]);

    $component = Livewire::actingAs($this->admin)
        ->test(Index::class)
        ->assertTableActionExists('copyToDraft')
        ->callTableAction('copyToDraft', $product);

    $draft = Product::query()->where('id', '!=', $product->id)->latest('id')->first();

    $component->assertRedirect(route('shopper.products.edit', $draft));

    expect($draft)->not->toBeNull()
        ->and($draft->name)->toBe('Cabernet')
        ->and($draft->summary)->toBe('Rich red wine')
        ->and($draft->slug)->toBeNull()
        ->and($draft->sku)->toBeNull()
        ->and($draft->barcode)->toBeNull()
        ->and($draft->is_visible)->toBeFalse()
        ->and($draft->isPublished())->toBeFalse();

    $product->refresh();

    expect($product->slug)->toBe('cabernet')
        ->and($product->sku)->toBe('SKU-CABERNET')
        ->and($product->is_visible)->toBeTrue();

    expect($draft->categories()->pluck('id')->all())->toBe([$category->id])
        ->and($draft->catalogTranslationPayload('en')['name'])->toBe('Cabernet Sauvignon')
        ->and($draft->catalogTranslationPayload('en')['summary'])->toBe('English summary')
        ->and($draft->getPrice()?->amount)->toBe(19900)
        ->and($draft->getPrice()?->compare_amount)->toBe(24900);

    $draft->load('attributeProducts.value');

    expect($draft->attributeProducts)->toHaveCount(2)
        ->and($draft->attributeProducts->firstWhere('attribute_id', $origin->id)?->attribute_value_id)->toBe($ukraine->id)
        ->and($draft->attributeProducts->firstWhere('attribute_id', $material->id)?->attribute_custom_value)->toBe('Cotton');
});

test('saving a draft copy generates a unique slug from the name', function (): void {
    $product = Product::factory()->standard()->create([
        'name' => 'Cabernet',
        'slug' => 'cabernet',
        'sku' => 'SKU-ORIGINAL',
        'is_visible' => true,
    ]);

    Livewire::actingAs($this->admin)
        ->test(Index::class)
        ->callTableAction('copyToDraft', $product);

    $draft = Product::query()->where('id', '!=', $product->id)->latest('id')->first();

    expect($draft)->not->toBeNull()
        ->and($draft->slug)->toBeNull();

    Livewire::actingAs($this->admin)
        ->test(ProductEditForm::class, ['product' => $draft])
        ->call('store')
        ->assertHasNoErrors();

    $draft->refresh();

    expect($draft->slug)->toBe('cabernet-1')
        ->and($product->refresh()->slug)->toBe('cabernet');
});

test('copying a variant product also copies variants and their attribute values', function (): void {
    $product = Product::factory()->variant()->create([
        'name' => 'Shirt',
        'slug' => 'shirt',
        'sku' => 'SKU-SHIRT',
        'is_visible' => true,
    ]);

    $color = Attribute::factory()->create([
        'name' => 'Color',
        'slug' => 'color',
        'type' => FieldType::Select,
        'is_enabled' => true,
    ]);
    $red = AttributeValue::factory()->create([
        'attribute_id' => $color->id,
        'key' => 'red',
        'value' => 'Red',
        'position' => 1,
    ]);

    $product->options()->attach($color->id, [
        'attribute_value_id' => $red->id,
    ]);

    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'name' => 'Shirt Red',
        'sku' => 'SKU-SHIRT-RED',
        'position' => 1,
    ]);
    $variant->values()->attach($red->id);
    $variant->saveCatalogTranslation('en', [
        'name' => 'Red shirt',
    ]);

    Price::query()->create([
        'priceable_type' => $variant->getMorphClass(),
        'priceable_id' => $variant->id,
        'amount' => 15000,
        'compare_amount' => null,
        'cost_amount' => null,
        'currency_id' => $this->currency->id,
    ]);

    Livewire::actingAs($this->admin)
        ->test(Index::class)
        ->callTableAction('copyToDraft', $product);

    $draft = Product::query()->where('id', '!=', $product->id)->latest('id')->first();

    expect($draft)->not->toBeNull()
        ->and($draft->is_visible)->toBeFalse()
        ->and($draft->variants)->toHaveCount(1);

    $copiedVariant = $draft->variants->first();

    expect($copiedVariant)->not->toBeNull()
        ->and($copiedVariant->id)->not->toBe($variant->id)
        ->and($copiedVariant->name)->toBe('Shirt Red')
        ->and($copiedVariant->sku)->toBeNull()
        ->and($copiedVariant->values->modelKeys())->toBe([$red->id])
        ->and($copiedVariant->catalogTranslationPayload('en')['name'])->toBe('Red shirt')
        ->and($copiedVariant->getPrice()?->amount)->toBe(15000);

    expect($product->refresh()->variants)->toHaveCount(1)
        ->and($variant->refresh()->sku)->toBe('SKU-SHIRT-RED');
});

test('users without add products permission cannot copy a product to a draft', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo('browse_products');

    $product = Product::factory()->standard()->create([
        'name' => 'Hidden copy',
        'slug' => 'hidden-copy',
    ]);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->assertTableActionHidden('copyToDraft', $product);

    expect(Product::query()->count())->toBe(1);
});
