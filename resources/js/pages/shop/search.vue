<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Search as SearchIcon, SlidersHorizontal } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import Container from '@/components/shop/container.vue';
import PageHead from '@/components/shop/page-head.vue';
import ProductCard from '@/components/shop/product-card.vue';
import ProductPagination from '@/components/shop/product-pagination.vue';
import type { PaginatorLink } from '@/components/shop/product-pagination.vue';
import ProductPriceFilters from '@/components/shop/product-price-filters.vue';
import { Button } from '@/components/ui/button';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
} from '@/components/ui/sheet';
import { useTrans } from '@/composables/useTrans';
import { withPriceParams } from '@/lib/price-filter';
import { home } from '@/routes';
import { search as searchRoute } from '@/routes/shop';
import type { PriceRange, Product } from '@/types/shop';

type Paginated<T> = {
    data: T[];
    total: number;
    current_page: number;
    last_page: number;
    links: PaginatorLink[];
};

type Filters = {
    price_min: number | null;
    price_max: number | null;
};

const props = defineProps<{
    query: string;
    products: Paginated<Product> | null;
    priceRange: PriceRange | null;
    filters: Filters;
}>();

const { t } = useTrans();

const search = ref<string>(props.query);
const filtersOpen = ref<boolean>(false);
let debounceId: number | undefined;

const crumbs = computed(() => [
    { label: t('shop.nav.home'), href: home.url() },
    { label: t('shop.search.heading') },
]);

const hasPriceFilter = computed<boolean>(
    () => props.products !== null && props.priceRange !== null,
);

function visit(overrides: Partial<Filters> & { q?: string } = {}): void {
    filtersOpen.value = false;

    router.get(
        searchRoute.url(),
        withPriceParams(
            { q: overrides.q ?? search.value },
            overrides.price_min !== undefined
                ? overrides.price_min
                : props.filters.price_min,
            overrides.price_max !== undefined
                ? overrides.price_max
                : props.filters.price_max,
        ),
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

watch(search, (value) => {
    window.clearTimeout(debounceId);
    debounceId = window.setTimeout(() => {
        visit({ q: value });
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
            v-else
            :class="
                hasPriceFilter
                    ? 'grid gap-10 lg:grid-cols-[260px_1fr]'
                    : undefined
            "
        >
            <ProductPriceFilters
                v-if="priceRange"
                class="hidden lg:block"
                :bounds="priceRange"
                :price-min="filters.price_min"
                :price-max="filters.price_max"
                @change="
                    (min, max) => visit({ price_min: min, price_max: max })
                "
            />

            <div>
                <div
                    v-if="products.data.length || hasPriceFilter"
                    class="mb-5 flex flex-wrap items-center justify-between gap-3"
                >
                    <p
                        v-if="products.data.length"
                        class="font-mono text-xs tracking-[0.04em] text-ink-mute"
                    >
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
                    <span v-else />

                    <Button
                        v-if="hasPriceFilter"
                        variant="outline"
                        size="sm"
                        class="rounded-sm lg:hidden"
                        @click="filtersOpen = true"
                    >
                        <SlidersHorizontal
                            class="size-4"
                            aria-hidden="true"
                        />
                        {{ t('shop.filters.title') }}
                    </Button>
                </div>

                <div
                    v-if="!products.data.length"
                    class="flex flex-col items-center justify-center rounded-lg border border-rule bg-paper py-20 text-center"
                >
                    <SearchIcon
                        class="size-10 text-ink-faint"
                        aria-hidden="true"
                    />
                    <h3 class="mt-4 font-heading text-md font-bold text-ink">
                        {{ t('shop.search.no_results') }}
                    </h3>
                    <p class="mt-1 text-sm text-ink-mute">
                        {{ t('shop.search.try_different') }}
                    </p>
                </div>

                <template v-else>
                    <div
                        :class="
                            hasPriceFilter
                                ? 'grid grid-cols-2 gap-4 lg:grid-cols-3'
                                : 'grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4'
                        "
                    >
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
            </div>
        </div>
    </Container>

    <Sheet v-if="priceRange" v-model:open="filtersOpen">
        <SheetContent side="left" class="w-[88vw] gap-0 sm:max-w-sm">
            <SheetHeader class="border-b border-rule p-5">
                <SheetTitle class="font-heading text-md font-bold">
                    {{ t('shop.filters.title') }}
                </SheetTitle>
                <SheetDescription class="text-sm text-ink-mute">
                    {{ t('shop.filters.price_range') }}
                </SheetDescription>
            </SheetHeader>

            <div class="flex-1 overflow-y-auto p-5">
                <ProductPriceFilters
                    class="border-0 p-0"
                    :bounds="priceRange"
                    :price-min="filters.price_min"
                    :price-max="filters.price_max"
                    @change="
                        (min, max) => visit({ price_min: min, price_max: max })
                    "
                />
            </div>
        </SheetContent>
    </Sheet>
</template>
