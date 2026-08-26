<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import BrandLink from '@/components/shop/brand-link.vue';
import PriceDisplay from '@/components/shop/price-display.vue';
import * as shop from '@/routes/shop';
import type { Product } from '@/types/shop';

const props = defineProps<{ product: Product }>();

const thumbnail = computed<string | null>(
    () => props.product.thumbnail ?? props.product.images?.[0]?.url ?? null,
);

const price = computed(() => props.product.storefront_price ?? null);

const percentage = computed<number | null>(() => {
    if (
        !price.value?.compare_amount ||
        price.value.compare_amount <= price.value.amount
    ) {
        return null;
    }
    return Math.round(
        ((price.value.compare_amount - price.value.amount) /
            price.value.compare_amount) *
            100,
    );
});
</script>

<template>
    <div class="group relative">
        <div
            class="relative aspect-square overflow-hidden rounded-lg bg-zinc-100 dark:bg-zinc-800"
        >
            <img
                v-if="thumbnail"
                :src="thumbnail"
                :alt="product.name"
                loading="lazy"
                class="size-full object-cover object-center transition duration-300 group-hover:scale-105"
            />
            <span
                v-if="percentage"
                class="absolute top-3 left-3 inline-flex items-center rounded-full bg-red-500 px-2.5 py-0.5 text-xs font-medium text-white"
            >
                -{{ percentage }}%
            </span>
        </div>

        <h3
            class="mt-3 text-sm text-zinc-700 transition group-hover:text-zinc-900 dark:text-zinc-300 dark:group-hover:text-white"
        >
            <Link :href="shop.product.url({ product: product.slug })">
                <span class="absolute inset-0 z-0" />
                {{ product.name }}
            </Link>
        </h3>

        <BrandLink v-if="product.brand" :brand="product.brand" />

        <div class="mt-1">
            <PriceDisplay :price="price" size="sm" />
        </div>
    </div>
</template>
