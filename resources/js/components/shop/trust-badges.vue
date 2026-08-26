<script setup lang="ts">
import { MessageCircle, RotateCcw, ShieldCheck, Truck } from 'lucide-vue-next';
import { computed } from 'vue';
import { useTrans } from '@/composables/useTrans';

withDefaults(
    defineProps<{
        layout?: 'grid' | 'stack';
    }>(),
    { layout: 'grid' },
);

const { t } = useTrans();

const badges = computed(() => [
    {
        icon: Truck,
        title: t('shop.trust.free_shipping.title'),
        description: t('shop.trust.free_shipping.description'),
    },
    {
        icon: ShieldCheck,
        title: t('shop.trust.secure_payment.title'),
        description: t('shop.trust.secure_payment.description'),
    },
    {
        icon: RotateCcw,
        title: t('shop.trust.easy_returns.title'),
        description: t('shop.trust.easy_returns.description'),
    },
    {
        icon: MessageCircle,
        title: t('shop.trust.support.title'),
        description: t('shop.trust.support.description'),
    },
]);
</script>

<template>
    <div
        :class="
            layout === 'grid'
                ? 'grid gap-4 sm:grid-cols-2 lg:grid-cols-4'
                : 'grid gap-4 sm:grid-cols-2'
        "
    >
        <div
            v-for="badge in badges"
            :key="badge.title"
            class="flex items-start gap-3 rounded-lg border border-rule bg-paper p-4"
        >
            <span
                class="grid size-10 shrink-0 place-items-center rounded-full bg-brand-soft text-brand"
            >
                <component :is="badge.icon" class="size-5" aria-hidden="true" />
            </span>
            <div>
                <h3 class="font-heading text-sm font-bold text-ink">
                    {{ badge.title }}
                </h3>
                <p class="mt-0.5 text-xs text-ink-mute">
                    {{ badge.description }}
                </p>
            </div>
        </div>
    </div>
</template>
