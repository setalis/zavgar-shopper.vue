<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shop;

use App\Actions\Product\FilterByStorefrontPrice;
use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\StorefrontPriceFilter;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class SearchController extends Controller
{
    public function __invoke(Request $request, FilterByStorefrontPrice $filterByStorefrontPrice): Response
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'min:2', 'max:100'],
            ...StorefrontPriceFilter::rules(),
        ]);

        $price = StorefrontPriceFilter::normalized($validated);
        $query = (string) $request->string('q', '');

        $products = null;
        $priceRange = null;

        if (mb_strlen($query) >= 2) {
            $listing = Product::query()
                ->scopes('publish')
                ->matchingSearch($query);

            $priceRange = $filterByStorefrontPrice->bounds($listing);
            $products = localize_storefront($filterByStorefrontPrice->apply($listing, $price['min'], $price['max'])
                ->with(['media', 'brand.media'])
                ->withStorefrontTranslations()
                ->withCurrentPrices()
                ->withCurrentStock()
                ->withApprovedReviewSummary()
                ->paginate(12)
                ->withQueryString());
        }

        return Inertia::render('shop/search', [
            'query' => $query,
            'products' => $products,
            'priceRange' => $priceRange,
            'filters' => [
                'price_min' => $price['min'],
                'price_max' => $price['max'],
            ],
        ]);
    }
}
