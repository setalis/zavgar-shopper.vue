<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useTrans } from '@/composables/useTrans';
import * as shop from '@/routes/shop';
import type { Brand } from '@/types/shop';

const props = defineProps<{
    brand: Brand;
}>();

const { t } = useTrans();

const href = computed<string | null>(() => {
    if (!props.brand.slug || props.brand.is_enabled === false) {
        return null;
    }

    return shop.brand.url({ brand: props.brand.slug });
});

const label = computed(() =>
    t('shop.product.brand_products', { name: props.brand.name }),
);
</script>

<template>
    <component
        :is="href ? Link : 'span'"
        v-bind="href ? { href, 'aria-label': label } : {}"
        class="relative z-10 mt-0.5 inline-flex max-w-full items-center"
        :class="href ? 'transition hover:opacity-80' : ''"
    >
        <img
            v-if="brand.thumbnail"
            :src="brand.thumbnail"
            :alt="brand.name"
            loading="lazy"
            class="h-5 max-w-24 object-contain object-left"
        />
        <span
            v-else
            class="font-mono text-[11px] tracking-[0.04em] text-ink-mute"
        >
            {{ brand.name }}
        </span>
    </component>
</template>
