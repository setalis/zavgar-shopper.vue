<script setup lang="ts">
import { Star } from 'lucide-vue-next';
import { computed } from 'vue';
import { useTrans } from '@/composables/useTrans';

const props = withDefaults(
    defineProps<{
        rating?: number;
        count?: number | null;
        size?: 'sm' | 'md';
    }>(),
    { rating: 5, count: null, size: 'sm' },
);

const { t } = useTrans();

const stars = computed<number[]>(() => [1, 2, 3, 4, 5]);

const iconClass = computed<string>(() =>
    props.size === 'md' ? 'size-4' : 'size-3.5',
);
</script>

<template>
    <span
        class="inline-flex items-center gap-px text-amber"
        :aria-label="t('shop.product.rating_sr', { rating: props.rating })"
    >
        <Star
            v-for="star in stars"
            :key="star"
            :class="[
                iconClass,
                star <= Math.round(props.rating)
                    ? 'fill-current'
                    : 'text-rule-strong',
            ]"
            aria-hidden="true"
        />
        <span
            v-if="props.count !== null"
            class="ml-1.5 font-mono text-[11px] text-ink-mute"
        >
            ({{ props.count }})
        </span>
    </span>
</template>
