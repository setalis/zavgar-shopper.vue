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
    }>(),
    { size: 'sm' },
);

const { currency, taxLabel } = useShop();
const { t } = useTrans();
const { money } = useFormat();

const textSize = computed<string>(() => {
    return {
        lg: 'text-2xl',
        md: 'text-lg',
        sm: 'text-sm',
    }[props.size];
});

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
</script>

<template>
    <div :class="textSize">
        <template v-if="price">
            <p class="flex items-center gap-2">
                <span class="font-semibold text-zinc-900 dark:text-white">
                    {{
                        (price.from ? `${t('shop.price.from')} ` : '') +
                        money(price.amount, currency)
                    }}
                </span>
                <span v-if="taxLabel" class="text-xs text-zinc-500">{{
                    taxLabel
                }}</span>
            </p>

            <p
                v-if="percentage"
                class="mt-0.5 flex items-center gap-1.5 sm:mt-0 sm:inline-flex"
            >
                <span class="sr-only">{{ t('shop.price.original_sr') }}</span>
                <span class="text-zinc-400 line-through">{{
                    money(price.compare_amount ?? 0, currency)
                }}</span>
                <span
                    class="inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700"
                >
                    -{{ percentage }}%
                </span>
            </p>
        </template>
        <p v-else class="font-semibold text-zinc-900 dark:text-white">
            {{ t('shop.price.unavailable') }}
        </p>
    </div>
</template>
