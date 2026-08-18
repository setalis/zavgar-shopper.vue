<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useTrans } from '@/composables/useTrans';
import * as shop from '@/routes/shop';
import type { Category } from '@/types/shop';

const props = defineProps<{
    category: Category;
    href?: string;
}>();

const { t } = useTrans();

const categoryHref = computed(
    () =>
        props.href ??
        shop.category.url({ category: props.category.slug }),
);

function productLabel(count: number): string {
    return t('shop.categories.product_count', {
        count,
        label:
            count === 1
                ? t('shop.categories.product')
                : t('shop.categories.products'),
    });
}
</script>

<template>
    <Link
        :href="categoryHref"
        class="group relative overflow-hidden rounded-2xl bg-zinc-100 dark:bg-zinc-800"
    >
        <div class="aspect-4/3 p-4">
            <img
                v-if="category.thumbnail"
                :src="category.thumbnail"
                :alt="category.name"
                loading="lazy"
                class="size-full object-cover object-center transition duration-500 group-hover:scale-105"
            />
            <div
                class="absolute inset-0 bg-linear-to-t from-zinc-900/70 to-transparent"
            />
        </div>
        <div class="absolute inset-x-0 bottom-0 p-4">
            <h3 class="text-base font-semibold text-white">
                {{ category.name }}
            </h3>
            <p
                v-if="category.products_count !== undefined"
                class="mt-0.5 text-xs text-zinc-300"
            >
                {{ productLabel(category.products_count) }}
            </p>
        </div>
    </Link>
</template>
