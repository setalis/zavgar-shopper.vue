<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Search, SlidersHorizontal } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import CategoryTile from '@/components/shop/category-tile.vue';
import Container from '@/components/shop/container.vue';
import PageHead from '@/components/shop/page-head.vue';
import ProductCard from '@/components/shop/product-card.vue';
import ProductFilters from '@/components/shop/product-filters.vue';
import ProductPagination from '@/components/shop/product-pagination.vue';
import type { PaginatorLink } from '@/components/shop/product-pagination.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
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
import { useTrans } from '@/composables/useTrans';
import { withPriceParams } from '@/lib/price-filter';
import { home } from '@/routes';
import * as shop from '@/routes/shop';
import type { Category, PriceRange, Product } from '@/types/shop';

const { t } = useTrans();
const { localized } = useLocalizedRoute();

type Paginated<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    total: number;
    links: PaginatorLink[];
};

type Filters = {
    search: string;
    category: number | null;
    sort: string;
    price_min: number | null;
    price_max: number | null;
};

type ShopCategory = Pick<Category, 'id' | 'name' | 'slug'> & {
    children?: Pick<Category, 'id' | 'name' | 'slug'>[];
};

const props = defineProps<{
    products: Paginated<Product>;
    categories: ShopCategory[];
    children: Category[];
    priceRange: PriceRange | null;
    filters: Filters;
}>();

const search = ref<string>(props.filters.search);
const sort = ref<string>(props.filters.sort);
const filtersOpen = ref<boolean>(false);

let searchTimer: ReturnType<typeof setTimeout> | null = null;

const crumbs = computed(() => [
    { label: t('shop.nav.home'), href: localized(home.url()) },
    { label: t('shop.index.title') },
]);

function visit(overrides: Partial<Filters> = {}): void {
    const next = { ...props.filters, ...overrides };

    router.get(
        localized(shop.index.url()),
        withPriceParams(
            {
                search: next.search,
                category: next.category,
                sort: next.sort,
            },
            next.price_min,
            next.price_max,
        ),
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

watch(sort, (value) => {
    visit({ sort: value });
});

watch(search, (value) => {
    if (searchTimer) {
        clearTimeout(searchTimer);
    }

    searchTimer = setTimeout(() => {
        visit({ search: value });
    }, 300);
});

function filterByCategory(categoryId: number | null): void {
    filtersOpen.value = false;
    visit({ category: categoryId });
}

function changePrice(min: number | null, max: number | null): void {
    visit({ price_min: min, price_max: max });
}

function categoryFilterUrl(categoryId: number): string {
    return localized(
        shop.index.url({
        query: withPriceParams(
            {
                search: props.filters.search,
                category: categoryId,
                sort: props.filters.sort,
            },
            props.filters.price_min,
            props.filters.price_max,
        ),
    }),
    );
}
</script>

<template>
    <Head :title="t('shop.index.title')" />

    <PageHead
        :title="t('shop.index.title')"
        :description="t('shop.index.subtitle')"
        :crumbs="crumbs"
    />

    <Container class="py-10 md:py-14">
        <div class="grid gap-10 lg:grid-cols-[260px_1fr]">
            <ProductFilters
                class="hidden lg:block"
                :categories="categories"
                :active-category="filters.category"
                :price-range="priceRange"
                :price-min="filters.price_min"
                :price-max="filters.price_max"
                @select-category="filterByCategory"
                @change-price="changePrice"
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
                        <div class="relative">
                            <label for="shop-search" class="sr-only">
                                {{ t('shop.nav.search') }}
                            </label>
                            <Search
                                class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-ink-faint"
                                aria-hidden="true"
                            />
                            <Input
                                id="shop-search"
                                v-model="search"
                                type="search"
                                :placeholder="
                                    t('shop.index.search_placeholder')
                                "
                                class="w-52 rounded-sm pl-9"
                            />
                        </div>

                        <Select v-model="sort">
                            <SelectTrigger
                                class="w-auto rounded-sm"
                                :aria-label="t('shop.index.sort_aria')"
                            >
                                <SelectValue
                                    :placeholder="
                                        t('shop.index.sort_placeholder')
                                    "
                                />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="latest">
                                    {{ t('shop.index.sort.newest') }}
                                </SelectItem>
                                <SelectItem value="name">
                                    {{ t('shop.index.sort.name') }}
                                </SelectItem>
                            </SelectContent>
                        </Select>

                        <Button
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

                <section
                    v-if="children.length"
                    class="mb-10"
                    :aria-label="t('shop.category.children')"
                >
                    <h2
                        class="mb-4 font-mono text-xs tracking-[0.08em] text-ink-faint uppercase"
                    >
                        {{ t('shop.category.children') }}
                    </h2>
                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
                        <CategoryTile
                            v-for="child in children"
                            :key="child.id"
                            :category="child"
                            :href="categoryFilterUrl(child.id)"
                        />
                    </div>
                </section>

                <div
                    v-if="!products.data.length"
                    class="flex flex-col items-center justify-center rounded-lg border border-rule bg-paper py-20 text-center"
                >
                    <Search class="size-10 text-ink-faint" aria-hidden="true" />
                    <h3 class="mt-4 font-heading text-md font-bold text-ink">
                        {{ t('shop.index.empty.title') }}
                    </h3>
                    <p class="mt-1 text-sm text-ink-mute">
                        {{ t('shop.index.empty.subtitle') }}
                    </p>
                </div>

                <template v-else>
                    <div class="grid grid-cols-2 gap-4 lg:grid-cols-3">
                        <ProductCard
                            v-for="product in products.data"
                            :key="product.id"
                            :product="product"
                        />
                    </div>

                    <ProductPagination
                        :links="products.links"
                        :label="t('shop.index.pagination')"
                    />
                </template>
            </div>
        </div>
    </Container>

    <Sheet v-model:open="filtersOpen">
        <SheetContent side="left" class="w-[88vw] gap-0 sm:max-w-sm">
            <SheetHeader class="border-b border-rule p-5">
                <SheetTitle class="font-heading text-md font-bold">
                    {{ t('shop.filters.title') }}
                </SheetTitle>
                <SheetDescription class="text-sm text-ink-mute">
                    {{ t('shop.filters.description') }}
                </SheetDescription>
            </SheetHeader>

            <div class="flex-1 overflow-y-auto p-5">
                <ProductFilters
                    class="border-0 p-0"
                    :categories="categories"
                    :active-category="filters.category"
                    :price-range="priceRange"
                    :price-min="filters.price_min"
                    :price-max="filters.price_max"
                    @select-category="filterByCategory"
                    @change-price="changePrice"
                />
            </div>
        </SheetContent>
    </Sheet>
</template>
