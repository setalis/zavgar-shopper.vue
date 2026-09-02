<?php

declare(strict_types=1);

namespace App\Actions\Wishlist;

use App\Models\Product;
use App\Models\User;
use App\Models\WishlistItem;

final class WishlistManager
{
    public const string SESSION_KEY = 'shopper_wishlist';

    public const int MAX_GUEST_ITEMS = 100;

    /**
     * @return list<int>
     */
    public function ids(): array
    {
        $this->syncGuestSessionIfAuthenticated();

        if (auth()->check()) {
            return WishlistItem::query()
                ->where('user_id', auth()->id())
                ->latest()
                ->pluck('product_id')
                ->map(fn (mixed $id): int => (int) $id)
                ->values()
                ->all();
        }

        return array_reverse($this->sessionIds());
    }

    public function count(): int
    {
        return count($this->ids());
    }

    public function contains(int $productId): bool
    {
        return in_array($productId, $this->ids(), true);
    }

    public function add(Product $product): void
    {
        $this->syncGuestSessionIfAuthenticated();

        if (auth()->check()) {
            WishlistItem::firstOrCreate([
                'user_id' => auth()->id(),
                'product_id' => $product->id,
            ]);

            return;
        }

        $ids = $this->sessionIds();

        if (in_array($product->id, $ids, true)) {
            return;
        }

        if (count($ids) >= self::MAX_GUEST_ITEMS) {
            array_shift($ids);
        }

        $ids[] = $product->id;
        session()->put(self::SESSION_KEY, $ids);
    }

    public function remove(Product $product): void
    {
        $this->syncGuestSessionIfAuthenticated();

        if (auth()->check()) {
            WishlistItem::query()
                ->where('user_id', auth()->id())
                ->where('product_id', $product->id)
                ->delete();

            return;
        }

        session()->put(
            self::SESSION_KEY,
            array_values(array_filter(
                $this->sessionIds(),
                fn (int $id): bool => $id !== $product->id,
            )),
        );
    }

    public function mergeFor(User $user): void
    {
        $ids = $this->sessionIds();

        if ($ids === []) {
            session()->forget(self::SESSION_KEY);

            return;
        }

        $publishedIds = Product::query()
            ->scopes('publish')
            ->whereIn('id', $ids)
            ->pluck('id')
            ->map(fn (mixed $id): int => (int) $id)
            ->all();

        foreach ($publishedIds as $productId) {
            WishlistItem::firstOrCreate([
                'user_id' => $user->id,
                'product_id' => $productId,
            ]);
        }

        session()->forget(self::SESSION_KEY);
    }

    private function syncGuestSessionIfAuthenticated(): void
    {
        if (! session()->has(self::SESSION_KEY)) {
            return;
        }

        $user = auth()->user();

        if (! $user instanceof User) {
            return;
        }

        $this->mergeFor($user);
    }

    /**
     * @return list<int>
     */
    private function sessionIds(): array
    {
        $ids = session(self::SESSION_KEY, []);

        if (! is_array($ids)) {
            return [];
        }

        return array_values(array_unique(array_map(
            fn (mixed $id): int => (int) $id,
            array_values($ids),
        )));
    }
}
