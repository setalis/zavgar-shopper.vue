<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Check } from 'lucide-vue-next';
import Container from '@/components/shop/container.vue';
import TrustBadges from '@/components/shop/trust-badges.vue';
import { Button } from '@/components/ui/button';
import { useTrans } from '@/composables/useTrans';
import { formatMoney } from '@/lib/format';
import { orders as accountOrders } from '@/routes/account';
import * as shop from '@/routes/shop';

type OrderStatusLike = string | { value?: string; label?: string } | null;

type Order = {
    id: number;
    number: string;
    price_amount: number;
    currency_code: string;
    status: OrderStatusLike;
};

const props = defineProps<{ order: Order }>();

const { t } = useTrans();

function statusLabel(status: OrderStatusLike): string {
    if (!status) {
        return '';
    }

    if (typeof status === 'string') {
        return (
            status.charAt(0).toUpperCase() + status.slice(1).replace(/_/g, ' ')
        );
    }

    if (typeof status === 'object') {
        const raw = status.label ?? status.value ?? '';

        return raw.charAt(0).toUpperCase() + raw.slice(1).replace(/_/g, ' ');
    }

    return '';
}
</script>

<template>
    <Head :title="t('shop.checkout.success.title')" />

    <Container class="py-14 md:py-20">
        <div class="mx-auto max-w-2xl text-center">
            <div class="flex justify-center">
                <span
                    class="grid size-16 place-items-center rounded-full bg-emerald/12 text-emerald"
                >
                    <Check class="size-8" aria-hidden="true" />
                </span>
            </div>

            <h1 class="mt-6 text-2xl md:text-3xl">
                {{ t('shop.checkout.success.heading') }}
            </h1>
            <p class="mt-3 text-md text-ink-mute">
                {{
                    t('shop.checkout.success.thank_you', {
                        number: props.order.number,
                    })
                }}
            </p>

            <div
                class="mt-8 rounded-lg border border-rule bg-paper p-6 text-left"
            >
                <h2
                    class="font-mono text-xs tracking-[0.08em] text-ink-mute uppercase"
                >
                    {{ t('shop.checkout.success.details') }}
                </h2>
                <dl class="mt-4 space-y-3">
                    <div
                        class="flex items-center justify-between border-b border-rule pb-3"
                    >
                        <dt class="text-sm text-ink-mute">
                            {{ t('shop.checkout.success.order_number') }}
                        </dt>
                        <dd class="font-mono text-sm font-semibold text-ink">
                            {{ props.order.number }}
                        </dd>
                    </div>
                    <div
                        class="flex items-center justify-between border-b border-rule pb-3"
                    >
                        <dt class="text-sm text-ink-mute">
                            {{ t('shop.checkout.success.total') }}
                        </dt>
                        <dd class="font-heading text-md font-bold text-ink">
                            {{
                                formatMoney(
                                    props.order.price_amount,
                                    props.order.currency_code,
                                )
                            }}
                        </dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-sm text-ink-mute">
                            {{ t('shop.checkout.success.status') }}
                        </dt>
                        <dd class="text-sm font-semibold text-ink">
                            {{ statusLabel(props.order.status) }}
                        </dd>
                    </div>
                </dl>
            </div>

            <div
                class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row"
            >
                <Button as-child>
                    <Link :href="accountOrders.url()">
                        {{ t('shop.checkout.success.view_orders') }}
                    </Link>
                </Button>
                <Button as-child variant="outline">
                    <Link :href="shop.index.url()">
                        {{ t('shop.checkout.success.continue_shopping') }}
                    </Link>
                </Button>
            </div>
        </div>

        <div class="mt-14">
            <TrustBadges />
        </div>
    </Container>
</template>
