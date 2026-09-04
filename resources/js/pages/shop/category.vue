<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Search, SlidersHorizontal } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import CategoryAttributeFilters from '@/components/shop/category-attribute-filters.vue';
import CategoryTile from '@/components/shop/category-tile.vue';
import Container from '@/components/shop/container.vue';
import PageHead from '@/components/shop/page-head.vue';
import ProductCard from '@/components/shop/product-card.vue';
import ProductPagination from '@/components/shop/product-pagination.vue';
import type { PaginatorLink } from '@/components/shop/product-pagination.vue';
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
import { useTrans } from '@/composables/useTrans';
import { stripHtml } from '@/lib/format';
import { home } from '@/routes';
import * as shop from '@/routes/shop';
import type { AttributeFilter, Category, Product } from '@/types/shop';

type Paginated<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    total: number;
    links: PaginatorLink[];
};

type Filters = {
    sort: string;
    attrs: Record<string, string[]>;
};

const props = defineProps<{
    category: Category;
    children: Category[];
    products: Paginated<Product>;
    attributeFilters: AttributeFilter[];
    filters: Filters;
}>();

const { t } = useTrans();

const sort = ref<string>(props.filters.sort);
const filtersOpen = ref<boolean>(false);

const crumbs = computed(() => [
    { label: t('shop.nav.home'), href: home.url() },
    { label: t('shop.nav.categories'), href: shop.categories.url() },
    { label: props.category.name },
]);

const description = computed<string>(() =>
    stripHtml(props.category.description),
);

const hasAttributeFilters = computed<boolean>(
    () => props.attributeFilters.length > 0,
);

const selectedAttrs = computed<Record<string, string[]>>(
    () => props.filters.attrs ?? {},
);

function visit(
    attrs: Record<string, string[]>,
    nextSort: string = sort.value,
): void {
    filtersOpen.value = false;

    router.get(
        shop.category.url({ category: props.category.slug }),
        { sort: nextSort, attrs },
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

function toggleAttr(slug: string, key: string): void {
    const next = { ...selectedAttrs.value };
    const values = [...(next[slug] ?? [])];
    const index = values.indexOf(key);

    if (index === -1) {
        values.push(key);
    } else {
        values.splice(index, 1);
    }

    if (values.length === 0) {
        delete next[slug];
    } else {
        next[slug] = values;
    }

    visit(next);
}

function clearAttrs(): void {
    visit({});
}

watch(sort, (value) => {
    visit(selectedAttrs.value, value);
});
</script>

<template>
    <Head :title="category.name" />

    <PageHead
        :title="category.name"
        :description="description"
        :crumbs="crumbs"
    />

    <Container class="py-10 md:py-14">
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
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
                <CategoryTile
                    v-for="child in children"
                    :key="child.id"
                    :category="child"
                />
            </div>
        </section>

        <div
            :class="
                hasAttributeFilters
                    ? 'grid gap-10 lg:grid-cols-[260px_1fr]'
                    : undefined
            "
        >
            <CategoryAttributeFilters
                v-if="hasAttributeFilters"
                class="hidden lg:block"
                :attributes="attributeFilters"
                :selected="selectedAttrs"
                @toggle="toggleAttr"
                @clear="clearAttrs"
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
                                :aria-label="t('shop.category.sort_aria')"
                            >
                                <SelectValue
                                    :placeholder="
                                        t('shop.category.sort_placeholder')
                                    "
                                />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="latest">
                                    {{ t('shop.category.sort.newest') }}
                                </SelectItem>
                                <SelectItem value="name">
                                    {{ t('shop.category.sort.name') }}
                                </SelectItem>
                            </SelectContent>
                        </Select>

                        <Button
                            v-if="hasAttributeFilters"
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
                        {{ t('shop.category.empty') }}
                    </h3>
                </div>

                <template v-else>
                    <div
                        :class="
                            hasAttributeFilters
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
                        :label="t('shop.category.pagination')"
                    />
                </template>
            </div>
        </div>
    </Container>

    <Sheet v-if="hasAttributeFilters" v-model:open="filtersOpen">
        <SheetContent side="left" class="w-[88vw] gap-0 sm:max-w-sm">
            <SheetHeader class="border-b border-rule p-5">
                <SheetTitle class="font-heading text-md font-bold">
                    {{ t('shop.filters.title') }}
                </SheetTitle>
                <SheetDescription class="text-sm text-ink-mute">
                    {{ t('shop.filters.attributes_description') }}
                </SheetDescription>
            </SheetHeader>

            <div class="flex-1 overflow-y-auto p-5">
                <CategoryAttributeFilters
                    class="border-0 p-0"
                    :attributes="attributeFilters"
                    :selected="selectedAttrs"
                    @toggle="toggleAttr"
                    @clear="clearAttrs"
                />
            </div>
        </SheetContent>
    </Sheet>
</template>
