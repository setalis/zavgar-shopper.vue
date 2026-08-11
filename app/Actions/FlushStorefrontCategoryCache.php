<?php

declare(strict_types=1);

namespace App\Actions;

use Illuminate\Support\Facades\Cache;

final class FlushStorefrontCategoryCache
{
    public const int FOOTER_LIMIT = 6;

    public const int CACHE_TTL = 7200;

    public static function navKey(string $locale): string
    {
        return 'nav.categories.tree.'.$locale;
    }

    public static function footerKey(string $locale, ?int $limit = null): string
    {
        return 'footer.categories.'.$locale.'.'.($limit ?? self::FOOTER_LIMIT);
    }

    public static function flush(): void
    {
        $locales = array_keys(config('app.available_locales', []));

        if ($locales === []) {
            $locales = [app()->getLocale()];
        }

        foreach ($locales as $locale) {
            $locale = (string) $locale;

            Cache::forget(self::navKey($locale));
            Cache::forget(self::footerKey($locale));
            Cache::forget('nav.categories.tree.'.$locale.'.4');
            Cache::forget('nav.categories.'.$locale.'.4');
        }
    }

    public function handle(): void
    {
        self::flush();
    }
}
