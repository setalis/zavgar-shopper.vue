<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { useStripeElements } from '@/composables/useStripeElements';
import { useTrans } from '@/composables/useTrans';
import type { Order } from '@/types/shop';

const props = defineProps<{
    order: Order;
    clientSecret: string;
    publishableKey: string;
    returnUrl: string;
}>();

const paymentElementRef = ref<HTMLDivElement | null>(null);

const { t } = useTrans();

const { ready, submitting, error, confirm } = useStripeElements(
    {
        publishableKey: props.publishableKey,
        clientSecret: props.clientSecret,
    },
    () => paymentElementRef.value,
);

async function pay(): Promise<void> {
    await confirm(props.returnUrl);
}
</script>

<template>
    <Head :title="t('shop.stripe_payment.title')" />

    <div class="mx-auto max-w-2xl px-4 py-12 sm:px-6 lg:px-8">
        <h1
            class="font-heading text-2xl font-semibold text-zinc-900 dark:text-white"
        >
            {{ t('shop.stripe_payment.heading') }}
        </h1>
        <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">
            {{ t('shop.stripe_payment.order') }}
            <span class="font-mono">{{ order.number }}</span>
        </p>

        <form class="mt-8 space-y-4" @submit.prevent="pay">
            <div ref="paymentElementRef" />

            <p v-if="error" class="text-sm text-red-600">{{ error }}</p>

            <Button
                type="submit"
                :disabled="!ready || submitting"
                class="w-full"
            >
                <span v-if="submitting">{{ t('shop.stripe_payment.processing') }}</span>
                <span v-else>{{ t('shop.stripe_payment.pay_now') }}</span>
            </Button>
        </form>
    </div>
</template>
