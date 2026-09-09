<?php

declare(strict_types=1);

use App\Actions\LocalizeCatalog;
use App\Actions\ZoneSessionManager;
use App\DTO\CountryByZoneData;
use App\Models\Channel;
use App\Support\StorefrontLocale;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Enumerable;
use Shopper\Cart\CartSessionManager;
use Shopper\Cart\Models\Cart;
use Shopper\Core\Models\TaxZone;

if (! function_exists('cartSession')) {
    function cartSession(): Cart
    {
        $session = resolve(CartSessionManager::class);
        $cart = $session->current();

        if (! $cart) {
            $zone = ZoneSessionManager::getSession();
            $defaultChannel = Channel::query()->scopes('default')->first();

            $cart = $session->create([
                'currency_code' => current_currency(),
                'channel_id' => $defaultChannel?->id,
                'zone_id' => $zone?->zoneId,
                'customer_id' => auth()->id(),
            ]);
        }

        return $cart;
    }
}

if (! function_exists('current_currency')) {
    function current_currency(): string
    {
        return ZoneSessionManager::getSession()?->currencyCode ?? shopper_currency();
    }
}

if (! function_exists('current_tax_label')) {
    function current_tax_label(): string
    {
        return once(function (): string {
            $zone = ZoneSessionManager::getSession();

            if (! $zone instanceof CountryByZoneData) {
                return '';
            }

            $taxZone = TaxZone::query()
                ->whereHas('country', fn ($q) => $q->where('cca2', $zone->countryCode))
                ->whereNull('province_code')
                ->first();

            return $taxZone?->is_tax_inclusive ? __('backend.tax.ttc') : __('backend.tax.ht');
        });
    }
}

if (! function_exists('storefront_logo_url')) {
    function storefront_logo_url(): ?string
    {
        $logo = shopper_setting('logo');

        if (blank($logo)) {
            return null;
        }

        $path = is_array($logo) ? (array_values($logo)[0] ?? null) : $logo;

        if (! is_string($path) || blank($path)) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return shopper_asset($path);
    }
}

if (! function_exists('localize_storefront')) {
    function localize_storefront(mixed $value): mixed
    {
        $localizer = resolve(LocalizeCatalog::class);

        if ($value instanceof Model) {
            if (method_exists($value, 'localizeForStorefront')) {
                return $value->localizeForStorefront();
            }

            return $localizer->handle($value);
        }

        if ($value instanceof AbstractPaginator) {
            $value->getCollection()->transform(function (mixed $item) use ($localizer): mixed {
                if ($item instanceof Model && method_exists($item, 'localizeForStorefront')) {
                    return $item->localizeForStorefront();
                }

                if ($item instanceof Model) {
                    return $localizer->handle($item);
                }

                return $item;
            });

            return $value;
        }

        if ($value instanceof Enumerable) {
            return $value->map(function (mixed $item) use ($localizer): mixed {
                if ($item instanceof Model && method_exists($item, 'localizeForStorefront')) {
                    return $item->localizeForStorefront();
                }

                if ($item instanceof Model) {
                    return $localizer->handle($item);
                }

                return $item;
            });
        }

        return $value;
    }
}

if (! function_exists('storefront_hreflang')) {
    /**
     * @param  array<string, mixed>|object|int|string  $parameters
     * @return list<array{locale: string, url: string}>
     */
    function storefront_hreflang(string $route, mixed $parameters = []): array
    {
        return StorefrontLocale::hreflang($route, $parameters);
    }
}
