<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Heart } from 'lucide-vue-next';
import ProductCard from '@/components/shop/product-card.vue';
import ProductPagination from '@/components/shop/product-pagination.vue';
import type { PaginatorLink } from '@/components/shop/product-pagination.vue';
import { Button } from '@/components/ui/button';
import { useTrans } from '@/composables/useTrans';
import * as shop from '@/routes/shop';
import type { Product } from '@/types/shop';

type Paginated<T> = {
    data: T[];
    total: number;
    current_page: number;
    last_page: number;
    links: PaginatorLink[];
};

defineProps<{
    products: Paginated<Product>;
}>();

const { t } = useTrans();
</script>

<template>
    <Head :title="t('shop.wishlist.title')" />

    <div class="flex items-center gap-3">
        <h1 class="font-heading text-2xl font-bold text-ink">
            {{ t('shop.wishlist.heading') }}
        </h1>
        <span
            v-if="products.total > 0"
            class="inline-flex items-center justify-center rounded-full bg-muted px-2.5 py-0.5 text-xs font-medium text-ink-mute"
        >
            {{ products.total }}
        </span>
    </div>

    <div
        v-if="!products.data.length"
        class="mt-12 flex flex-col items-center justify-center text-center"
    >
        <Heart class="size-12 text-ink-faint" aria-hidden="true" />
        <h3 class="mt-4 text-sm font-medium text-ink">
            {{ t('shop.wishlist.empty.title') }}
        </h3>
        <p class="mt-1 text-sm text-ink-mute">
            {{ t('shop.wishlist.empty.subtitle') }}
        </p>
        <Link :href="shop.index.url()" class="mt-6">
            <Button>{{ t('shop.wishlist.empty.cta') }}</Button>
        </Link>
    </div>

    <template v-else>
        <div class="mt-6 grid grid-cols-2 gap-4 lg:grid-cols-3">
            <ProductCard
                v-for="product in products.data"
                :key="product.id"
                :product="product"
            />
        </div>

        <ProductPagination
            :links="products.links"
            :label="t('shop.wishlist.pagination')"
        />
    </template>
</template>
