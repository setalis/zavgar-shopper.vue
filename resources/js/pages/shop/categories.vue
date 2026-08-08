<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import CategoryTile from '@/components/shop/category-tile.vue';
import Container from '@/components/shop/container.vue';
import { useTrans } from '@/composables/useTrans';
import type { Category } from '@/types/shop';

defineProps<{
    categories: Category[];
}>();

const { t } = useTrans();
</script>

<template>
    <Head :title="t('shop.categories.title')" />

    <Container class="py-8 sm:py-12">
        <div class="text-center">
            <h1
                class="font-heading text-3xl font-bold text-zinc-900 dark:text-white"
            >
                {{ t('shop.categories.heading') }}
            </h1>
            <p class="mt-2 text-sm text-zinc-500">
                {{ t('shop.categories.subtitle') }}
            </p>
        </div>

        <div
            v-if="!categories.length"
            class="mt-16 flex flex-col items-center justify-center text-center"
        >
            <h3 class="text-sm font-medium text-zinc-900 dark:text-white">
                {{ t('shop.categories.empty') }}
            </h3>
        </div>

        <div
            v-else
            class="mt-10 grid grid-cols-2 gap-6 sm:grid-cols-3 lg:grid-cols-4"
        >
            <CategoryTile
                v-for="category in categories"
                :key="category.id"
                :category="category"
            />
        </div>
    </Container>
</template>
