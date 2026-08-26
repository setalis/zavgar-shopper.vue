<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class BrandController extends Controller
{
    public function __invoke(Request $request, Brand $brand): Response
    {
        abort_unless($brand->is_enabled, 404);

        $sort = (string) $request->string('sort', 'latest');

        $query = $brand->products()
            ->scopes('publish')
            ->with(['media', 'brand.media'])
            ->withCurrentPrices()
            ->withCurrentStock();

        $query = match ($sort) {
            'name' => $query->orderBy('name'),
            default => $query->latest(),
        };

        return Inertia::render('shop/brand', [
            'brand' => $brand->load('media'),
            'products' => $query->paginate(12)->withQueryString(),
            'filters' => ['sort' => $sort],
        ]);
    }
}
