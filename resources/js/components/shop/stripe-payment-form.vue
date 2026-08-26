<script setup lang="ts">
import { Lock } from 'lucide-vue-next';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { useShop } from '@/composables/useShop';
import { useStripeElements } from '@/composables/useStripeElements';
import { useTrans } from '@/composables/useTrans';
import { formatMoney } from '@/lib/format';

const props = defineProps<{
    clientSecret: string;
    publishableKey: string;
    returnUrl: string;
    total?: number;
}>();

const mount = ref<HTMLElement | null>(null);
const { currency, taxLabel } = useShop();
const { t } = useTrans();

const { submitting, error, confirm } = useStripeElements(
    {
        clientSecret: props.clientSecret,
        publishableKey: props.publishableKey,
    },
    () => mount.value,
);

async function pay(): Promise<void> {
    await confirm(props.returnUrl);
}
</script>

<template>
    <div class="space-y-4">
        <div ref="mount" class="min-h-[240px]" />

        <p v-if="error" class="text-sm text-destructive">{{ error }}</p>

        <div class="flex flex-col gap-3 border-t border-rule pt-5">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div v-if="total !== undefined" class="flex flex-col">
                    <span class="font-mono text-xs text-ink-mute">
                        {{ t('shop.stripe.total') }} {{ taxLabel }}
                    </span>
                    <span class="font-heading text-xl font-extrabold text-ink">
                        {{ formatMoney(total, currency) }}
                    </span>
                </div>
                <Button
                    type="button"
                    size="lg"
                    :disabled="submitting"
                    @click="pay"
                >
                    {{
                        submitting
                            ? t('shop.stripe.processing')
                            : t('shop.stripe.pay_now')
                    }}
                </Button>
            </div>
            <p
                class="inline-flex items-center gap-1.5 font-mono text-xs text-ink-mute"
            >
                <Lock class="size-3" aria-hidden="true" />
                {{ t('shop.stripe.secure') }}
            </p>
        </div>
    </div>
</template>
