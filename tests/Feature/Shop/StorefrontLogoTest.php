<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Shopper\Core\Models\Setting;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Storage::fake(config('shopper.media.storage.disk_name'));
    Cache::forget('shopper-setting.logo');
});

test('home page shares null logo when shop logo setting is empty', function (): void {
    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('shop.logo', null)
        );
});

test('home page shares shop logo url from admin settings', function (): void {
    $disk = config('shopper.media.storage.disk_name');
    $path = 'store-logo.png';

    Storage::disk($disk)->put($path, 'fake-logo');

    Setting::query()->updateOrCreate(['key' => 'logo'], [
        'value' => $path,
        'display_name' => Setting::lockedAttributesDisplayName('logo'),
        'locked' => true,
    ]);

    Cache::forget('shopper-setting.logo');

    $expected = shopper_asset($path);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('shop.logo', $expected)
        );
});

test('storefront logo url helper resolves filament array value', function (): void {
    $disk = config('shopper.media.storage.disk_name');
    $path = 'logo-from-array.jpg';

    Storage::disk($disk)->put($path, 'fake-logo');

    Setting::query()->updateOrCreate(['key' => 'logo'], [
        'value' => [$path],
        'display_name' => Setting::lockedAttributesDisplayName('logo'),
        'locked' => true,
    ]);

    Cache::forget('shopper-setting.logo');

    expect(storefront_logo_url())->toBe(shopper_asset($path));
});
