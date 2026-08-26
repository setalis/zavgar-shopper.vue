<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import CategoryTile from '@/components/shop/category-tile.vue';
import Container from '@/components/shop/container.vue';
import PageHead from '@/components/shop/page-head.vue';
import { useTrans } from '@/composables/useTrans';
import { home } from '@/routes';
import type { Category } from '@/types/shop';

defineProps<{
    categories: Category[];
}>();

const { t } = useTrans();

const crumbs = computed(() => [
    { label: t('shop.nav.home'), href: home.url() },
    { label: t('shop.categories.heading') },
]);
</script>

<template>
    <Head :title="t('shop.categories.title')" />

    <PageHead
        :title="t('shop.categories.heading')"
        :description="t('shop.categories.subtitle')"
        :crumbs="crumbs"
    />

    <Container class="py-10 md:py-14">
        <div
            v-if="!categories.length"
            class="flex flex-col items-center justify-center rounded-lg border border-rule bg-paper py-20 text-center"
        >
            <h3 class="font-heading text-md font-bold text-ink">
                {{ t('shop.categories.empty') }}
            </h3>
        </div>

        <div
            v-else
            class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5"
        >
            <CategoryTile
                v-for="category in categories"
                :key="category.id"
                :category="category"
            />
        </div>
    </Container>
</template>
