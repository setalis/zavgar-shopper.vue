<?php

declare(strict_types=1);

use App\Livewire\Shopper\Pages\Settings\General;
use App\Models\User;
use Filament\Forms\Components\FileUpload;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Shopper\Database\Seeders\AuthTableSeeder;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(AuthTableSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole(config('shopper.admin.roles.admin'));
});

test('shopper general settings page uses the app override', function (): void {
    expect(config('shopper.components.setting.pages.general'))->toBe(General::class);

    $route = app('router')->getRoutes()->getByName('shopper.settings.shop');

    expect($route)->not->toBeNull()
        ->and($route->getActionName())->toBe(General::class);
});

test('general settings logo upload is not cropped as an avatar', function (): void {
    $component = Livewire::actingAs($this->admin)
        ->test(General::class)
        ->assertSuccessful();

    $logo = $component->instance()->form->getComponent(
        fn (mixed $component): bool => $component instanceof FileUpload
            && $component->getName() === 'logo',
    );

    expect($logo)->toBeInstanceOf(FileUpload::class)
        ->and($logo->isAvatar())->toBeFalse()
        ->and($logo->shouldAutomaticallyCropImagesToAspectRatio())->toBeFalse()
        ->and($logo->getImageAspectRatio())->toBeNull()
        ->and($logo->getAutomaticallyResizeImagesHeight())->toBeNull()
        ->and($logo->getAutomaticallyResizeImagesWidth())->toBeNull()
        ->and($logo->getAutomaticallyResizeImagesMode())->toBeNull();
});

test('shopper-general livewire alias resolves to the app override', function (): void {
    $component = Livewire::actingAs($this->admin)
        ->test('shopper-general')
        ->assertSuccessful();

    expect($component->instance())->toBeInstanceOf(General::class);
});
