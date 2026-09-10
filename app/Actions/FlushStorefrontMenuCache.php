<?php

declare(strict_types=1);

namespace App\Actions;

use Illuminate\Support\Facades\Cache;

final class FlushStorefrontMenuCache
{
    public const int CACHE_TTL = 7200;

    public static function navKey(string $locale): string
    {
        return 'nav.menu.tree.'.$locale;
    }

    public static function flush(): void
    {
        $locales = array_keys(config('app.available_locales', []));

        if ($locales === []) {
            $locales = [app()->getLocale()];
        }

        foreach ($locales as $locale) {
            Cache::forget(self::navKey((string) $locale));
        }
    }

    public function handle(): void
    {
        self::flush();
    }
}
