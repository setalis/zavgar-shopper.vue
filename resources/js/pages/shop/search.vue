<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Search as SearchIcon } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import Container from '@/components/shop/container.vue';
import PageHead from '@/components/shop/page-head.vue';
import ProductCard from '@/components/shop/product-card.vue';
import ProductPagination from '@/components/shop/product-pagination.vue';
import type { PaginatorLink } from '@/components/shop/product-pagination.vue';
import { useTrans } from '@/composables/useTrans';
import { home } from '@/routes';
import { search as searchRoute } from '@/routes/shop';
import type { Product } from '@/types/shop';

type Paginated<T> = {
    data: T[];
    total: number;
    current_page: number;
    last_page: number;
    links: PaginatorLink[];
};

const props = defineProps<{
    query: string;
    products: Paginated<Product> | null;
}>();

const { t } = useTrans();

const search = ref<string>(props.query);
let debounceId: number | undefined;

const crumbs = computed(() => [
    { label: t('shop.nav.home'), href: home.url() },
    { label: t('shop.search.heading') },
]);

watch(search, (value) => {
    window.clearTimeout(debounceId);
    debounceId = window.setTimeout(() => {
        router.get(
            searchRoute.url(),
            { q: value },
            { preserveState: true, preserveScroll: true, replace: true },
        );
    }, 300);
});
</script>

<template>
    <Head :title="t('shop.search.title')" />

    <PageHead :title="t('shop.search.heading')" :crumbs="crumbs">
        <div class="relative mt-6 max-w-[560px]">
            <SearchIcon
                class="pointer-events-none absolute top-1/2 left-5 size-[18px] -translate-y-1/2 text-ink-faint"
                aria-hidden="true"
            />
            <input
                v-model="search"
                type="search"
                autofocus
                :placeholder="t('shop.search.placeholder')"
                :aria-label="t('shop.search.heading')"
                class="w-full rounded-full border border-rule-strong bg-paper py-3.5 pr-5 pl-13 text-base transition placeholder:text-ink-faint focus:border-brand focus:ring-4 focus:ring-brand/12 focus:outline-none"
            />
        </div>
    </PageHead>

    <Container class="py-10 md:py-14">
        <p
            v-if="products === null"
            class="rounded-lg border border-rule bg-paper py-20 text-center text-sm text-ink-mute"
        >
            {{ t('shop.search.min_chars') }}
        </p>

        <div
            v-else-if="!products.data.length"
            class="flex flex-col items-center justify-center rounded-lg border border-rule bg-paper py-20 text-center"
        >
            <SearchIcon class="size-10 text-ink-faint" aria-hidden="true" />
            <h3 class="mt-4 font-heading text-md font-bold text-ink">
                {{ t('shop.search.no_results') }}
            </h3>
            <p class="mt-1 text-sm text-ink-mute">
                {{ t('shop.search.try_different') }}
            </p>
        </div>

        <template v-else>
            <p class="mb-5 font-mono text-xs tracking-[0.04em] text-ink-mute">
                {{
                    t('shop.search.results_for', {
                        count: products.total,
                        results:
                            products.total === 1
                                ? t('shop.search.result')
                                : t('shop.search.results'),
                        query,
                    })
                }}
            </p>

            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                <ProductCard
                    v-for="product in products.data"
                    :key="product.id"
                    :product="product"
                />
            </div>

            <ProductPagination
                :links="products.links"
                :label="t('shop.search.pagination')"
            />
        </template>
    </Container>
</template>
