<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shop;

use App\Actions\Product\ApplyCategoryAttributeFilters;
use App\Actions\Product\BuildCategoryAttributeFilters;
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
    ): Response {
        $sort = $request->sort();
        $selectedAttrs = $request->selectedAttrs();
        $attributeFilters = $buildCategoryAttributeFilters->handle($category);

        $query = Product::query()
            ->scopes('publish')
            ->whereHas('categories', fn ($q) => $q->where('id', $category->id))
            ->with(['media', 'brand.media'])
            ->withCurrentPrices()
            ->withCurrentStock()
            ->withApprovedReviewSummary();

        $query = $applyCategoryAttributeFilters->handle($query, $selectedAttrs, $attributeFilters);

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
            'filters' => [
                'sort' => $sort,
                'attrs' => (object) $selectedAttrs,
            ],
        ]);
    }
}
