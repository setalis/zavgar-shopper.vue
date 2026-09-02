<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Actions\Wishlist\WishlistManager;
use App\Models\User;
use Illuminate\Auth\Events\Login;

final class MergeGuestWishlist
{
    public function __construct(private WishlistManager $wishlist) {}

    public function handle(Login $event): void
    {
        if (! $event->user instanceof User) {
            return;
        }

        $this->wishlist->mergeFor($event->user);
    }
}
