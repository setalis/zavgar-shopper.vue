<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class SearchSuggestController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'min:3', 'max:100'],
        ]);

        $products = Product::query()
            ->scopes('publish')
            ->matchingSearch($validated['q'])
            ->with(['media'])
            ->withCurrentPrices()
            ->limit(8)
            ->get()
            ->map(fn (Product $product): array => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'sku' => $product->sku,
                'thumbnail' => $product->thumbnail,
                'storefront_price' => $product->storefront_price,
            ])
            ->values()
            ->all();

        return response()->json([
            'products' => $products,
        ]);
    }
}
