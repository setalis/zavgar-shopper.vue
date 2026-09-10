<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Actions\FlushStorefrontCategoryCache;
use App\Actions\FlushStorefrontMenuCache;
use App\Actions\GetCountriesByZone;
use App\Actions\Wishlist\WishlistManager;
use App\Actions\ZoneSessionManager;
use App\Models\Category;
use App\Models\Channel;
use App\Models\MenuItem;
use App\Support\StorefrontLocale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Middleware;
use Shopper\Cart\CartSessionManager;

class HandleInertiaRequests extends Middleware
{
    /**
     * @var string
     */
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user()?->append('full_name'),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'locale' => fn (): string => app()->getLocale(),
            'default_locale' => fn (): string => StorefrontLocale::default(),
            'locales' => fn (): array => config('app.available_locales', []),
            'locale_urls' => fn (): array => StorefrontLocale::switchUrls($request),
            'translations' => fn (): array => $this->frontendTranslations(),
            'shop' => fn (): array => $this->shopProps(),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function frontendTranslations(): array
    {
        $locale = app()->getLocale();
        $path = lang_path("frontend/{$locale}.json");

        if (! is_file($path)) {
            return [];
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            return [];
        }

        return json_decode($contents, true) ?? [];
    }

    /**
     * @return array<string, mixed>
     */
    private function shopProps(): array
    {
        $cart = resolve(CartSessionManager::class)->current();
        $zone = ZoneSessionManager::ensureDefaultSession();
        $wishlistIds = resolve(WishlistManager::class)->ids();

        return [
            'cart_count' => $cart?->lines->sum('quantity') ?? 0,
            'wishlist_count' => count($wishlistIds),
            'wishlist_ids' => $wishlistIds,
            'zone' => $zone ? [
                'country_code' => $zone->countryCode,
                'country_name' => $zone->countryName,
                'currency_code' => $zone->currencyCode,
                'zone_id' => $zone->zoneId,
            ] : null,
            'currency' => current_currency(),
            'channels' => Channel::query()
                ->scopes('enabled')
                ->select('id', 'name', 'slug')
                ->get()
                ->toArray(),
            'available_zones' => fn (): array => resolve(GetCountriesByZone::class)->handle()->values()->toArray(),
            'tax_label' => current_tax_label(),
            'logo' => storefront_logo_url(),
            'nav_categories' => $this->navCategories(),
            'nav_menu' => $this->navMenu(),
            'footer_categories' => $this->topCategories(FlushStorefrontCategoryCache::FOOTER_LIMIT, 'footer'),
        ];
    }

    /**
     * @return array<int, array{id: int, name: string, slug: string, thumbnail: ?string, children: array<int, array{id: int, name: string, slug: string}>}>
     */
    private function navCategories(): array
    {
        return Cache::remember(
            FlushStorefrontCategoryCache::navKey(app()->getLocale()),
            FlushStorefrontCategoryCache::CACHE_TTL,
            fn (): array => Category::query()
                ->scopes('enabled')
                ->whereNull('parent_id')
                ->withStorefrontTranslations()
                ->with([
                    'media',
                    'children' => fn ($query) => $query
                        ->scopes('enabled')
                        ->withStorefrontTranslations()
                        ->with('media')
                        ->orderBy('position'),
                ])
                ->orderBy('position')
                ->get(['id', 'name', 'slug'])
                ->map(function (Category $category): array {
                    $category->localizeForStorefront();

                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'slug' => $category->slug,
                        'thumbnail' => $category->getFirstMedia(
                            (string) config('shopper.media.storage.thumbnail_collection', 'thumbnail'),
                        )?->getUrl(),
                        'children' => $category->children
                            ->map(function (Category $child): array {
                                $child->localizeForStorefront();

                                return [
                                    'id' => $child->id,
                                    'name' => $child->name,
                                    'slug' => $child->slug,
                                ];
                            })
                            ->values()
                            ->all(),
                    ];
                })
                ->all(),
        );
    }

    /**
     * @return list<array{id: int, title: string, href: string, children: list<mixed>}>
     */
    private function navMenu(): array
    {
        return Cache::remember(
            FlushStorefrontMenuCache::navKey(app()->getLocale()),
            FlushStorefrontMenuCache::CACHE_TTL,
            function (): array {
                $targets = ['brand', 'category', 'product', 'collection'];

                return MenuItem::query()
                    ->scopes('enabled')
                    ->roots()
                    ->with([
                        ...$targets,
                        'children' => fn ($query) => $query
                            ->scopes('enabled')
                            ->orderBy('position')
                            ->with([
                                ...$targets,
                                'children' => fn ($query) => $query
                                    ->scopes('enabled')
                                    ->orderBy('position')
                                    ->with($targets),
                            ]),
                    ])
                    ->orderBy('position')
                    ->get()
                    ->map(fn (MenuItem $item): ?array => $item->toNavArray())
                    ->filter()
                    ->values()
                    ->all();
            },
        );
    }

    /**
     * @return array<int, array{id: int, name: string, slug: string}>
     */
    private function topCategories(int $limit, string $cacheKey): array
    {
        $locale = app()->getLocale();
        $key = $cacheKey === 'footer'
            ? FlushStorefrontCategoryCache::footerKey($locale, $limit)
            : "{$cacheKey}.categories.{$locale}.{$limit}";

        return Cache::remember(
            $key,
            FlushStorefrontCategoryCache::CACHE_TTL,
            fn (): array => Category::query()
                ->scopes('enabled')
                ->whereNull('parent_id')
                ->withStorefrontTranslations()
                ->orderBy('position')
                ->take($limit)
                ->get(['id', 'name', 'slug'])
                ->map(function (Category $category): array {
                    $category->localizeForStorefront();

                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'slug' => $category->slug,
                    ];
                })
                ->all(),
        );
    }
}
