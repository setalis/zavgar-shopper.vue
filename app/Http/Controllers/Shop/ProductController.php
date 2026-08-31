<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shop;

use App\Actions\Product\BuildVariantOptions;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class ProductController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Product::query()
            ->scopes('publish')
            ->with(['media', 'brand.media'])
            ->withCurrentPrices()
            ->withCurrentStock()
            ->withApprovedReviewSummary();

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
                ->where('id', $categoryId)
                ->first();

            if ($selectedCategory !== null) {
                $query->whereHas(
                    'categories',
                    fn ($q) => $q->where('id', $selectedCategory->id),
                );

                $children = $selectedCategory->children()
                    ->scopes('enabled')
                    ->with('media')
                    ->withCount(['products' => fn ($q) => $q->whereNull(shopper_table('products').'.deleted_at')])
                    ->orderBy('position')
                    ->get();
            }
        }

        $sort = (string) $request->string('sort', 'latest');
        $query = match ($sort) {
            'name' => $query->orderBy('name'),
            default => $query->latest(),
        };

        return Inertia::render('shop/index', [
            'products' => $query->paginate(12)->withQueryString(),
            'categories' => Category::query()
                ->scopes('enabled')
                ->whereNull('parent_id')
                ->with(['children' => fn ($q) => $q->scopes('enabled')->orderBy('position')->select(['id', 'name', 'slug', 'parent_id'])])
                ->orderBy('position')
                ->get(['id', 'name', 'slug']),
            'children' => $children,
            'filters' => [
                'search' => $search,
                'category' => $selectedCategory?->id,
                'sort' => $sort,
            ],
        ]);
    }

    public function show(Product $product): Response
    {
        abort_unless($product->isPublished(), 404);

        $currencyCode = current_currency();
        $priceConstraint = fn ($q) => $q->whereRelation('currency', 'code', $currencyCode);

        $product->load([
            'brand.media',
            'media',
            'prices' => $priceConstraint,
            'relatedProducts' => fn ($q) => $q->withCurrentPrices()->withCurrentStock()->withApprovedReviewSummary(),
            'relatedProducts.brand.media',
            'relatedProducts.media',
            'relatedProducts.variants' => fn ($q) => $q->select(['id', 'product_id']),
            'relatedProducts.variants.prices' => $priceConstraint,
            'variants.media',
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

        if (filled($product->description)) {
            $product->setAttribute(
                'description',
                str($product->description)->sanitizeHtml()->toString(),
            );
        }

        return Inertia::render('shop/product', [
            'product' => $product,
            'variantOptions' => $variantOptions,
            'canReview' => $this->canReview($product),
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
