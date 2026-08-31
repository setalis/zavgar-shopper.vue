<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Heart } from 'lucide-vue-next';
import { computed } from 'vue';
import PriceDisplay from '@/components/shop/price-display.vue';
import StarRating from '@/components/shop/star-rating.vue';
import { Button } from '@/components/ui/button';
import { useCart } from '@/composables/useCart';
import { useTrans } from '@/composables/useTrans';
import * as shop from '@/routes/shop';
import { ProductType } from '@/types/shop';
import type { Product } from '@/types/shop';

const props = defineProps<{ product: Product }>();

const { t } = useTrans();
const { add } = useCart();

const href = computed<string>(() =>
    shop.product.url({ product: props.product.slug }),
);

const thumbnail = computed<string | null>(
    () => props.product.thumbnail ?? props.product.images?.[0]?.url ?? null,
);

const price = computed(() => props.product.storefront_price ?? null);

const hasVariants = computed<boolean>(
    () =>
        (props.product.variants?.length ?? 0) > 0 ||
        props.product.type === ProductType.VARIANT,
);

const inStock = computed<boolean>(() => {
    if (props.product.allow_backorder) {
        return true;
    }

    return (props.product.storefront_stock ?? 0) > 0;
});

const reviewCount = computed<number>(
    () =>
        props.product.storefront_reviews_count ??
        props.product.reviews?.length ??
        0,
);

const averageRating = computed<number>(() => {
    if (props.product.storefront_average_rating != null) {
        return props.product.storefront_average_rating;
    }

    const reviews = props.product.reviews ?? [];

    if (reviews.length === 0) {
        return 0;
    }

    return (
        reviews.reduce((total, review) => total + review.rating, 0) /
        reviews.length
    );
});

const onSale = computed<boolean>(
    () =>
        !!price.value?.compare_amount &&
        price.value.compare_amount > price.value.amount,
);

function addToCart(): void {
    add({ product_id: props.product.id, quantity: 1 });
}
</script>

<template>
    <article
        class="group relative flex flex-col rounded-lg border border-rule bg-paper p-4 transition duration-200 ease-brand hover:-translate-y-[3px] hover:border-brand-line hover:shadow-md"
    >
        <div
            class="relative mb-4 aspect-square overflow-hidden rounded-sm bg-muted"
        >
            <img
                v-if="thumbnail"
                :src="thumbnail"
                :alt="product.name"
                loading="lazy"
                class="size-full object-cover object-center transition duration-500 ease-brand group-hover:scale-[1.06]"
            />

            <span
                v-if="onSale"
                class="absolute top-2.5 left-2.5 rounded-[4px] bg-rose px-2.5 py-1 font-mono text-[10px] font-semibold tracking-[0.05em] text-paper"
            >
                {{ t('shop.product.badge_sale') }}
            </span>
            <span
                v-else-if="product.featured"
                class="absolute top-2.5 left-2.5 rounded-[4px] bg-card-green px-2.5 py-1 font-mono text-[10px] font-semibold tracking-[0.05em] text-paper"
            >
                {{ t('shop.product.badge_featured') }}
            </span>

            <button
                type="button"
                class="absolute top-2.5 right-2.5 z-10 grid size-8 place-items-center rounded-full bg-paper text-ink opacity-0 shadow-sm transition group-hover:opacity-100 hover:text-rose focus-visible:opacity-100"
                :aria-label="t('shop.product.add_to_wishlist')"
                :title="t('shop.nav.wishlist_soon')"
            >
                <Heart class="size-4" aria-hidden="true" />
            </button>
        </div>

        <span
            class="mb-1 inline-flex items-center gap-1.5 font-mono text-[11px] tracking-[0.04em] text-ink-mute"
        >
            <span
                class="size-1.5 rounded-full"
                :class="inStock ? 'bg-emerald' : 'bg-rule-strong'"
                aria-hidden="true"
            />
            {{
                inStock
                    ? t('shop.product.in_stock')
                    : t('shop.product.out_of_stock')
            }}
        </span>

        <h3
            class="mb-1 font-heading text-sm leading-tight font-semibold text-ink"
        >
            <Link :href="href" class="transition hover:text-brand">
                <span class="absolute inset-0 z-0" aria-hidden="true" />
                {{ product.name }}
            </Link>
        </h3>

        <PriceDisplay :price="price" size="md" class="mb-2" />

        <StarRating
            v-if="reviewCount > 0"
            class="mb-4"
            :rating="averageRating"
            :count="reviewCount"
        />

        <Button
            v-if="hasVariants"
            as-child
            size="sm"
            block
            class="relative z-10 mt-auto"
        >
            <Link :href="href">{{ t('shop.product.choose_options') }}</Link>
        </Button>
        <Button
            v-else
            size="sm"
            block
            class="relative z-10 mt-auto"
            :disabled="!inStock"
            @click="addToCart"
        >
            {{ t('shop.product.order_now') }}
        </Button>
    </article>
</template>
