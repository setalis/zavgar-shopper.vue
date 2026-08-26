<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { LayoutGrid } from 'lucide-vue-next';
import * as shop from '@/routes/shop';
import type { Category } from '@/types/shop';

defineProps<{ category: Category }>();
</script>

<template>
    <Link
        :href="shop.category.url({ category: category.slug })"
        class="group flex flex-col items-center"
    >
        <div
            class="grid size-24 place-items-center overflow-hidden rounded-full bg-muted ring-2 ring-transparent transition group-hover:ring-brand"
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
                class="size-8 text-ink-faint"
                aria-hidden="true"
            />
        </div>
        <span
            class="mt-3 text-center font-heading text-sm font-semibold text-ink transition group-hover:text-brand"
        >
            {{ category.name }}
        </span>
    </Link>
</template>
