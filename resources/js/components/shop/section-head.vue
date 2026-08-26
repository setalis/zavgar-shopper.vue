<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight } from 'lucide-vue-next';

defineProps<{
    title: string;
    description?: string;
    viewAllHref?: string;
    viewAllLabel?: string;
}>();
</script>

<template>
    <div class="mb-7 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h2 class="text-xl tracking-[-0.02em] md:text-2xl">
                {{ title }}
            </h2>
            <p v-if="description" class="mt-1.5 text-sm text-ink-mute">
                {{ description }}
            </p>
        </div>

        <Link
            v-if="viewAllHref"
            :href="viewAllHref"
            class="group inline-flex items-center gap-1.5 text-sm font-semibold text-brand transition-all hover:gap-3"
        >
            {{ viewAllLabel }}
            <ArrowRight class="size-4" aria-hidden="true" />
        </Link>
    </div>
</template>
