<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shop;

use App\Actions\Wishlist\WishlistManager;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Inertia\Inertia;
use Inertia\Response;

final class WishlistController extends Controller
{
    /**
     * @var list<string>
     */
    private const array CARD_COLUMNS = [
        'id',
        'name',
        'slug',
        'brand_id',
        'type',
        'featured',
        'security_stock',
        'allow_backorder',
    ];

    public function __construct(private WishlistManager $wishlist) {}

    public function index(Request $request): Response
    {
        $ids = $this->publishedIds($this->wishlist->ids());
        $perPage = 12;
        $page = max(1, $request->integer('page', 1));
        $pageIds = array_slice($ids, ($page - 1) * $perPage, $perPage);

        $products = $pageIds === []
            ? collect()
            : $this->cardQuery()
                ->whereIn('id', $pageIds)
                ->get()
                ->sortBy(fn (Product $product): int|false => array_search($product->id, $pageIds, true))
                ->values();

        $paginator = new LengthAwarePaginator(
            $products,
            count($ids),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ],
        );

        return Inertia::render('shop/wishlist', [
            'products' => $paginator,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:'.shopper_table('products').',id'],
        ]);

        $product = Product::query()->scopes('publish')->findOrFail($data['product_id']);

        $this->wishlist->add($product);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('backend.wishlist.added')]);

        return back();
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->wishlist->remove($product);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('backend.wishlist.removed')]);

        return back();
    }

    /**
     * @param  list<int>  $ids
     * @return list<int>
     */
    private function publishedIds(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        $published = Product::query()
            ->scopes('publish')
            ->whereIn('id', $ids)
            ->pluck('id')
            ->map(fn (mixed $id): int => (int) $id)
            ->all();

        return array_values(array_filter(
            $ids,
            fn (int $id): bool => in_array($id, $published, true),
        ));
    }

    private function cardQuery(): Builder
    {
        return Product::query()
            ->select(self::CARD_COLUMNS)
            ->with(['media', 'brand.media'])
            ->withCurrentPrices()
            ->withCurrentStock()
            ->withApprovedReviewSummary()
            ->scopes('publish');
    }
}
