<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shop;

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
            'bentoBanners' => fn () => HomepageBanner::query()
                ->enabled()
                ->with(['media', 'category', 'product', 'collection', 'brand'])
                ->orderBy('position')
                ->get()
                ->map(fn (HomepageBanner $banner): array => $banner->toStorefrontArray())
                ->values()
                ->all(),
            'featuredProducts' => fn () => $this->cardQuery()
                ->where('featured', true)
                ->limit(10)
                ->get(),
            'latestProducts' => fn () => $this->cardQuery()
                ->latest()
                ->limit(10)
                ->get(),
            'featuredCollections' => fn () => Collection::query()
                ->has('products')
                ->withCount('products')
                ->with('media')
                ->orderByDesc('products_count')
                ->limit(6)
                ->get(),
            'categories' => fn () => Category::hydrateBranchProductsCount(
                Category::query()
                    ->scopes('enabled')
                    ->whereNull('parent_id')
                    ->with('media')
                    ->orderBy('position')
                    ->limit(10)
                    ->get(),
            ),
        ]);
    }

    private function cardQuery(): Builder
    {
        return Product::query()
            ->select(self::CARD_COLUMNS)
            ->with(['media', 'brand.media'])
            ->withCurrentPrices()
            ->withCurrentStock()
            ->withApprovedReviewSummary()
            ->scopes('publish');
    }
}
