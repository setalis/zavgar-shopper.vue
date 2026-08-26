<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Search } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
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
import type { Brand, Product } from '@/types/shop';

type Paginated<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    total: number;
    links: PaginatorLink[];
};

const props = defineProps<{
    brand: Brand;
    products: Paginated<Product>;
    filters: { sort: string };
}>();

const { t } = useTrans();

const sort = ref<string>(props.filters.sort);

const crumbs = computed(() => [
    { label: t('shop.nav.home'), href: home.url() },
    { label: t('shop.nav.shop'), href: shop.index.url() },
    { label: props.brand.name },
]);

const description = computed<string>(() => stripHtml(props.brand.description));

watch(sort, (value) => {
    router.get(
        shop.brand.url({ brand: props.brand.slug ?? '' }),
        { sort: value },
        { preserveState: true, preserveScroll: true, replace: true },
    );
});
</script>

<template>
    <Head :title="brand.name" />

    <PageHead :title="brand.name" :description="description" :crumbs="crumbs">
        <img
            v-if="brand.thumbnail"
            :src="brand.thumbnail"
            :alt="brand.name"
            class="mt-6 h-10 max-w-40 object-contain object-left"
        />
    </PageHead>

    <Container class="py-10 md:py-14">
        <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
            <span class="font-mono text-xs tracking-[0.04em] text-ink-mute">
                {{ t('shop.index.results_count', { count: products.total }) }}
            </span>

            <Select v-if="products.data.length" v-model="sort">
                <SelectTrigger
                    class="w-auto rounded-sm"
                    :aria-label="t('shop.brand_page.sort_aria')"
                >
                    <SelectValue
                        :placeholder="t('shop.brand_page.sort_placeholder')"
                    />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="latest">
                        {{ t('shop.brand_page.sort.newest') }}
                    </SelectItem>
                    <SelectItem value="name">
                        {{ t('shop.brand_page.sort.name') }}
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
                {{ t('shop.brand_page.empty') }}
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
                :label="t('shop.brand_page.pagination')"
            />
        </template>
    </Container>
</template>
