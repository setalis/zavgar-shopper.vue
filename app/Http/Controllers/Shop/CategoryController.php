<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shop;

use App\Actions\Product\ApplyCategoryAttributeFilters;
use App\Actions\Product\BuildCategoryAttributeFilters;
use App\Actions\Product\FilterByStorefrontPrice;
use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\ShowCategoryRequest;
use App\Models\Category;
use App\Models\Product;
use Inertia\Inertia;
use Inertia\Response;

final class CategoryController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('shop/categories', [
            'categories' => Category::hydrateBranchProductsCount(
                Category::query()
                    ->scopes('enabled')
                    ->whereNull('parent_id')
                    ->with('media')
                    ->orderBy('position')
                    ->get(),
            ),
        ]);
    }

    public function show(
        ShowCategoryRequest $request,
        Category $category,
        BuildCategoryAttributeFilters $buildCategoryAttributeFilters,
        ApplyCategoryAttributeFilters $applyCategoryAttributeFilters,
        FilterByStorefrontPrice $filterByStorefrontPrice,
    ): Response {
        $sort = $request->sort();
        $selectedAttrs = $request->selectedAttrs();
        $price = $request->priceRange();
        $attributeFilters = $buildCategoryAttributeFilters->handle($category);

        $query = Product::query()
            ->scopes('publish')
            ->whereHas('categories', fn ($q) => $q->where('id', $category->id));

        $query = $applyCategoryAttributeFilters->handle($query, $selectedAttrs, $attributeFilters);

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

        return Inertia::render('shop/category', [
            'category' => $category->load('media'),
            'children' => Category::hydrateBranchProductsCount(
                $category->children()
                    ->scopes('enabled')
                    ->with('media')
                    ->orderBy('position')
                    ->get(),
            ),
            'products' => $query->paginate(12)->withQueryString(),
            'attributeFilters' => $attributeFilters,
            'priceRange' => $priceRange,
            'filters' => [
                'sort' => $sort,
                'attrs' => (object) $selectedAttrs,
                'price_min' => $price['min'],
                'price_max' => $price['max'],
            ],
        ]);
    }
}
