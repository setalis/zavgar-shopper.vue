<?php

declare(strict_types=1);

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Shopper\Core\Enum\FieldType;
use Shopper\Core\Models\Attribute;
use Shopper\Core\Models\AttributeValue;
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

test('empty category has no attribute filters', function (): void {
    $category = Category::factory()->create([
        'name' => 'Empty',
        'slug' => 'empty',
        'is_enabled' => true,
        'parent_id' => null,
    ]);

    $this->get(route('shop.category', $category))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shop/category')
            ->has('attributeFilters', 0)
            ->where('filters.sort', 'latest')
            ->where('filters.attrs', [])
        );
});

test('products without filterable attributes yield no attribute filters', function (): void {
    $category = Category::factory()->create([
        'name' => 'Cameras',
        'slug' => 'cameras',
        'is_enabled' => true,
        'parent_id' => null,
    ]);

    $product = Product::factory()->standard()->create([
        'name' => 'Mirrorless Body',
        'slug' => 'mirrorless-body',
    ]);
    $product->categories()->attach($category);

    $hidden = Attribute::factory()->create([
        'name' => 'Hidden spec',
        'slug' => 'hidden-spec',
        'type' => FieldType::Text,
        'is_enabled' => true,
        'is_filterable' => false,
    ]);
    $product->options()->attach($hidden->id, [
        'attribute_custom_value' => 'Should not appear',
    ]);

    $this->get(route('shop.category', $category))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shop/category')
            ->has('products.data', 1)
            ->has('attributeFilters', 0)
        );
});

test('attribute filters include only filterable attributes from published products in the category', function (): void {
    $cameras = Category::factory()->create([
        'name' => 'Cameras',
        'slug' => 'cameras',
        'is_enabled' => true,
        'parent_id' => null,
    ]);

    $lenses = Category::factory()->create([
        'name' => 'Lenses',
        'slug' => 'lenses',
        'is_enabled' => true,
        'parent_id' => null,
    ]);

    $origin = Attribute::factory()->create([
        'name' => 'Origin',
        'slug' => 'origin',
        'type' => FieldType::Select,
        'is_enabled' => true,
        'is_filterable' => true,
    ]);
    $ukraine = AttributeValue::factory()->create([
        'attribute_id' => $origin->id,
        'key' => 'ukraine',
        'value' => 'Ukraine',
        'position' => 1,
    ]);
    $japan = AttributeValue::factory()->create([
        'attribute_id' => $origin->id,
        'key' => 'japan',
        'value' => 'Japan',
        'position' => 2,
    ]);

    $material = Attribute::factory()->create([
        'name' => 'Material',
        'slug' => 'material',
        'type' => FieldType::Text,
        'is_enabled' => true,
        'is_filterable' => true,
    ]);

    $disabled = Attribute::factory()->create([
        'name' => 'Disabled spec',
        'slug' => 'disabled-spec',
        'type' => FieldType::Text,
        'is_enabled' => false,
        'is_filterable' => true,
    ]);

    $camera = Product::factory()->standard()->create([
        'name' => 'Camera',
        'slug' => 'camera',
    ]);
    $camera->categories()->attach($cameras);
    $camera->options()->attach($origin->id, [
        'attribute_value_id' => $ukraine->id,
    ]);
    $camera->options()->attach($material->id, [
        'attribute_custom_value' => 'Magnesium',
    ]);
    $camera->options()->attach($disabled->id, [
        'attribute_custom_value' => 'Hidden',
    ]);

    $draft = Product::factory()->create([
        'name' => 'Draft camera',
        'slug' => 'draft-camera',
        'is_visible' => false,
        'published_at' => now(),
    ]);
    $draft->categories()->attach($cameras);
    $draft->options()->attach($origin->id, [
        'attribute_value_id' => $japan->id,
    ]);

    $lens = Product::factory()->standard()->create([
        'name' => 'Prime lens',
        'slug' => 'prime-lens',
    ]);
    $lens->categories()->attach($lenses);
    $lens->options()->attach($origin->id, [
        'attribute_value_id' => $japan->id,
    ]);

    $this->get(route('shop.category', $cameras))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shop/category')
            ->has('attributeFilters', 2)
            ->where('attributeFilters.0.slug', 'material')
            ->where('attributeFilters.0.values.0.key', 'Magnesium')
            ->where('attributeFilters.1.slug', 'origin')
            ->has('attributeFilters.1.values', 1)
            ->where('attributeFilters.1.values.0.key', 'ukraine')
            ->where('attributeFilters.1.values.0.label', 'Ukraine')
        );
});

