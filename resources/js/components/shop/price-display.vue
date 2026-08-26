<script setup lang="ts">
import { computed } from 'vue';
import { useFormat } from '@/composables/useFormat';
import { useShop } from '@/composables/useShop';
import { useTrans } from '@/composables/useTrans';
import type { StorefrontPrice } from '@/types/shop';

type Size = 'sm' | 'md' | 'lg';

const props = withDefaults(
    defineProps<{
        price: StorefrontPrice | null;
        size?: Size;
        showSaveBadge?: boolean;
    }>(),
    { size: 'sm', showSaveBadge: false },
);

const { currency, taxLabel } = useShop();
const { t } = useTrans();
const { money } = useFormat();

const nowClass = computed<string>(
    () =>
        ({
            lg: 'text-2xl',
            md: 'text-md',
            sm: 'text-base',
        })[props.size],
);

const wasClass = computed<string>(
    () =>
        ({
            lg: 'text-base',
            md: 'text-sm',
            sm: 'text-xs',
        })[props.size],
);

const percentage = computed<number | null>(() => {
    if (
        !props.price?.compare_amount ||
        props.price.compare_amount <= props.price.amount
    ) {
        return null;
    }

    return Math.round(
        ((props.price.compare_amount - props.price.amount) /
            props.price.compare_amount) *
            100,
    );
});

const saved = computed<number | null>(() => {
    if (!props.price?.compare_amount) {
        return null;
    }

    return props.price.compare_amount - props.price.amount;
});
</script>

<template>
    <div v-if="price" class="flex flex-wrap items-baseline gap-2">
        <span :class="['font-heading font-bold text-ink', nowClass]">
            {{
                (price.from ? `${t('shop.price.from')} ` : '') +
                money(price.amount, currency)
            }}
        </span>

        <span
            v-if="percentage"
            :class="['font-mono text-ink-faint line-through', wasClass]"
        >
            <span class="sr-only">{{ t('shop.price.original_sr') }}</span>
            {{ money(price.compare_amount ?? 0, currency) }}
        </span>

        <span
            v-if="percentage && showSaveBadge"
            class="inline-flex items-center rounded-sm bg-emerald/12 px-2 py-1 font-mono text-[11px] font-semibold tracking-[0.04em] text-emerald"
        >
            {{
                t('shop.price.save', {
                    amount: money(saved ?? 0, currency),
                })
            }}
        </span>
        <span
            v-else-if="percentage"
            class="inline-flex items-center rounded-sm bg-rose/10 px-2 py-0.5 font-mono text-[11px] font-semibold text-rose"
        >
            -{{ percentage }}%
        </span>

        <span v-if="taxLabel" class="font-mono text-[11px] text-ink-mute">
            {{ taxLabel }}
        </span>
    </div>
    <p v-else class="font-heading font-bold text-ink-mute">
        {{ t('shop.price.unavailable') }}
    </p>
</template>
