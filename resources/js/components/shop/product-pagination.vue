<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { computed } from 'vue';
import { cn } from '@/lib/utils';

export type PaginatorLink = {
    url: string | null;
    label: string;
    active: boolean;
};

const props = defineProps<{
    links: PaginatorLink[];
    label: string;
}>();

const isVisible = computed<boolean>(() => props.links.length > 3);

/** Laravel labels the edges with HTML entities; the design uses chevrons. */
function edge(label: string): 'previous' | 'next' | null {
    if (label.includes('Previous') || label.includes('&laquo;')) {
        return 'previous';
    }

    if (label.includes('Next') || label.includes('&raquo;')) {
        return 'next';
    }

    return null;
}
</script>

<template>
    <nav
        v-if="isVisible"
        class="mt-10 flex flex-wrap justify-center gap-1.5"
        :aria-label="label"
    >
        <template v-for="(link, index) in links" :key="index">
            <component
                :is="link.url ? Link : 'span'"
                :href="link.url ?? undefined"
                :aria-current="link.active ? 'page' : undefined"
                :aria-disabled="link.url ? undefined : 'true'"
                :class="
                    cn(
                        'grid size-10 place-items-center rounded-sm border font-mono text-sm transition',
                        link.active
                            ? 'border-brand bg-primary text-paper'
                            : 'border-rule bg-paper text-ink-soft',
                        link.url
                            ? 'hover:border-brand-line hover:bg-brand-soft hover:text-brand'
                            : 'cursor-not-allowed opacity-40',
                    )
                "
            >
                <ChevronLeft
                    v-if="edge(link.label) === 'previous'"
                    class="size-4"
                    aria-hidden="true"
                />
                <ChevronRight
                    v-else-if="edge(link.label) === 'next'"
                    class="size-4"
                    aria-hidden="true"
                />
                <span v-else v-html="link.label" />
            </component>
        </template>
    </nav>
</template>
