<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shop;

use App\Actions\Product\BuildProductAttributes;
use App\Actions\Product\BuildVariantOptions;
use App\Actions\Product\FilterByStorefrontPrice;
use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\StorefrontPriceFilter;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class ProductController extends Controller
{
    public function index(Request $request, FilterByStorefrontPrice $filterByStorefrontPrice): Response
    {
        $price = StorefrontPriceFilter::fromRequest($request);

        $query = Product::query()->scopes('publish');

        $search = (string) $request->string('search', '');

        if ($search !== '') {
            $query->matchingSearch($search);
        }

        $categoryId = $request->integer('category') ?: null;
        $selectedCategory = null;
        $children = collect();

        if ($categoryId !== null) {
            $selectedCategory = Category::query()
                ->scopes('enabled')
                ->withStorefrontTranslations()
                ->where('id', $categoryId)
                ->first();

            if ($selectedCategory !== null) {
                $selectedCategory->localizeForStorefront();

                $query->whereHas(
                    'categories',
                    fn ($q) => $q->where('id', $selectedCategory->id),
                );

                $children = localize_storefront(Category::hydrateBranchProductsCount(
                    $selectedCategory->children()
                        ->scopes('enabled')
                        ->withStorefrontTranslations()
                        ->with('media')
                        ->orderBy('position')
                        ->get(),
                ));
            }
        }

        $priceRange = $filterByStorefrontPrice->bounds($query);
        $query = $filterByStorefrontPrice->apply($query, $price['min'], $price['max'])
            ->with(['media', 'brand.media'])
            ->withStorefrontTranslations()
            ->withCurrentPrices()
            ->withCurrentStock()
            ->withApprovedReviewSummary();

        $sort = (string) $request->string('sort', 'latest');
        $query = match ($sort) {
            'name' => $query->orderByLocalizedName(),
            default => $query->latest(),
        };

        return Inertia::render('shop/index', [
            'products' => localize_storefront($query->paginate(12)->withQueryString()),
            'categories' => localize_storefront(Category::query()
                ->scopes('enabled')
                ->whereNull('parent_id')
                ->withStorefrontTranslations()
                ->with(['children' => fn ($q) => $q->scopes('enabled')->withStorefrontTranslations()->orderBy('position')->select(['id', 'name', 'slug', 'parent_id'])])
                ->orderBy('position')
                ->get(['id', 'name', 'slug'])),
            'children' => localize_storefront($children),
            'priceRange' => $priceRange,
            'filters' => [
                'search' => $search,
                'category' => $selectedCategory?->id,
                'sort' => $sort,
                'price_min' => $price['min'],
                'price_max' => $price['max'],
            ],
        ]);
    }

    public function show(Product $product, BuildProductAttributes $buildProductAttributes): Response
    {
        abort_unless($product->isPublished(), 404);

        $currencyCode = current_currency();
        $priceConstraint = fn ($q) => $q->whereRelation('currency', 'code', $currencyCode);

        $product->load([
            'brand.media',
            'media',
            'translations',
            'prices' => $priceConstraint,
            'relatedProducts' => fn ($q) => $q->withStorefrontTranslations()->withCurrentPrices()->withCurrentStock()->withApprovedReviewSummary(),
            'relatedProducts.brand.media',
            'relatedProducts.media',
            'relatedProducts.variants' => fn ($q) => $q->select(['id', 'product_id']),
            'relatedProducts.variants.prices' => $priceConstraint,
            'variants.media',
            'variants.translations',
            'variants.values.attribute',
            'variants.prices' => $priceConstraint,
            'ratings' => fn ($q) => $q
                ->where('approved', true)
                ->with(['author:id,first_name,last_name'])
                ->latest(),
        ]);

        $product->setRelation('reviews', $product->ratings);
        $product->unsetRelation('ratings');

        $variantOptions = null;

        if ($product->canUseVariants() && $product->variants->isNotEmpty()) {
            ProductVariant::loadCurrentStock($product->variants); // @phpstan-ignore argument.type
            $product->variants->each(fn (ProductVariant $variant) => $variant->append('stock'));
            $variantOptions = resolve(BuildVariantOptions::class)->handle($product);
        } else {
            $product->setAttribute('real_stock', $product->getStock());
            $product->append('stock');
        }

        $product->localizeForStorefront();
        localize_storefront($product->relatedProducts);
        localize_storefront($product->variants);

        if (filled($product->description)) {
            $product->setAttribute(
                'description',
                str($product->description)->sanitizeHtml()->toString(),
            );
        }

        return Inertia::render('shop/product', [
            'product' => $product,
            'variantOptions' => $variantOptions,
            'productAttributes' => $buildProductAttributes->handle($product),
            'canReview' => $this->canReview($product),
            'hreflang' => storefront_hreflang('shop.product', ['product' => $product]),
        ]);
    }

    private function canReview(Product $product): bool
    {
        $user = auth()->user();

        if ($user === null) {
            return false;
        }

        return ! $product->ratings()
            ->where('author_id', $user->id)
            ->where('author_type', $user->getMorphClass())
            ->exists();
    }
}
