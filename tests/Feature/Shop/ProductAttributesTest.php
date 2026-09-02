<?php

declare(strict_types=1);

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Shopper\Core\Enum\FieldType;
use Shopper\Core\Models\Attribute;
use Shopper\Core\Models\AttributeValue;
use Shopper\Core\Models\Currency;
use Shopper\Core\Models\Price;
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

    $this->currency = $currency;
});

test('standard product page includes text and select attributes', function (): void {
    $product = Product::factory()->standard()->create([
        'name' => 'Cotton Shirt',
        'slug' => 'cotton-shirt',
    ]);

    $material = Attribute::factory()->create([
        'name' => 'Material',
        'slug' => 'material',
        'type' => FieldType::Text,
        'is_enabled' => true,
    ]);

    $origin = Attribute::factory()->create([
        'name' => 'Origin',
        'slug' => 'origin',
        'type' => FieldType::Select,
        'is_enabled' => true,
    ]);

    $ukraine = AttributeValue::factory()->create([
        'attribute_id' => $origin->id,
        'key' => 'ukraine',
        'value' => 'Ukraine',
        'position' => 1,
    ]);

    $product->options()->attach($material->id, [
        'attribute_custom_value' => 'Cotton',
    ]);
    $product->options()->attach($origin->id, [
        'attribute_value_id' => $ukraine->id,
    ]);

    $this->get(route('shop.product', $product))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shop/product')
            ->has('productAttributes', 2)
            ->where('productAttributes.0.name', 'Material')
            ->where('productAttributes.0.value', 'Cotton')
            ->where('productAttributes.0.type', 'text')
            ->where('productAttributes.1.name', 'Origin')
            ->where('productAttributes.1.value', 'Ukraine')
            ->where('productAttributes.1.type', 'select')
        );
});

test('variant product page excludes attributes used as variant options', function (): void {
    $product = Product::factory()->variant()->create([
        'name' => 'Variant shirt',
        'slug' => 'variant-shirt',
    ]);

    $color = Attribute::factory()->create([
        'name' => 'Color',
        'slug' => 'color',
        'type' => FieldType::Select,
        'is_enabled' => true,
    ]);

    $material = Attribute::factory()->create([
        'name' => 'Material',
        'slug' => 'material',
        'type' => FieldType::Text,
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
    $product->options()->attach($material->id, [
        'attribute_custom_value' => 'Cotton',
    ]);

    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'name' => 'Red',
        'position' => 1,
    ]);
    $variant->values()->attach($red->id);

    Price::query()->create([
        'priceable_type' => 'variant',
        'priceable_id' => $variant->id,
        'amount' => 15000,
        'compare_amount' => null,
        'cost_amount' => null,
        'currency_id' => $this->currency->id,
    ]);

    $this->get(route('shop.product', $product))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shop/product')
            ->has('productAttributes', 1)
            ->where('productAttributes.0.name', 'Material')
            ->where('productAttributes.0.value', 'Cotton')
        );
});

test('product page excludes disabled attributes', function (): void {
    $product = Product::factory()->standard()->create([
        'name' => 'Disabled attribute product',
        'slug' => 'disabled-attribute-product',
    ]);

    $disabled = Attribute::factory()->create([
        'name' => 'Hidden spec',
        'slug' => 'hidden-spec',
        'type' => FieldType::Text,
        'is_enabled' => false,
    ]);

    $enabled = Attribute::factory()->create([
        'name' => 'Visible spec',
        'slug' => 'visible-spec',
        'type' => FieldType::Text,
        'is_enabled' => true,
    ]);

    $product->options()->attach($disabled->id, [
        'attribute_custom_value' => 'Should not appear',
    ]);
    $product->options()->attach($enabled->id, [
        'attribute_custom_value' => 'Should appear',
    ]);

    $this->get(route('shop.product', $product))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shop/product')
            ->has('productAttributes', 1)
            ->where('productAttributes.0.name', 'Visible spec')
            ->where('productAttributes.0.value', 'Should appear')
        );
});

test('product page joins checkbox attribute values', function (): void {
    $product = Product::factory()->standard()->create([
        'name' => 'Checkbox attribute product',
        'slug' => 'checkbox-attribute-product',
    ]);

    $features = Attribute::factory()->create([
        'name' => 'Features',
        'slug' => 'features',
        'type' => FieldType::Checkbox,
        'is_enabled' => true,
    ]);

    $waterproof = AttributeValue::factory()->create([
        'attribute_id' => $features->id,
        'key' => 'waterproof',
        'value' => 'Waterproof',
        'position' => 1,
    ]);

    $breathable = AttributeValue::factory()->create([
        'attribute_id' => $features->id,
        'key' => 'breathable',
        'value' => 'Breathable',
        'position' => 2,
    ]);

    $product->options()->attach($features->id, [
        'attribute_value_id' => $waterproof->id,
    ]);
    $product->options()->attach($features->id, [
        'attribute_value_id' => $breathable->id,
    ]);

    $this->get(route('shop.product', $product))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shop/product')
            ->has('productAttributes', 1)
            ->where('productAttributes.0.name', 'Features')
            ->where('productAttributes.0.value', 'Waterproof, Breathable')
            ->where('productAttributes.0.type', 'checkbox')
        );
});
