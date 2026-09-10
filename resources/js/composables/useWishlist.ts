import { router } from '@inertiajs/vue3';
import { useLocalizedRoute } from '@/composables/useLocalizedRoute';
import { useShop } from '@/composables/useShop';
import * as wishlist from '@/routes/shop/wishlist';

export function useWishlist() {
    const { wishlistIds } = useShop();
    const { localized } = useLocalizedRoute();

    function has(productId: number): boolean {
        return wishlistIds.value.includes(productId);
    }

    function add(productId: number): void {
        router.post(
            localized(wishlist.store.url()),
            { product_id: productId },
            { preserveScroll: true },
        );
    }

    function remove(productId: number): void {
        router.delete(localized(wishlist.destroy.url(productId)), {
            preserveScroll: true,
        });
    }

    function toggle(productId: number): void {
        if (has(productId)) {
            remove(productId);
        } else {
            add(productId);
        }
    }

    return { has, add, remove, toggle };
}
