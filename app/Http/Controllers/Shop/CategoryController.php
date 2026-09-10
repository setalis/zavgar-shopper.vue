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
            'categories' => localize_storefront(Category::hydrateBranchProductsCount(
                Category::query()
                    ->scopes('enabled')
                    ->whereNull('parent_id')
                    ->withStorefrontTranslations()
                    ->with('media')
                    ->orderBy('position')
                    ->get(),
            )),
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
            ->withStorefrontTranslations()
            ->withCurrentPrices()
            ->withCurrentStock()
            ->withApprovedReviewSummary();

        $query = match ($sort) {
            'name' => $query->orderByLocalizedName(),
            default => $query->latest(),
        };

        $category->load(['media', 'translations']);
        $category->localizeForStorefront();

        return Inertia::render('shop/category', [
            'category' => $category,
            'children' => localize_storefront(Category::hydrateBranchProductsCount(
                $category->children()
                    ->scopes('enabled')
                    ->withStorefrontTranslations()
                    ->with('media')
                    ->orderBy('position')
                    ->get(),
            )),
            'products' => localize_storefront($query->paginate(12)->withQueryString()),
            'attributeFilters' => $attributeFilters,
            'priceRange' => $priceRange,
            'filters' => [
                'sort' => $sort,
                'attrs' => (object) $selectedAttrs,
                'price_min' => $price['min'],
                'price_max' => $price['max'],
            ],
            'hreflang' => storefront_hreflang('shop.category', ['category' => $category]),
        ]);
    }
}
