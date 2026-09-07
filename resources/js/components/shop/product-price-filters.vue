<script setup lang="ts">
import { computed, useAttrs } from 'vue';
import PriceRangeFilter from '@/components/shop/price-range-filter.vue';
import { useTrans } from '@/composables/useTrans';
import { cn } from '@/lib/utils';
import type { PriceRange } from '@/types/shop';

defineOptions({ inheritAttrs: false });

defineProps<{
    bounds: PriceRange;
    priceMin: number | null;
    priceMax: number | null;
}>();

const emit = defineEmits<{
    change: [min: number | null, max: number | null];
}>();

const { t } = useTrans();

const attrs = useAttrs();
const rootClass = computed<string>(() =>
    cn(
        'self-start rounded-lg border border-rule bg-paper p-5 lg:sticky lg:max-h-[calc(100vh-11rem)] lg:overflow-y-auto',
        attrs.class as string,
    ),
);
</script>

<template>
    <aside
        v-bind="{ ...$attrs, class: undefined }"
        :class="rootClass"
        :style="{ top: 'calc(var(--header-h) + var(--nav-h) + 1rem)' }"
        :aria-label="t('shop.filters.title')"
    >
        <PriceRangeFilter
            :bounds="bounds"
            :price-min="priceMin"
            :price-max="priceMax"
            @change="(min, max) => emit('change', min, max)"
        />
    </aside>
</template>
