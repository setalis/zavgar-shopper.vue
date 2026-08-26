<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import BrandIcon from '@/components/shop/brand-icon.vue';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();

const page = usePage();
</script>

<template>
    <div
        class="flex min-h-svh flex-col items-center justify-center gap-6 bg-muted p-6 md:p-10"
    >
        <div class="w-full max-w-md">
            <div class="flex flex-col gap-8">
                <div class="flex flex-col items-center gap-4">
                    <Link
                        :href="home.url()"
                        class="inline-flex items-center gap-2 font-heading text-xl font-extrabold tracking-[-0.02em] text-ink"
                    >
                        <BrandIcon class="h-9 w-auto fill-current text-brand" />
                        {{ page.props.name }}
                    </Link>
                </div>

                <div
                    class="rounded-xl border border-rule bg-paper p-6 shadow-sm md:p-8"
                >
                    <div class="mb-6 space-y-2 text-center">
                        <h1 class="text-xl">{{ title }}</h1>
                        <p v-if="description" class="text-sm text-ink-mute">
                            {{ description }}
                        </p>
                    </div>

                    <slot />
                </div>
            </div>
        </div>
    </div>
</template>
