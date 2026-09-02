<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Heart } from 'lucide-vue-next';
import { computed } from 'vue';
import Container from '@/components/shop/container.vue';
import PageHead from '@/components/shop/page-head.vue';
import ProductCard from '@/components/shop/product-card.vue';
import ProductPagination from '@/components/shop/product-pagination.vue';
import type { PaginatorLink } from '@/components/shop/product-pagination.vue';
import { Button } from '@/components/ui/button';
import { useTrans } from '@/composables/useTrans';
import { home } from '@/routes';
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

const crumbs = computed(() => [
    { label: t('shop.nav.home'), href: home.url() },
    { label: t('shop.wishlist.heading') },
]);
</script>

<template>
    <Head :title="t('shop.wishlist.title')" />

    <PageHead :title="t('shop.wishlist.heading')" :crumbs="crumbs" />

    <Container class="py-10 md:py-14">
        <div
            v-if="!products.data.length"
            class="flex flex-col items-center justify-center rounded-lg border border-rule bg-paper py-20 text-center"
        >
            <Heart class="size-12 text-ink-faint" aria-hidden="true" />
            <h2 class="mt-4 font-heading text-lg font-bold text-ink">
                {{ t('shop.wishlist.empty.title') }}
            </h2>
            <p class="mt-1 text-sm text-ink-mute">
                {{ t('shop.wishlist.empty.subtitle') }}
            </p>
            <Button as-child class="mt-6">
                <Link :href="shop.index.url()">
                    {{ t('shop.wishlist.empty.cta') }}
                </Link>
            </Button>
        </div>

        <template v-else>
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
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
    </Container>
</template>
