<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import Container from '@/components/shop/container.vue';

export type Crumb = { label: string; href?: string };

withDefaults(
    defineProps<{
        title: string;
        description?: string;
        crumbs?: Crumb[];
        tone?: 'muted' | 'paper';
    }>(),
    { crumbs: () => [], tone: 'muted' },
);
</script>

<template>
    <section
        :class="[
            'border-b border-rule py-10 md:py-14',
            tone === 'muted' ? 'bg-muted' : 'bg-paper',
        ]"
    >
        <Container>
            <nav
                v-if="crumbs.length > 0"
                class="mb-3 flex flex-wrap items-center gap-1.5 font-mono text-xs tracking-[0.04em] text-ink-mute"
                aria-label="Breadcrumb"
            >
                <template v-for="(crumb, index) in crumbs" :key="crumb.label">
                    <span
                        v-if="index > 0"
                        aria-hidden="true"
                        class="text-ink-faint"
                    >
                        /
                    </span>
                    <Link
                        v-if="crumb.href"
                        :href="crumb.href"
                        class="transition hover:text-brand"
                    >
                        {{ crumb.label }}
                    </Link>
                    <span v-else aria-current="page" class="text-ink">
                        {{ crumb.label }}
                    </span>
                </template>
            </nav>

            <h1 class="text-2xl md:text-3xl">{{ title }}</h1>

            <p
                v-if="description"
                class="mt-3 max-w-[68ch] text-base text-ink-mute"
            >
                {{ description }}
            </p>

            <slot />
        </Container>
    </section>
</template>
