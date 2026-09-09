<?php

declare(strict_types=1);

use App\Livewire\Shopper\Pages\Settings\General;
use App\Models\User;
use App\Support\SettingMediaPath;
use Filament\Forms\Components\FileUpload;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Shopper\Core\Models\Currency;
use Shopper\Core\Models\Setting;
use Shopper\Database\Seeders\AuthTableSeeder;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(AuthTableSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole(config('shopper.admin.roles.admin'));

    $this->disk = (string) config('shopper.media.storage.disk_name');
    Storage::fake($this->disk);
    Cache::forget('shopper-setting.logo');
});

function fillRequiredGeneralSettings(mixed $test): mixed
{
    $currency = Currency::query()->create([
        'name' => 'US Dollar',
        'code' => 'USD',
        'symbol' => '$',
        'format' => '$1,234.56',
        'is_enabled' => true,
    ]);

    return $test
        ->set('data.name', 'ZAVGAR')
        ->set('data.email', 'shop@example.com')
        ->set('data.legal_name', 'ZAVGAR LLC')
        ->set('data.street_address', 'Street 1')
        ->set('data.city', 'Kyiv')
        ->set('data.postal_code', '01001')
        ->set('data.currencies', [$currency->id])
        ->set('data.default_currency_id', $currency->id);
}

function storedLogoPath(): ?string
{
    $setting = Setting::query()->where('key', 'logo')->first();

    return SettingMediaPath::path($setting?->value);
}

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

test('general settings form hydrates an existing logo file', function (): void {
    $path = 'old-logo.png';
    Storage::disk($this->disk)->put($path, 'old-logo');

    Setting::query()->updateOrCreate(['key' => 'logo'], [
        'value' => $path,
        'display_name' => Setting::lockedAttributesDisplayName('logo'),
        'locked' => true,
    ]);

    $component = Livewire::actingAs($this->admin)
        ->test(General::class)
        ->assertSuccessful();

    expect(SettingMediaPath::path($component->get('data.logo')))->toBe($path);
});

test('replacing a logo stores the new file path instead of the old one', function (): void {
    $oldPath = 'old-logo.png';
    Storage::disk($this->disk)->put($oldPath, 'old-logo');

    Setting::query()->updateOrCreate(['key' => 'logo'], [
        'value' => $oldPath,
        'display_name' => Setting::lockedAttributesDisplayName('logo'),
        'locked' => true,
    ]);

    $component = fillRequiredGeneralSettings(
        Livewire::actingAs($this->admin)->test(General::class),
    );

    $newFile = UploadedFile::fake()->image('new-logo.png', 400, 120);

    $component->set('data.logo', $newFile);

    $instance = $component->instance();
    $instance->data['logo'] = [
        'existing-logo' => $oldPath,
        ...Arr::wrap($instance->data['logo']),
    ];

    $component
        ->call('store')
        ->assertHasNoErrors();

    $path = storedLogoPath();

    expect($path)->not->toBeNull()
        ->and($path)->not->toBe($oldPath);

    Storage::disk($this->disk)->assertExists($path);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('shop.logo', shopper_asset($path))
        );
});

test('first logo upload writes the stored path to settings', function (): void {
    $component = fillRequiredGeneralSettings(
        Livewire::actingAs($this->admin)->test(General::class),
    );

    $component
        ->set('data.logo', UploadedFile::fake()->image('first-logo.png', 400, 120))
        ->call('store')
        ->assertHasNoErrors();

    $path = storedLogoPath();

    expect($path)->not->toBeNull()
        ->and($path)->not->toBe('');

    Storage::disk($this->disk)->assertExists($path);
});
