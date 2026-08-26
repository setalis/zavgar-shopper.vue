<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ShoppingBag, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import Container from '@/components/shop/container.vue';
import PageHead from '@/components/shop/page-head.vue';
import QtyStepper from '@/components/shop/qty-stepper.vue';
import TrustBadges from '@/components/shop/trust-badges.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { useCart } from '@/composables/useCart';
import { useShop } from '@/composables/useShop';
import { useTrans } from '@/composables/useTrans';
import { formatMoney } from '@/lib/format';
import { home } from '@/routes';
import * as shop from '@/routes/shop';
import * as checkout from '@/routes/shop/checkout';
import type { Cart, CartContext, Product } from '@/types/shop';

defineProps<{
    cart: Cart | null;
    cartContext: CartContext | null;
}>();

const page = usePage();
const { currency, taxLabel } = useShop();
const cartActions = useCart();
const { t } = useTrans();

/**
 * Promotion codes are applied during checkout, so the cart field mirrors the
 * template's affordance without submitting anywhere.
 */
const promoCode = ref<string>('');

const crumbs = computed(() => [
    { label: t('shop.nav.home'), href: home.url() },
    { label: t('shop.cart.heading') },
]);

function productSlug(
    purchasable: Cart['lines'][number]['purchasable'],
): string | null {
    if ('slug' in purchasable && typeof purchasable.slug === 'string') {
        return purchasable.slug;
    }

    if ('product' in purchasable) {
        const parent = (purchasable as { product?: Product }).product;

        return parent?.slug ?? null;
    }

    return null;
}

function lineName(purchasable: Cart['lines'][number]['purchasable']): string {
    return (purchasable as { name?: string }).name ?? '';
}

function confirmClear(): void {
    if (window.confirm(t('shop.cart.clear_confirm'))) {
        cartActions.clear();
    }
}
</script>

