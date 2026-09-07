<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shop;

use App\Actions\Product\FilterByStorefrontPrice;
use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\StorefrontPriceFilter;
use App\Models\Collection;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class CollectionController extends Controller
{
    public function show(Request $request, Collection $collection, FilterByStorefrontPrice $filterByStorefrontPrice): Response
    {
        abort_unless(
            $collection->published_at !== null && $collection->published_at->lte(now()),
            404,
        );

        $price = StorefrontPriceFilter::fromRequest($request);
        $sort = (string) $request->string('sort', 'latest');

        $query = $collection->products()->scopes('publish');

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

        return Inertia::render('shop/collection', [
            'collection' => $collection->load('media'),
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
