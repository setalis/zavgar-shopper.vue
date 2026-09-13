<?php

declare(strict_types=1);

use App\Livewire\Shopper\Pages\Product\Index;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Shopper\Core\Models\Currency;
use Shopper\Core\Models\Setting;
use Shopper\Database\Seeders\AuthTableSeeder;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(AuthTableSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole(config('shopper.admin.roles.admin'));

    $currency = Currency::query()->create([
        'name' => 'US Dollar',
        'code' => 'USD',
        'symbol' => '$',
        'format' => '$1,234.56',
        'is_enabled' => true,
    ]);

    Setting::query()->create([
        'key' => 'default_currency_id',
        'display_name' => 'Currency',
        'value' => $currency->id,
        'locked' => true,
    ]);
});

test('product index shows category names and filters products by category', function (): void {
    $wine = Category::factory()->create([
        'name' => 'Wine',
        'slug' => 'wine',
        'is_enabled' => true,
        'parent_id' => null,
    ]);
    $red = Category::factory()->create([
        'name' => 'Red',
        'slug' => 'red',
        'is_enabled' => true,
        'parent_id' => $wine->id,
    ]);
    $spirits = Category::factory()->create([
        'name' => 'Spirits',
        'slug' => 'spirits',
        'is_enabled' => true,
        'parent_id' => null,
    ]);

    $cabernet = Product::factory()->standard()->create([
        'name' => 'Cabernet',
        'sku' => 'SKU-CABERNET',
    ]);
    $cabernet->categories()->attach($red);

    $vodka = Product::factory()->standard()->create([
        'name' => 'Vodka',
        'sku' => 'SKU-VODKA',
    ]);
    $vodka->categories()->attach($spirits);

    Livewire::actingAs($this->admin)
        ->test(Index::class)
        ->assertSuccessful()
        ->assertCanRenderTableColumn('categories.name')
        ->assertSee('Wine / Red')
        ->assertSee('Spirits')
        ->assertCanSeeTableRecords([$cabernet, $vodka])
        ->assertTableFilterExists('categories')
        ->filterTable('categories', $red)
        ->call('applyTableFilters')
        ->assertCanSeeTableRecords([$cabernet])
        ->assertCanNotSeeTableRecords([$vodka]);
});
