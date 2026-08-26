<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Lock } from 'lucide-vue-next';
import { ref } from 'vue';
import Container from '@/components/shop/container.vue';
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

    <Container class="py-14 md:py-20">
        <div class="mx-auto max-w-2xl">
            <h1 class="text-2xl">
                {{ t('shop.stripe_payment.heading') }}
            </h1>
            <p class="mt-2 text-sm text-ink-mute">
                {{ t('shop.stripe_payment.order') }}
                <span class="font-mono text-ink">{{ order.number }}</span>
            </p>

            <form
                class="mt-8 space-y-4 rounded-lg border border-rule bg-paper p-6 md:p-8"
                @submit.prevent="pay"
            >
                <div ref="paymentElementRef" />

                <p v-if="error" class="text-sm text-destructive">
                    {{ error }}
                </p>

                <Button
                    type="submit"
                    block
                    size="lg"
                    :disabled="!ready || submitting"
                >
                    <span v-if="submitting">
                        {{ t('shop.stripe_payment.processing') }}
                    </span>
                    <span v-else>{{ t('shop.stripe_payment.pay_now') }}</span>
                </Button>

                <p
                    class="inline-flex items-center gap-1.5 font-mono text-xs text-ink-mute"
                >
                    <Lock class="size-3" aria-hidden="true" />
                    {{ t('shop.checkout.secure_encrypted') }}
                </p>
            </form>
        </div>
    </Container>
</template>
