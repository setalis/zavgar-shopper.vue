<script setup lang="ts">
import { computed } from 'vue';
import type { OrderStatus, PaymentStatus, ShippingStatus } from '@/types/shop';

type StatusType = 'order' | 'payment' | 'shipping';

const props = defineProps<{
    status: OrderStatus | PaymentStatus | ShippingStatus | string;
    type?: StatusType;
}>();

const colorMap: Record<string, string> = {
    pending: 'bg-amber-100 text-amber-800',
    confirmed: 'bg-brand-soft text-brand-deep',
    processing: 'bg-brand-soft text-brand-deep',
    completed: 'bg-emerald-100 text-emerald-800',
    paid: 'bg-emerald-100 text-emerald-800',
    authorized: 'bg-emerald-100 text-emerald-800',
    shipped: 'bg-brand-soft text-brand-deep',
    in_transit: 'bg-brand-soft text-brand-deep',
    delivered: 'bg-emerald-100 text-emerald-800',
    cancelled: 'bg-rose-100 text-rose-800',
    failed: 'bg-rose-100 text-rose-800',
    refunded: 'bg-muted text-ink-soft',
    partially_paid: 'bg-amber-100 text-amber-800',
    voided: 'bg-muted text-ink-soft',
    returned: 'bg-orange-100 text-orange-800',
};

const classes = computed<string>(
    () => colorMap[props.status] ?? 'bg-muted text-ink-soft',
);

const label = computed<string>(() =>
    props.status
        .toString()
        .split('_')
        .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' '),
);
</script>

<template>
    <span
        :class="[
            'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium',
            classes,
        ]"
    >
        {{ label }}
    </span>
</template>
