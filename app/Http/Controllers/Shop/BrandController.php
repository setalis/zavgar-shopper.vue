<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shop;

use App\Actions\Product\FilterByStorefrontPrice;
use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\IndexBrandsRequest;
use App\Http\Requests\Shop\StorefrontPriceFilter;
use App\Models\Brand;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class BrandController extends Controller
{
    public function index(IndexBrandsRequest $request): Response
    {
        $listing = Brand::query()
            ->scopes('enabled')
            ->with('media')
            ->withCount([
                'products' => fn (Builder $query): Builder => $query->scopes('publish'),
            ]);

        $availableLetters = Brand::query()
            ->scopes('enabled')
            ->when(
                $request->search() !== '',
                fn (Builder $query): Builder => $query->where(
                    'name',
                    'like',
                    '%'.addcslashes($request->search(), '%_\\').'%',
                ),
            )
            ->orderBy('name')
            ->pluck('name')
            ->map(fn (string $name): string => IndexBrandsRequest::letterKey($name))
            ->unique()
            ->values()
            ->all();

        $sort = $request->sort();
        $listing = $request->apply($listing);

        $listing = match ($sort) {
            'latest' => $listing->latest(),
            'products' => $listing->orderByDesc('products_count')->orderBy('name'),
            default => $listing->orderBy('name'),
        };

        return Inertia::render('shop/brands', [
            'brands' => $listing->paginate(24)->withQueryString(),
            'availableLetters' => $availableLetters,
            'filters' => [
                'q' => $request->search(),
                'letter' => $request->letter(),
                'sort' => $sort,
                'with_products' => $request->withProducts(),
            ],
        ]);
    }

    public function show(Request $request, Brand $brand, FilterByStorefrontPrice $filterByStorefrontPrice): Response
    {
        abort_unless($brand->is_enabled, 404);

        $price = StorefrontPriceFilter::fromRequest($request);
        $sort = (string) $request->string('sort', 'latest');

        $query = $brand->products()->scopes('publish');

        $priceRange = $filterByStorefrontPrice->bounds($query);
        $query = $filterByStorefrontPrice->apply($query, $price['min'], $price['max'])
            ->with(['media', 'brand.media'])
            ->withStorefrontTranslations()
            ->withCurrentPrices()
            ->withCurrentStock()
            ->withApprovedReviewSummary();

        $query = match ($sort) {
            'name' => $query->orderByLocalizedName(),
            default => $query->latest(),
        };

        return Inertia::render('shop/brand', [
            'brand' => $brand->load('media'),
            'products' => localize_storefront($query->paginate(12)->withQueryString()),
            'priceRange' => $priceRange,
            'filters' => [
                'sort' => $sort,
                'price_min' => $price['min'],
                'price_max' => $price['max'],
            ],
        ]);
    }
}
