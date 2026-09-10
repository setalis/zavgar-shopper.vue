<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shop;

use App\Enums\HomepageBannerPlacement;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Collection;
use App\Models\HomepageBanner;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Inertia\Inertia;
use Inertia\Response;

final class HomeController extends Controller
{
    /**
     * Columns the storefront product card needs: identity, brand, plus the
     * stock and type flags that drive its availability badge and CTA.
     *
     * @var list<string>
     */
    private const array CARD_COLUMNS = [
        'id',
        'name',
        'slug',
        'brand_id',
        'type',
        'featured',
        'security_stock',
        'allow_backorder',
    ];

    public function __invoke(): Response
    {
        return Inertia::render('shop/home', [
            'bentoBanners' => $this->bannersFor(HomepageBannerPlacement::Bento),
            'promoBanners' => $this->bannersFor(HomepageBannerPlacement::Promo),
            'featuredProducts' => fn () => localize_storefront($this->cardQuery()
                ->where('featured', true)
                ->limit(10)
                ->get()),
            'latestProducts' => fn () => localize_storefront($this->cardQuery()
                ->latest()
                ->limit(10)
                ->get()),
            'featuredCollections' => fn () => localize_storefront(Collection::query()
                ->has('products')
                ->withStorefrontTranslations()
                ->withCount('products')
                ->with('media')
                ->orderByDesc('products_count')
                ->limit(6)
                ->get()),
            'categories' => fn () => localize_storefront(Category::hydrateBranchProductsCount(
                Category::query()
                    ->scopes('enabled')
                    ->whereNull('parent_id')
                    ->withStorefrontTranslations()
                    ->with('media')
                    ->orderBy('position')
                    ->limit(10)
                    ->get(),
            )),
        ]);
    }

    private function cardQuery(): Builder
    {
        return Product::query()
            ->select(self::CARD_COLUMNS)
            ->with(['media', 'brand.media'])
            ->withStorefrontTranslations()
            ->withCurrentPrices()
            ->withCurrentStock()
            ->withApprovedReviewSummary()
            ->scopes('publish');
    }

    /**
     * @return \Closure(): list<array<string, mixed>>
     */
    private function bannersFor(HomepageBannerPlacement $placement): \Closure
    {
        return fn () => HomepageBanner::query()
            ->enabled()
            ->placement($placement)
            ->withStorefrontTranslations()
            ->with(['media', 'category', 'product', 'collection', 'brand'])
            ->orderBy('position')
            ->get()
            ->map(fn (HomepageBanner $banner): array => $banner->toStorefrontArray())
            ->values()
            ->all();
    }
}
