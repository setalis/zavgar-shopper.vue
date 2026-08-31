<?php

declare(strict_types=1);

namespace App\Concerns;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;

trait ResolvesStorefrontReviews
{
    public function initializeResolvesStorefrontReviews(): void
    {
        $this->append(['storefront_reviews_count', 'storefront_average_rating']);
        $this->makeHidden(['approved_reviews_count', 'approved_average_rating']);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    #[Scope]
    protected function withApprovedReviewSummary(Builder $query): Builder
    {
        return $query
            ->withCount([
                'ratings as approved_reviews_count' => fn (Builder $ratings): Builder => $ratings->where('approved', true),
            ])
            ->withAvg([
                'ratings as approved_average_rating' => fn (Builder $ratings): Builder => $ratings->where('approved', true),
            ], 'rating');
    }

    /**
     * @return Attribute<int, never>
     */
    protected function storefrontReviewsCount(): Attribute
    {
        return Attribute::get(function (): int {
            if (array_key_exists('approved_reviews_count', $this->attributes)) {
                return (int) $this->attributes['approved_reviews_count'];
            }

            $reviews = $this->getAttribute('reviews');

            if (is_iterable($reviews)) {
                return count($reviews);
            }

            return 0;
        });
    }

    /**
     * @return Attribute<float, never>
     */
    protected function storefrontAverageRating(): Attribute
    {
        return Attribute::get(function (): float {
            if (array_key_exists('approved_average_rating', $this->attributes) && $this->attributes['approved_average_rating'] !== null) {
                return round((float) $this->attributes['approved_average_rating'], 1);
            }

            $reviews = $this->getAttribute('reviews');

            if (! is_iterable($reviews)) {
                return 0.0;
            }

            $reviews = collect($reviews);

            if ($reviews->isEmpty()) {
                return 0.0;
            }

            return round($reviews->avg('rating') ?? 0, 1);
        });
    }
}
