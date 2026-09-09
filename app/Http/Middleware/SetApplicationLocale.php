<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\StorefrontLocale;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class SetApplicationLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $available = StorefrontLocale::available();
        $default = StorefrontLocale::default();
        $fromPrefix = $request->attributes->get('storefront_locale');

        $locale = $default;

        if (is_string($fromPrefix) && in_array($fromPrefix, $available, true)) {
            $locale = $fromPrefix;
        } elseif (! StorefrontLocale::isStorefrontRequest($request)) {
            $sessionLocale = session('locale', session('shopper_locale', $default));

            if (is_string($sessionLocale) && in_array($sessionLocale, $available, true)) {
                $locale = $sessionLocale;
            }
        }

        if (in_array($locale, $available, true)) {
            app()->setLocale($locale);
            session(['locale' => $locale, 'shopper_locale' => $locale]);
        }

        StorefrontLocale::applyUrlDefaults($locale);

        return $next($request);
    }
}
