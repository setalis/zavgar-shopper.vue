<script setup lang="ts">
import { MessageCircle, RotateCcw, ShieldCheck, Truck } from 'lucide-vue-next';
import { computed } from 'vue';
import { useTrans } from '@/composables/useTrans';

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
    <div class="grid grid-cols-2 gap-6 sm:grid-cols-4">
        <div
            v-for="badge in badges"
            :key="badge.title"
            class="flex flex-col items-center text-center"
        >
            <div
                class="flex size-12 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800"
            >
                <component
                    :is="badge.icon"
                    class="size-6 text-zinc-600 dark:text-zinc-400"
                />
            </div>
            <h3
                class="mt-3 font-heading text-sm font-medium text-zinc-900 dark:text-white"
            >
                {{ badge.title }}
            </h3>
            <p class="mt-1 text-xs text-zinc-500">{{ badge.description }}</p>
        </div>
    </div>
</template>
