<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Slider } from '@/components/ui/slider';
import { useFormat } from '@/composables/useFormat';
import { useShop } from '@/composables/useShop';
import { useTrans } from '@/composables/useTrans';
import { isNoDivisionCurrency } from '@/lib/format';
import type { PriceRange } from '@/types/shop';

const props = defineProps<{
    bounds: PriceRange;
    priceMin: number | null;
    priceMax: number | null;
}>();

const emit = defineEmits<{
    change: [min: number | null, max: number | null];
}>();

const { t } = useTrans();
const { currency } = useShop();
const { money } = useFormat();

const step = computed<number>(() =>
    isNoDivisionCurrency(currency.value) ? 1 : 100,
);

const collapsed = computed<boolean>(
    () => props.bounds.min === props.bounds.max,
);

function selectedRange(): number[] {
    return [
        clamp(props.priceMin ?? props.bounds.min),
        clamp(props.priceMax ?? props.bounds.max),
    ];
}

function clamp(value: number): number {
    return Math.min(Math.max(value, props.bounds.min), props.bounds.max);
}

const range = ref<number[]>(selectedRange());

let timer: ReturnType<typeof setTimeout> | null = null;

watch(
    () =>
        [props.bounds.min, props.bounds.max, props.priceMin, props.priceMax] as const,
    () => {
        range.value = selectedRange();
    },
);

function commit(values: number[]): void {
    const min = clamp(Math.min(values[0], values[1]));
    const max = clamp(Math.max(values[0], values[1]));
    const isFull = min <= props.bounds.min && max >= props.bounds.max;

    emit('change', isFull ? null : min, isFull ? null : max);
}

function onUpdate(value: number | number[] | undefined): void {
    if (!Array.isArray(value) || value.length < 2) {
        return;
    }

    range.value = [value[0], value[1]];

    if (timer) {
        clearTimeout(timer);
    }

    timer = setTimeout(() => commit(range.value), 300);
}
</script>

<template>
    <div>
        <h3
            class="mb-3 font-heading text-sm font-bold tracking-[0.06em] text-ink uppercase"
        >
            {{ t('shop.filters.price_range') }}
        </h3>

        <Slider
            v-if="!collapsed"
            :model-value="range"
            :min="bounds.min"
            :max="bounds.max"
            :step="step"
            @update:model-value="onUpdate"
        />

        <p class="mt-3 font-mono text-[11px] text-ink-mute">
            {{ money(range[0] ?? bounds.min, currency) }} —
            {{ money(range[1] ?? bounds.max, currency) }}
        </p>
    </div>
</template>
