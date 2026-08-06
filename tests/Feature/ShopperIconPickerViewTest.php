<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Shopper\Database\Seeders\AuthTableSeeder;
use Shopper\Livewire\SlideOvers\AttributeForm;

uses(RefreshDatabase::class);

test('attribute form slide over renders icon picker without missing view errors', function (): void {
    $this->seed(AuthTableSeeder::class);

    $user = User::factory()->create();
    $user->assignRole(config('shopper.admin.roles.admin'));

    Livewire::actingAs($user)
        ->test(AttributeForm::class)
        ->assertSuccessful()
        ->assertSee('fi-fo-select', false);
});
