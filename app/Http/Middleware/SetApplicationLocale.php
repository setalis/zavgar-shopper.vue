<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class SetApplicationLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $available = array_keys(config('app.available_locales', []));

        $locale = session('locale', session('shopper_locale', config('app.locale')));

        if (in_array($locale, $available, strict: true)) {
            app()->setLocale($locale);

            if (session('locale') !== $locale || session('shopper_locale') !== $locale) {
                session(['locale' => $locale, 'shopper_locale' => $locale]);
            }
        }

        return $next($request);
    }
}
