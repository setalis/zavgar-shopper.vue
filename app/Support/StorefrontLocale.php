<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;

final class StorefrontLocale
{
    private static ?string $urlLocale = null;

    public static function default(): string
    {
        return (string) config('app.storefront_locale', 'uk');
    }

    /**
     * @return list<string>
     */
    public static function available(): array
    {
        return array_keys(config('app.available_locales', []));
    }

    /**
     * @return list<string>
     */
    public static function nonDefault(): array
    {
        $default = self::default();

        return array_values(array_filter(
            self::available(),
            fn (string $locale): bool => $locale !== $default,
        ));
    }

    public static function isDefault(?string $locale = null): bool
    {
        return ($locale ?? app()->getLocale()) === self::default();
    }

    public static function current(): string
    {
        return app()->getLocale();
    }

    public static function applyUrlDefaults(?string $locale = null): void
    {
        self::$urlLocale = $locale ?? app()->getLocale();
    }

    /**
     * @return array<string, string>
     */
    public static function switchUrls(Request $request): array
    {
        $path = $request->getPathInfo();
        $query = $request->getQueryString();
        $suffix = $query ? '?'.$query : '';
        $storefront = self::isStorefrontRequest($request);
        $urls = [];

        foreach (self::available() as $locale) {
            $localized = $storefront
                ? self::withLocalePrefix($path, $locale)
                : ($path === '' ? '/' : $path);

            $urls[$locale] = $localized.$suffix;
        }

        return $urls;
    }

    /**
     * @param  array<string, mixed>|object|int|string  $parameters
     * @return list<array{locale: string, url: string}>
     */
    public static function hreflang(string $route, mixed $parameters = []): array
    {
        $previous = self::$urlLocale;
        $links = [];

        foreach (self::available() as $locale) {
            self::$urlLocale = $locale;

            $links[] = [
                'locale' => $locale,
                'url' => route($route, $parameters, true),
            ];
        }

        self::$urlLocale = $previous;

        return $links;
    }

    public static function prefixPath(string $path, mixed $route = null): string
    {
        $locale = self::$urlLocale ?? app()->getLocale();

        if ($locale === '' || $locale === null || $locale === self::default() || ! self::shouldPrefixRoute($route)) {
            return $path === '' ? '/' : $path;
        }

        return self::withLocalePrefix($path, $locale);
    }

    public static function withLocalePrefix(string $path, string $locale): string
    {
        $normalized = '/'.trim($path, '/');

        foreach (self::nonDefault() as $prefix) {
            if ($normalized === '/'.$prefix) {
                $normalized = '/';
                break;
            }

            if (str_starts_with($normalized, '/'.$prefix.'/')) {
                $normalized = substr($normalized, strlen($prefix) + 1) ?: '/';
                break;
            }
        }

        if ($locale === self::default()) {
            return $normalized === '' ? '/' : $normalized;
        }

        if ($normalized === '/') {
            return '/'.$locale;
        }

        return '/'.$locale.$normalized;
    }

    public static function isStorefrontRequest(Request $request): bool
    {
        if ($request->is('cpanel', 'cpanel/*', 'livewire/*', 'webhooks/*', 'up')) {
            return false;
        }

        $segment = $request->segment(1);

        if ($segment === null) {
            return true;
        }

        if (in_array($segment, self::available(), true)) {
            return true;
        }

        return in_array($segment, [
            'shop',
            'categories',
            'collections',
            'brands',
            'search',
            'contact',
            'cart',
            'wishlist',
            'checkout',
            'account',
            'settings',
            'dashboard',
        ], true);
    }

    private static function shouldPrefixRoute(mixed $route): bool
    {
        if (! $route instanceof Route) {
            return false;
        }

        $name = $route->getName();

        if (! is_string($name) || $name === '') {
            return false;
        }

        foreach (['shopper.', 'login', 'register', 'password.', 'verification.', 'two-factor.', 'webhooks.', 'locale.update'] as $skip) {
            if ($name === rtrim($skip, '.') || str_starts_with($name, $skip)) {
                return false;
            }
        }

        return true;
    }
}
