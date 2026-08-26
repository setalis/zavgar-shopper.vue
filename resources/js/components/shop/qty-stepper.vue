<script setup lang="ts">
import { Minus, Plus } from 'lucide-vue-next';
import {
    NumberField,
    NumberFieldDecrement,
    NumberFieldIncrement,
    NumberFieldInput,
} from '@/components/ui/number-field';
import { useTrans } from '@/composables/useTrans';

const props = withDefaults(
    defineProps<{
        min?: number;
        max?: number;
        disabled?: boolean;
    }>(),
    { min: 1, max: 99, disabled: false },
);

const quantity = defineModel<number>({ required: true });

const { t } = useTrans();

const stepperButtonClass =
    'static grid size-9 translate-y-0 place-items-center rounded-full p-0 text-ink transition hover:bg-brand-soft hover:text-brand disabled:opacity-40';
</script>

<template>
    <NumberField
        v-model="quantity"
        :min="props.min"
        :max="props.max"
        :disabled="props.disabled"
        :aria-label="t('shop.product.quantity')"
        class="w-fit"
    >
        <div
            class="inline-flex items-center gap-1 rounded-full border border-rule-strong bg-paper p-1"
        >
            <NumberFieldDecrement :class="stepperButtonClass">
                <Minus class="size-4" aria-hidden="true" />
            </NumberFieldDecrement>

            <NumberFieldInput
                class="h-9 w-10 rounded-none border-0 bg-transparent p-0 text-center font-heading text-sm font-bold text-ink shadow-none focus-visible:border-0 focus-visible:ring-0"
            />

            <NumberFieldIncrement :class="stepperButtonClass">
                <Plus class="size-4" aria-hidden="true" />
            </NumberFieldIncrement>
        </div>
    </NumberField>
</template>
