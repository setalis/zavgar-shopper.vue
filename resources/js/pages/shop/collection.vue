<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Search, SlidersHorizontal } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import HreflangLinks from '@/components/shop/hreflang-links.vue';
import Container from '@/components/shop/container.vue';
import PageHead from '@/components/shop/page-head.vue';
import ProductCard from '@/components/shop/product-card.vue';
import ProductPagination from '@/components/shop/product-pagination.vue';
import type { PaginatorLink } from '@/components/shop/product-pagination.vue';
import ProductPriceFilters from '@/components/shop/product-price-filters.vue';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
} from '@/components/ui/sheet';
import { useLocalizedRoute } from '@/composables/useLocalizedRoute';
import type { HreflangLink } from '@/composables/useLocalizedRoute';
import { useTrans } from '@/composables/useTrans';
import { stripHtml } from '@/lib/format';
import { withPriceParams } from '@/lib/price-filter';
import { home } from '@/routes';
import * as shop from '@/routes/shop';
import type { Collection, PriceRange, Product } from '@/types/shop';

type Paginated<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    total: number;
    links: PaginatorLink[];
};

type Filters = {
    sort: string;
    price_min: number | null;
    price_max: number | null;
};

const props = defineProps<{
    collection: Collection;
    products: Paginated<Product>;
    priceRange: PriceRange | null;
    filters: Filters;
    hreflang: HreflangLink[];
}>();

const { t } = useTrans();
const { localized } = useLocalizedRoute();

const sort = ref<string>(props.filters.sort);
const filtersOpen = ref<boolean>(false);

const crumbs = computed(() => [
    { label: t('shop.nav.home'), href: localized(home.url()) },
    { label: props.collection.name },
]);

const description = computed<string>(() =>
    stripHtml(props.collection.description),
);

const hasPriceFilter = computed<boolean>(() => props.priceRange !== null);

function visit(overrides: Partial<Filters> = {}): void {
    const next = { ...props.filters, ...overrides };
    filtersOpen.value = false;

    router.get(
        localized(
            shop.collection.url({ collection: props.collection.slug }),
        ),
        withPriceParams({ sort: next.sort }, next.price_min, next.price_max),
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

watch(sort, (value) => {
    visit({ sort: value });
});
</script>

<template>
    <Head :title="collection.name">
        <HreflangLinks :links="hreflang" />
    </Head>

    <PageHead
        :title="collection.name"
        :description="description"
        :crumbs="crumbs"
    />

    <Container class="py-10 md:py-14">
        <div
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
                    class="mb-5 flex flex-wrap items-center justify-between gap-3"
                >
                    <span
                        class="font-mono text-xs tracking-[0.04em] text-ink-mute"
                    >
                        {{
                            t('shop.index.results_count', {
                                count: products.total,
                            })
                        }}
                    </span>

                    <div class="flex flex-wrap items-center gap-3">
                        <Select v-if="products.data.length" v-model="sort">
                            <SelectTrigger
                                class="w-auto rounded-sm"
                                :aria-label="t('shop.collection_page.sort_aria')"
                            >
                                <SelectValue
                                    :placeholder="
                                        t('shop.collection_page.sort_placeholder')
                                    "
                                />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="latest">
                                    {{ t('shop.collection_page.sort.newest') }}
                                </SelectItem>
                                <SelectItem value="name">
                                    {{ t('shop.collection_page.sort.name') }}
                                </SelectItem>
                            </SelectContent>
                        </Select>

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
                </div>

                <div
                    v-if="!products.data.length"
                    class="flex flex-col items-center justify-center rounded-lg border border-rule bg-paper py-20 text-center"
                >
                    <Search class="size-10 text-ink-faint" aria-hidden="true" />
                    <h3 class="mt-4 font-heading text-md font-bold text-ink">
                        {{ t('shop.collection_page.empty') }}
                    </h3>
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
                        :label="t('shop.collection_page.pagination')"
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
