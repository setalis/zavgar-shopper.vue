<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shop;

use App\Actions\Product\AddProductReviewAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\StoreProductReviewRequest;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

final class ProductReviewController extends Controller
{
    public function store(StoreProductReviewRequest $request, Product $product): RedirectResponse
    {
        abort_unless($product->isPublished(), 404);

        $user = $request->user();

        $alreadyReviewed = $product->ratings()
            ->where('author_id', $user->id)
            ->where('author_type', $user->getMorphClass())
            ->exists();

        if ($alreadyReviewed) {
            return back()->withErrors(['review' => __('backend.reviews.already_submitted')]);
        }

        resolve(AddProductReviewAction::class)->execute(
            $product,
            $request->validated(),
            $user,
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => __('backend.reviews.submitted')]);

        return back();
    }
}