test('filtering by a single attribute value narrows the product list', function (): void {
    [$category, $cotton, $silk] = categoryWithMaterialProducts();

    $this->get(route('shop.category', [
        'category' => $category,
        'attrs' => ['material' => ['Cotton']],
    ]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shop/category')
            ->has('products.data', 1)
            ->where('products.data.0.id', $cotton->id)
            ->has('attributeFilters', 1)
            ->where('filters.attrs.material.0', 'Cotton')
            ->missing('products.data.1')
        );

    expect($silk->id)->not->toBe($cotton->id);
});

test('multiple values of the same attribute are combined with OR', function (): void {
    [$category, $cotton, $silk] = categoryWithMaterialProducts();

    $this->get(route('shop.category', [
        'category' => $category,
        'attrs' => ['material' => ['Cotton', 'Silk']],
    ]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('products.data', 2)
            ->where('products.data.0.id', $silk->id)
            ->where('products.data.1.id', $cotton->id)
        );
});

test('different attributes are combined with AND', function (): void {
    $category = Category::factory()->create([
        'name' => 'Shirts',
        'slug' => 'shirts',
        'is_enabled' => true,
        'parent_id' => null,
    ]);

    $origin = Attribute::factory()->create([
        'name' => 'Origin',
        'slug' => 'origin',
        'type' => FieldType::Select,
        'is_enabled' => true,
        'is_filterable' => true,
    ]);
    $ukraine = AttributeValue::factory()->create([
        'attribute_id' => $origin->id,
        'key' => 'ukraine',
        'value' => 'Ukraine',
        'position' => 1,
    ]);
    $poland = AttributeValue::factory()->create([
        'attribute_id' => $origin->id,
        'key' => 'poland',
        'value' => 'Poland',
        'position' => 2,
    ]);

    $material = Attribute::factory()->create([
        'name' => 'Material',
        'slug' => 'material',
        'type' => FieldType::Text,
        'is_enabled' => true,
        'is_filterable' => true,
    ]);

    $match = Product::factory()->standard()->create([
        'name' => 'Cotton Ukraine',
        'slug' => 'cotton-ukraine',
        'created_at' => now()->subDay(),
    ]);
    $match->categories()->attach($category);
    $match->options()->attach($origin->id, ['attribute_value_id' => $ukraine->id]);
    $match->options()->attach($material->id, ['attribute_custom_value' => 'Cotton']);

    $otherOrigin = Product::factory()->standard()->create([
        'name' => 'Cotton Poland',
        'slug' => 'cotton-poland',
    ]);
    $otherOrigin->categories()->attach($category);
    $otherOrigin->options()->attach($origin->id, ['attribute_value_id' => $poland->id]);
    $otherOrigin->options()->attach($material->id, ['attribute_custom_value' => 'Cotton']);

    $otherMaterial = Product::factory()->standard()->create([
        'name' => 'Silk Ukraine',
        'slug' => 'silk-ukraine',
    ]);
    $otherMaterial->categories()->attach($category);
    $otherMaterial->options()->attach($origin->id, ['attribute_value_id' => $ukraine->id]);
    $otherMaterial->options()->attach($material->id, ['attribute_custom_value' => 'Silk']);

    $this->get(route('shop.category', [
        'category' => $category,
        'attrs' => [
            'origin' => ['ukraine'],
            'material' => ['Cotton'],
        ],
    ]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('products.data', 1)
            ->where('products.data.0.id', $match->id)
            ->has('attributeFilters', 2)
        );
});

test('attribute filters remain when the current selection matches no products', function (): void {
    $category = Category::factory()->create([
        'name' => 'Shirts',
        'slug' => 'shirts',
        'is_enabled' => true,
        'parent_id' => null,
    ]);

    $origin = Attribute::factory()->create([
        'name' => 'Origin',
        'slug' => 'origin',
        'type' => FieldType::Select,
        'is_enabled' => true,
        'is_filterable' => true,
    ]);
    $ukraine = AttributeValue::factory()->create([
        'attribute_id' => $origin->id,
        'key' => 'ukraine',
        'value' => 'Ukraine',
        'position' => 1,
    ]);
    $poland = AttributeValue::factory()->create([
        'attribute_id' => $origin->id,
        'key' => 'poland',
        'value' => 'Poland',
        'position' => 2,
    ]);

    $material = Attribute::factory()->create([
        'name' => 'Material',
        'slug' => 'material',
        'type' => FieldType::Text,
        'is_enabled' => true,
        'is_filterable' => true,
    ]);

    $cottonUkraine = Product::factory()->standard()->create([
        'name' => 'Cotton Ukraine',
        'slug' => 'cotton-ukraine',
    ]);
    $cottonUkraine->categories()->attach($category);
    $cottonUkraine->options()->attach($origin->id, ['attribute_value_id' => $ukraine->id]);
    $cottonUkraine->options()->attach($material->id, ['attribute_custom_value' => 'Cotton']);

    $silkPoland = Product::factory()->standard()->create([
        'name' => 'Silk Poland',
        'slug' => 'silk-poland',
    ]);
    $silkPoland->categories()->attach($category);
    $silkPoland->options()->attach($origin->id, ['attribute_value_id' => $poland->id]);
    $silkPoland->options()->attach($material->id, ['attribute_custom_value' => 'Silk']);

    $this->get(route('shop.category', [
        'category' => $category,
        'attrs' => [
            'origin' => ['ukraine'],
            'material' => ['Silk'],
        ],
    ]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('products.data', 0)
            ->has('attributeFilters', 2)
            ->where('filters.attrs.origin.0', 'ukraine')
            ->where('filters.attrs.material.0', 'Silk')
        );
});

test('sort is preserved together with attribute filters', function (): void {
    [$category] = categoryWithMaterialProducts();

    $this->get(route('shop.category', [
        'category' => $category,
        'sort' => 'name',
        'attrs' => ['material' => ['Cotton']],
    ]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('filters.sort', 'name')
            ->where('filters.attrs.material.0', 'Cotton')
            ->has('products.data', 1)
        );
});

test('unknown attribute filter keys are ignored', function (): void {
    [$category, $cotton, $silk] = categoryWithMaterialProducts();

    $this->get(route('shop.category', [
        'category' => $category,
        'attrs' => [
            'material' => ['DoesNotExist'],
            'unknown' => ['value'],
        ],
    ]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('products.data', 2)
            ->where('products.data.0.id', $silk->id)
            ->where('products.data.1.id', $cotton->id)
            ->where('filters.attrs.material.0', 'DoesNotExist')
        );
});

test('brand filter is first and lists only enabled brands of published products in the category', function (): void {
    $category = Category::factory()->create([
        'name' => 'Oils',
        'slug' => 'oils',
        'is_enabled' => true,
        'parent_id' => null,
    ]);

    $otherCategory = Category::factory()->create([
        'name' => 'Filters',
        'slug' => 'filters',
        'is_enabled' => true,
        'parent_id' => null,
    ]);

    $castrol = Brand::factory()->create([
        'name' => 'Castrol',
        'slug' => 'castrol',
        'is_enabled' => true,
    ]);
    $zavgar = Brand::factory()->create([
        'name' => 'Zavgar',
        'slug' => 'zavgar',
        'is_enabled' => true,
    ]);
    $disabled = Brand::factory()->create([
        'name' => 'Hidden',
        'slug' => 'hidden-brand',
        'is_enabled' => false,
    ]);
    $otherBrand = Brand::factory()->create([
        'name' => 'Other',
        'slug' => 'other-brand',
        'is_enabled' => true,
    ]);

    $material = Attribute::factory()->create([
        'name' => 'Viscosity',
        'slug' => 'viscosity',
        'type' => FieldType::Text,
        'is_enabled' => true,
        'is_filterable' => true,
    ]);

    $castrolOil = Product::factory()->standard()->create([
        'name' => 'Castrol 5W-40',
        'slug' => 'castrol-5w-40',
        'brand_id' => $castrol->id,
    ]);
    $castrolOil->categories()->attach($category);
    $castrolOil->options()->attach($material->id, [
        'attribute_custom_value' => '5W-40',
    ]);

    $zavgarOil = Product::factory()->standard()->create([
        'name' => 'Zavgar 10W-40',
        'slug' => 'zavgar-10w-40',
        'brand_id' => $zavgar->id,
    ]);
    $zavgarOil->categories()->attach($category);

    $disabledBrandProduct = Product::factory()->standard()->create([
        'name' => 'Hidden oil',
        'slug' => 'hidden-oil',
        'brand_id' => $disabled->id,
    ]);
    $disabledBrandProduct->categories()->attach($category);

    $draft = Product::factory()->create([
        'name' => 'Draft oil',
        'slug' => 'draft-oil',
        'brand_id' => $otherBrand->id,
        'is_visible' => false,
        'published_at' => now(),
    ]);
    $draft->categories()->attach($category);

    $otherCategoryProduct = Product::factory()->standard()->create([
        'name' => 'Cabin filter',
        'slug' => 'cabin-filter',
        'brand_id' => $otherBrand->id,
    ]);
    $otherCategoryProduct->categories()->attach($otherCategory);

    $this->get(route('shop.category', $category))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shop/category')
            ->has('attributeFilters', 2)
            ->where('attributeFilters.0.slug', 'brand')
            ->has('attributeFilters.0.values', 2)
            ->where('attributeFilters.0.values.0.key', 'castrol')
            ->where('attributeFilters.0.values.0.label', 'Castrol')
            ->where('attributeFilters.0.values.1.key', 'zavgar')
            ->where('attributeFilters.1.slug', 'viscosity')
        );
});

test('filtering by brand narrows the product list', function (): void {
    $category = Category::factory()->create([
        'name' => 'Oils',
        'slug' => 'oils',
        'is_enabled' => true,
        'parent_id' => null,
    ]);

    $castrol = Brand::factory()->create([
        'name' => 'Castrol',
        'slug' => 'castrol',
        'is_enabled' => true,
    ]);
    $zavgar = Brand::factory()->create([
        'name' => 'Zavgar',
        'slug' => 'zavgar',
        'is_enabled' => true,
    ]);

    $castrolOil = Product::factory()->standard()->create([
        'name' => 'Castrol 5W-40',
        'slug' => 'castrol-5w-40',
        'brand_id' => $castrol->id,
        'created_at' => now()->subDay(),
    ]);
    $castrolOil->categories()->attach($category);

    $zavgarOil = Product::factory()->standard()->create([
        'name' => 'Zavgar 10W-40',
        'slug' => 'zavgar-10w-40',
        'brand_id' => $zavgar->id,
    ]);
    $zavgarOil->categories()->attach($category);

    $this->get(route('shop.category', [
        'category' => $category,
        'attrs' => ['brand' => ['castrol']],
    ]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('products.data', 1)
            ->where('products.data.0.id', $castrolOil->id)
            ->where('filters.attrs.brand.0', 'castrol')
            ->where('attributeFilters.0.slug', 'brand')
            ->has('attributeFilters.0.values', 2)
        );

    expect($zavgarOil->id)->not->toBe($castrolOil->id);
});

test('brand and attribute filters are combined with AND', function (): void {
    $category = Category::factory()->create([
        'name' => 'Oils',
        'slug' => 'oils',
        'is_enabled' => true,
        'parent_id' => null,
    ]);

    $castrol = Brand::factory()->create([
        'name' => 'Castrol',
        'slug' => 'castrol',
        'is_enabled' => true,
    ]);
    $zavgar = Brand::factory()->create([
        'name' => 'Zavgar',
        'slug' => 'zavgar',
        'is_enabled' => true,
    ]);

    $viscosity = Attribute::factory()->create([
        'name' => 'Viscosity',
        'slug' => 'viscosity',
        'type' => FieldType::Text,
        'is_enabled' => true,
        'is_filterable' => true,
    ]);

    $match = Product::factory()->standard()->create([
        'name' => 'Castrol 5W-40',
        'slug' => 'castrol-5w-40',
        'brand_id' => $castrol->id,
        'created_at' => now()->subDay(),
    ]);
    $match->categories()->attach($category);
    $match->options()->attach($viscosity->id, [
        'attribute_custom_value' => '5W-40',
    ]);

    $sameBrand = Product::factory()->standard()->create([
        'name' => 'Castrol 10W-40',
        'slug' => 'castrol-10w-40',
        'brand_id' => $castrol->id,
    ]);
    $sameBrand->categories()->attach($category);
    $sameBrand->options()->attach($viscosity->id, [
        'attribute_custom_value' => '10W-40',
    ]);

    $sameViscosity = Product::factory()->standard()->create([
        'name' => 'Zavgar 5W-40',
        'slug' => 'zavgar-5w-40',
        'brand_id' => $zavgar->id,
    ]);
    $sameViscosity->categories()->attach($category);
    $sameViscosity->options()->attach($viscosity->id, [
        'attribute_custom_value' => '5W-40',
    ]);

    $this->get(route('shop.category', [
        'category' => $category,
        'attrs' => [
            'brand' => ['castrol'],
            'viscosity' => ['5W-40'],
        ],
    ]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('products.data', 1)
            ->where('products.data.0.id', $match->id)
            ->has('attributeFilters', 2)
            ->where('attributeFilters.0.slug', 'brand')
        );
});

/**
 * @return array{0: Category, 1: Product, 2: Product}
 */
function categoryWithMaterialProducts(): array
{
    $category = Category::factory()->create([
        'name' => 'Fabrics',
        'slug' => 'fabrics',
        'is_enabled' => true,
        'parent_id' => null,
    ]);

    $material = Attribute::factory()->create([
        'name' => 'Material',
        'slug' => 'material',
        'type' => FieldType::Text,
        'is_enabled' => true,
        'is_filterable' => true,
    ]);

    $cotton = Product::factory()->standard()->create([
        'name' => 'Cotton Shirt',
        'slug' => 'cotton-shirt',
        'created_at' => now()->subDay(),
    ]);
    $cotton->categories()->attach($category);
    $cotton->options()->attach($material->id, [
        'attribute_custom_value' => 'Cotton',
    ]);

    $silk = Product::factory()->standard()->create([
        'name' => 'Silk Shirt',
        'slug' => 'silk-shirt',
        'created_at' => now(),
    ]);
    $silk->categories()->attach($category);
    $silk->options()->attach($material->id, [
        'attribute_custom_value' => 'Silk',
    ]);

    return [$category, $cotton, $silk];
}
