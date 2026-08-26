<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { LayoutGrid } from 'lucide-vue-next';
import { computed } from 'vue';
import { useTrans } from '@/composables/useTrans';
import * as shop from '@/routes/shop';
import type { Category } from '@/types/shop';

const props = defineProps<{
    category: Category;
    href?: string;
}>();

const { t } = useTrans();

const categoryHref = computed<string>(
    () => props.href ?? shop.category.url({ category: props.category.slug }),
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
        class="group rounded-lg border border-rule bg-paper p-5 text-center transition duration-200 ease-brand hover:-translate-y-[3px] hover:border-brand-line hover:bg-brand-soft"
    >
        <div
            class="mx-auto mb-3 grid size-20 place-items-center overflow-hidden rounded-full bg-muted"
        >
            <img
                v-if="category.thumbnail"
                :src="category.thumbnail"
                :alt="category.name"
                loading="lazy"
                class="size-full object-cover object-center"
            />
            <LayoutGrid
                v-else
                class="size-7 text-ink-faint"
                aria-hidden="true"
            />
        </div>

        <span class="block font-heading text-sm font-bold text-ink">
            {{ category.name }}
        </span>
        <span
            v-if="category.products_count !== undefined"
            class="mt-1 block font-mono text-[11px] text-ink-mute"
        >
            {{ productLabel(category.products_count) }}
        </span>
    </Link>
</template>