<template>
    <Head :title="t('shop.cart.title')" />

    <PageHead :title="t('shop.cart.heading')" :crumbs="crumbs" />

    <Container class="py-10 md:py-14">
        <div
            v-if="!cart || !cart.lines.length"
            class="flex flex-col items-center justify-center rounded-lg border border-rule bg-paper py-20 text-center"
        >
            <ShoppingBag class="size-12 text-ink-faint" aria-hidden="true" />
            <h2 class="mt-4 font-heading text-lg font-bold text-ink">
                {{ t('shop.cart.empty.title') }}
            </h2>
            <p class="mt-1 text-sm text-ink-mute">
                {{ t('shop.cart.empty.subtitle') }}
            </p>
            <Button as-child class="mt-6">
                <Link :href="shop.index.url()">
                    {{ t('shop.cart.continue_shopping') }}
                </Link>
            </Button>
        </div>

        <div v-else class="grid gap-10 lg:grid-cols-[1.6fr_1fr]">
            <div>
                <ul
                    role="list"
                    class="overflow-hidden rounded-lg border border-rule bg-paper"
                >
                    <li
                        v-for="(line, index) in cart.lines"
                        :key="line.id"
                        :class="[
                            'grid grid-cols-[80px_1fr] items-center gap-4 p-5 sm:grid-cols-[80px_1fr_auto_auto_auto]',
                            index > 0 && 'border-t border-rule',
                        ]"
                    >
                        <div
                            class="size-20 shrink-0 overflow-hidden rounded-sm bg-muted"
                        >
                            <img
                                v-if="line.purchasable.thumbnail"
                                :src="line.purchasable.thumbnail"
                                :alt="lineName(line.purchasable)"
                                class="size-full object-cover object-center"
                            />
                        </div>

                        <div class="min-w-0">
                            <h3
                                class="font-heading text-sm leading-tight font-semibold text-ink"
                            >
                                <Link
                                    v-if="productSlug(line.purchasable)"
                                    :href="
                                        shop.product.url({
                                            product: productSlug(
                                                line.purchasable,
                                            ) as string,
                                        })
                                    "
                                    class="transition hover:text-brand"
                                >
                                    {{ lineName(line.purchasable) }}
                                </Link>
                                <template v-else>
                                    {{ lineName(line.purchasable) }}
                                </template>
                            </h3>
                            <p class="mt-1 font-mono text-xs text-ink-mute">
                                {{
                                    formatMoney(
                                        line.unit_price_amount,
                                        currency,
                                    )
                                }}
                            </p>
                        </div>

                        <QtyStepper
                            :model-value="line.quantity"
                            class="col-start-2 sm:col-start-auto"
                            @update:model-value="
                                (value) => cartActions.update(line.id, value)
                            "
                        />

                        <p
                            class="col-start-2 font-heading text-md font-bold text-ink sm:col-start-auto sm:text-right"
                        >
                            {{
                                formatMoney(
                                    line.unit_price_amount * line.quantity,
                                    currency,
                                )
                            }}
                        </p>

                        <button
                            type="button"
                            class="col-start-2 grid size-9 place-items-center justify-self-start rounded-full text-ink-faint transition hover:bg-rose/10 hover:text-rose sm:col-start-auto sm:justify-self-auto"
                            :aria-label="t('shop.cart.remove')"
                            @click="cartActions.remove(line.id)"
                        >
                            <Trash2 class="size-4" aria-hidden="true" />
                        </button>
                    </li>
                </ul>

                <div
                    class="mt-4 flex flex-wrap items-center justify-between gap-3"
                >
                    <Link
                        :href="shop.index.url()"
                        class="text-sm text-ink-mute transition hover:text-brand"
                    >
                        {{ t('shop.cart.continue_shopping_back') }}
                    </Link>
                    <button
                        type="button"
                        class="text-sm text-destructive transition hover:opacity-80"
                        @click="confirmClear"
                    >
                        {{ t('shop.cart.clear') }}
                    </button>
                </div>

                <form
                    class="mt-7 rounded-lg border border-rule bg-paper p-5"
                    @submit.prevent
                >
                    <label
                        for="cart-promo"
                        class="mb-3 block font-mono text-xs tracking-[0.08em] text-ink-mute uppercase"
                    >
                        {{ t('shop.cart.promo.label') }}
                    </label>
                    <div class="flex flex-wrap gap-2">
                        <Input
                            id="cart-promo"
                            v-model="promoCode"
                            class="max-w-64 flex-1 rounded-sm"
                            :placeholder="t('shop.cart.promo.placeholder')"
                        />
                        <Button type="submit" variant="outline">
                            {{ t('shop.cart.promo.apply') }}
                        </Button>
                    </div>
                    <p class="mt-2 text-xs text-ink-mute">
                        {{ t('shop.cart.promo.hint') }}
                    </p>
                </form>
            </div>

            <div
                class="self-start lg:sticky"
                :style="{ top: 'calc(var(--header-h) + var(--nav-h) + 1rem)' }"
            >
                <div class="rounded-lg border border-rule bg-paper p-6">
                    <h2 class="text-lg">
                        {{ t('shop.cart.order_summary') }}
                    </h2>

                    <dl class="mt-6 space-y-3 text-sm text-ink-mute">
                        <div
                            class="flex items-center justify-between border-b border-rule pb-3"
                        >
                            <dt>{{ t('shop.cart.tax') }}</dt>
                            <dd class="font-heading font-semibold text-ink">
                                {{
                                    formatMoney(
                                        cartContext?.taxTotal ?? 0,
                                        currency,
                                    )
                                }}
                            </dd>
                        </div>

                        <div
                            class="flex items-center justify-between border-b border-rule pb-3"
                        >
                            <dt>{{ t('shop.cart.delivery') }}</dt>
                            <dd>{{ t('shop.cart.delivery_calculated') }}</dd>
                        </div>

                        <div
                            v-if="cartContext && cartContext.discountTotal > 0"
                            class="flex items-center justify-between border-b border-rule pb-3"
                        >
                            <dt>{{ t('shop.cart.discount') }}</dt>
                            <dd class="font-semibold text-emerald">
                                −{{
                                    formatMoney(
                                        cartContext.discountTotal,
                                        currency,
                                    )
                                }}
                            </dd>
                        </div>

                        <div class="flex items-center justify-between pt-1">
                            <dt class="font-heading text-md font-bold text-ink">
                                {{ t('shop.cart.subtotal') }} {{ taxLabel }}
                            </dt>
                            <dd
                                class="font-heading text-xl font-extrabold text-ink"
                            >
                                {{
                                    formatMoney(
                                        cartContext?.subtotal ?? 0,
                                        currency,
                                    )
                                }}
                            </dd>
                        </div>
                    </dl>

                    <Button as-child block size="lg" class="mt-6">
                        <Link :href="checkout.index.url()">
                            {{
                                page.props.auth.user
                                    ? t('shop.cart.proceed_checkout')
                                    : t('shop.cart.sign_in_checkout')
                            }}
                        </Link>
                    </Button>
                </div>
            </div>
        </div>
    </Container>

    <section class="border-t border-rule bg-muted py-10 md:py-14">
        <Container>
            <TrustBadges />
        </Container>
    </section>
</template>
