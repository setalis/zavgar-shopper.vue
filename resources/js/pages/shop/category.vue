<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Search } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import CategoryTile from '@/components/shop/category-tile.vue';
import Container from '@/components/shop/container.vue';
import PageHead from '@/components/shop/page-head.vue';
import ProductCard from '@/components/shop/product-card.vue';
import ProductPagination from '@/components/shop/product-pagination.vue';
import type { PaginatorLink } from '@/components/shop/product-pagination.vue';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useTrans } from '@/composables/useTrans';
import { stripHtml } from '@/lib/format';
import { home } from '@/routes';
import * as shop from '@/routes/shop';
import type { Category, Product } from '@/types/shop';

type Paginated<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    total: number;
    links: PaginatorLink[];
};

const props = defineProps<{
    category: Category;
    children: Category[];
    products: Paginated<Product>;
    filters: { sort: string };
}>();

const { t } = useTrans();

const sort = ref<string>(props.filters.sort);

const crumbs = computed(() => [
    { label: t('shop.nav.home'), href: home.url() },
    { label: t('shop.nav.categories'), href: shop.categories.url() },
    { label: props.category.name },
]);

const description = computed<string>(() =>
    stripHtml(props.category.description),
);

watch(sort, (value) => {
    router.get(
        shop.category.url({ category: props.category.slug }),
        { sort: value },
        { preserveState: true, preserveScroll: true, replace: true },
    );
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

        <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
            <span class="font-mono text-xs tracking-[0.04em] text-ink-mute">
                {{ t('shop.index.results_count', { count: products.total }) }}
            </span>

            <Select v-if="products.data.length" v-model="sort">
                <SelectTrigger
                    class="w-auto rounded-sm"
                    :aria-label="t('shop.category.sort_aria')"
                >
                    <SelectValue
                        :placeholder="t('shop.category.sort_placeholder')"
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
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
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
    </Container>
</template>
