<?php

declare(strict_types=1);

use App\Livewire\Shopper\SlideOvers\AttributeForm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Shopper\Core\Models\Attribute;
use Shopper\Database\Seeders\AuthTableSeeder;

uses(RefreshDatabase::class);

test('registered shopper attribute form alias uses text input for icon', function (): void {
    $this->seed(AuthTableSeeder::class);

    $user = User::factory()->create();
    $user->assignRole(config('shopper.admin.roles.admin'));

    Livewire::actingAs($user)
        ->test('shopper-slide-overs.attribute-form')
        ->assertSuccessful()
        ->assertSee('Phosphor icon name', false)
        ->assertSeeHtml('id="form.icon"');
});

test('attribute form slide over renders without icon picker memory issues', function (): void {
    $this->seed(AuthTableSeeder::class);

    $user = User::factory()->create();
    $user->assignRole(config('shopper.admin.roles.admin'));

    Livewire::actingAs($user)
        ->test(AttributeForm::class)
        ->assertSuccessful()
        ->assertSee('Phosphor icon name', false)
        ->assertSeeHtml('id="form.icon"');
});

test('attribute form can be updated via livewire', function (): void {
    $this->seed(AuthTableSeeder::class);

    $user = User::factory()->create();
    $user->assignRole(config('shopper.admin.roles.admin'));

    $attribute = Attribute::query()->create([
        'name' => 'Color',
        'slug' => 'color',
        'type' => 'text',
        'icon' => 'phosphor-palette',
        'is_enabled' => true,
        'is_searchable' => false,
        'is_filterable' => true,
    ]);

    Livewire::actingAs($user)
        ->test(AttributeForm::class, ['attributeId' => $attribute->id])
        ->set('data.name', 'Colour')
        ->call('store')
        ->assertHasNoErrors();

    expect($attribute->fresh()->name)->toBe('Colour');
});
