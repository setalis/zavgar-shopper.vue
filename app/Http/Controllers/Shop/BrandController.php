<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shop;

use App\Actions\Product\FilterByStorefrontPrice;
use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\StorefrontPriceFilter;
use App\Models\Brand;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class BrandController extends Controller
{
    public function __invoke(Request $request, Brand $brand, FilterByStorefrontPrice $filterByStorefrontPrice): Response
    {
        abort_unless($brand->is_enabled, 404);

        $price = StorefrontPriceFilter::fromRequest($request);
        $sort = (string) $request->string('sort', 'latest');

        $query = $brand->products()->scopes('publish');

        $priceRange = $filterByStorefrontPrice->bounds($query);
        $query = $filterByStorefrontPrice->apply($query, $price['min'], $price['max'])
            ->with(['media', 'brand.media'])
            ->withCurrentPrices()
            ->withCurrentStock()
            ->withApprovedReviewSummary();

        $query = match ($sort) {
            'name' => $query->orderBy('name'),
            default => $query->latest(),
        };

        return Inertia::render('shop/brand', [
            'brand' => $brand->load('media'),
            'products' => $query->paginate(12)->withQueryString(),
            'priceRange' => $priceRange,
            'filters' => [
                'sort' => $sort,
                'price_min' => $price['min'],
                'price_max' => $price['max'],
            ],
        ]);
    }
}
