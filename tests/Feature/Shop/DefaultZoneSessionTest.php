<?php

declare(strict_types=1);

use App\Actions\GetCountriesByZone;
use App\Actions\ZoneSessionManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Shopper\Core\Models\Country;
use Shopper\Core\Models\Currency;
use Shopper\Core\Models\Zone;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->currency = Currency::query()->create([
        'name' => 'US Dollar',
        'code' => 'USD',
        'symbol' => '$',
        'format' => '$1,234.56',
    ]);

    GetCountriesByZone::flush();
});

/**
 * @return array{0: Zone, 1: Country}
 */
function createEnabledZoneWithCountry(Currency $currency, string $cca2 = 'UA', string $zoneName = 'Ukraine'): array
{
    $zone = Zone::factory()->create([
        'name' => $zoneName,
        'currency_id' => $currency->id,
        'is_enabled' => true,
    ]);

    $country = Country::factory()->create([
        'name' => $cca2 === 'UA' ? 'Ukraine' : "Country {$cca2}",
        'cca2' => $cca2,
        'cca3' => $cca2.'X',
    ]);

    $zone->countries()->attach($country->id);

    GetCountriesByZone::flush();

    return [$zone, $country];
}

test('single available country is selected as the default zone session', function (): void {
    [, $country] = createEnabledZoneWithCountry($this->currency);

    expect(ZoneSessionManager::getSession())->toBeNull();

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('shop.zone.country_code', $country->cca2)
            ->where('shop.zone.country_name', $country->name)
        );

    expect(session('zone_country_code'))->toBe($country->cca2);
    expect(ZoneSessionManager::getSession()?->countryCode)->toBe($country->cca2);
});

test('multiple available countries do not auto-select a default zone', function (): void {
    createEnabledZoneWithCountry($this->currency, 'UA', 'Ukraine');
    createEnabledZoneWithCountry($this->currency, 'PL', 'Poland');

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('shop.zone', null)
        );

    expect(session('zone_country_code'))->toBeNull();
    expect(ZoneSessionManager::getSession())->toBeNull();
});

test('ensureDefaultSession keeps an already selected zone', function (): void {
    [, $first] = createEnabledZoneWithCountry($this->currency, 'UA', 'Ukraine');
    createEnabledZoneWithCountry($this->currency, 'PL', 'Poland');

    ZoneSessionManager::setSessionForCountryCode($first->cca2);

    $zone = ZoneSessionManager::ensureDefaultSession();

    expect($zone?->countryCode)->toBe($first->cca2);
});
