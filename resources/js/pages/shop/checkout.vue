<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Check, ChevronRight, Lock, ShoppingBag } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import Container from '@/components/shop/container.vue';
import PageHead from '@/components/shop/page-head.vue';
import StripePaymentForm from '@/components/shop/stripe-payment-form.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import { Spinner } from '@/components/ui/spinner';
import { useShop } from '@/composables/useShop';
import { useTrans } from '@/composables/useTrans';
import { formatMoney } from '@/lib/format';
import { home } from '@/routes';
import * as shop from '@/routes/shop';
import * as checkout from '@/routes/shop/checkout';
import type { Address, Cart, CartContext, DeliveryOption } from '@/types/shop';

type ShippingAddressForm = {
    first_name: string;
    last_name: string;
    street_address: string;
    street_address_plus: string;
    postal_code: string;
    city: string;
    state: string;
    phone_number: string;
};

const props = defineProps<{
    cart: Cart;
    cartContext: CartContext;
    savedAddresses: Address[];
    shippingAddress: ShippingAddressForm | null;
    deliveryOptions: DeliveryOption[];
    selectedDeliveryOption: string | number | null;
    paymentOptions: Array<{
        id: number;
        title: string;
        driver: string;
        logo?: string | null;
    }>;
    selectedPaymentMethod: number | null;
    step: 1 | 2 | 3;
    stripeData: {
        client_secret: string;
        publishable_key: string;
        return_url: string;
    } | null;
}>();

const { currency, taxLabel, zone } = useShop();
const { t } = useTrans();

const step = computed<1 | 2 | 3>(() => props.step);

const maxStep = computed<1 | 2 | 3>(() => {
    if (props.selectedDeliveryOption !== null) {
        return 3;
    }

    if (props.shippingAddress) {
        return 2;
    }

    return 1;
});

const selectedAddressId = ref<number | null>(null);

const addressForm = useForm<ShippingAddressForm>({
    first_name: props.shippingAddress?.first_name ?? '',
    last_name: props.shippingAddress?.last_name ?? '',
    street_address: props.shippingAddress?.street_address ?? '',
    street_address_plus: props.shippingAddress?.street_address_plus ?? '',
    postal_code: props.shippingAddress?.postal_code ?? '',
    city: props.shippingAddress?.city ?? '',
    state: props.shippingAddress?.state ?? '',
    phone_number: props.shippingAddress?.phone_number ?? '',
});

const shippingForm = useForm<{ service_code: string }>({
    service_code: String(props.selectedDeliveryOption ?? ''),
});

const paymentForm = useForm<{ payment_method_id: number | null }>({
    payment_method_id: props.selectedPaymentMethod ?? null,
});

const selectedDelivery = computed<DeliveryOption | null>(
    () =>
        props.deliveryOptions.find(
            (o) => o.service_code === props.selectedDeliveryOption,
        ) ?? null,
);

const currentPaymentMethod = computed(
    () =>
        props.paymentOptions.find(
            (m) => m.id === paymentForm.payment_method_id,
        ) ?? null,
);

const isStripeSelected = computed<boolean>(
    () => currentPaymentMethod.value?.driver === 'stripe',
);
const preparingStripe = ref<boolean>(false);
const stripeMounted = ref<boolean>(false);

watch(
    () => isStripeSelected.value && Boolean(props.stripeData),
    (active) => {
        if (active) {
            stripeMounted.value = true;
        }
    },
    { immediate: true },
);

watch(
    () => paymentForm.payment_method_id,
    (id) => {
        if (!id) {
            return;
        }

        const method = props.paymentOptions.find((m) => m.id === id) ?? null;

        if (!method) {
            return;
        }

        if (method.driver === 'stripe' && !props.stripeData) {
            preparingStripe.value = true;
            router.post(
                checkout.preparePayment.url(),
                { payment_method_id: id },
                {
                    preserveScroll: true,
                    onFinish: () => (preparingStripe.value = false),
                },
            );
        }
    },
);

const total = computed<number>(() => {
    const sub = props.cartContext?.total ?? 0;
    const delivery = selectedDelivery.value?.amount ?? 0;

    return sub + delivery;
});

const crumbs = computed(() => [
    { label: t('shop.nav.home'), href: home.url() },
    { label: t('shop.cart.heading'), href: shop.cart.url() },
    { label: t('shop.checkout.heading') },
]);

const steps = computed(() => [
    { n: 1 as const, label: t('shop.checkout.step.shipping') },
    { n: 2 as const, label: t('shop.checkout.step.delivery') },
    { n: 3 as const, label: t('shop.checkout.step.payment') },
]);

function selectAddress(address: Address): void {
    selectedAddressId.value = address.id;
    addressForm.first_name = address.first_name ?? '';
    addressForm.last_name = address.last_name ?? '';
    addressForm.street_address = address.street_address ?? '';
    addressForm.street_address_plus = address.street_address_plus ?? '';
    addressForm.postal_code = address.postal_code ?? '';
    addressForm.city = address.city ?? '';
    addressForm.state = address.state ?? '';
    addressForm.phone_number = address.phone_number ?? '';
}

function clearAddress(): void {
    selectedAddressId.value = null;
    addressForm.reset();
}

function goToStep(target: 1 | 2 | 3): void {
    if (target === step.value) {
        return;
    }

    if (target > maxStep.value) {
        return;
    }

    router.get(
        checkout.index.url(),
        { step: target },
        { preserveScroll: true, preserveState: false },
    );
}

function submitAddress(): void {
    addressForm.post(checkout.shippingAddress.url(), { preserveScroll: true });
}

function submitShipping(): void {
    shippingForm.post(checkout.shippingOption.url(), { preserveScroll: true });
}

function placeOrder(): void {
    paymentForm.post(checkout.placeOrder.url(), { preserveScroll: true });
}

function lineImage(line: Cart['lines'][number]): string | null {
    return (
        line.purchasable.thumbnail ?? line.purchasable.images?.[0]?.url ?? null
    );
}

function lineName(line: Cart['lines'][number]): string {
    return (line.purchasable as { name?: string })?.name ?? '';
}
</script>

<template>
    <Head :title="t('shop.checkout.title')" />

    <PageHead :title="t('shop.checkout.heading')" :crumbs="crumbs" />

    <Container class="py-10 md:py-14">
        <nav class="mb-10" :aria-label="t('shop.checkout.heading')">
            <ol class="flex flex-wrap items-center gap-2">
                <li
                    v-for="(s, i) in steps"
                    :key="s.n"
                    class="flex items-center gap-2"
                >
                    <button
                        type="button"
                        :disabled="s.n > maxStep"
                        :aria-current="step === s.n ? 'step' : undefined"
                        :class="[
                            'flex items-center gap-2 text-sm font-semibold transition',
                            step === s.n
                                ? 'text-ink'
                                : maxStep > s.n
                                  ? 'text-emerald'
                                  : 'text-ink-faint',
                        ]"
                        @click="goToStep(s.n as 1 | 2 | 3)"
                    >
                        <span
                            :class="[
                                'grid size-7 place-items-center rounded-full font-mono text-xs font-bold',
                                step === s.n
                                    ? 'bg-primary text-paper'
                                    : step > s.n
                                      ? 'bg-emerald/12 text-emerald'
                                      : 'bg-muted text-ink-faint',
                            ]"
                        >
                            <Check
                                v-if="step > s.n"
                                class="size-4"
                                aria-hidden="true"
                            />
                            <template v-else>{{ s.n }}</template>
                        </span>
                        {{ s.label }}
                    </button>
                    <ChevronRight
                        v-if="i < steps.length - 1"
                        class="size-4 text-rule-strong"
                        aria-hidden="true"
                    />
                </li>
            </ol>
        </nav>

        <div class="grid gap-10 lg:grid-cols-[1.6fr_1fr]">
            <div>
                <template v-if="step === 1">
                    <div v-if="savedAddresses.length" class="mb-8">
                        <h2 class="text-lg">
                            {{ t('shop.checkout.saved_addresses') }}
                        </h2>
                        <div class="mt-4 grid gap-3 sm:grid-cols-2">
                            <button
                                v-for="address in savedAddresses"
                                :key="address.id"
                                type="button"
                                :class="[
                                    'rounded-lg border bg-paper p-5 text-left transition',
                                    selectedAddressId === address.id
                                        ? 'border-brand ring-1 ring-brand'
                                        : 'border-rule hover:border-brand-line',
                                ]"
                                @click="selectAddress(address)"
                            >
                                <p
                                    class="font-heading text-sm font-bold text-ink"
                                >
                                    {{ address.first_name }}
                                    {{ address.last_name }}
                                </p>
                                <p class="mt-1 text-xs text-ink-mute">
                                    {{ address.street_address }},
                                    {{ address.city }}
                                    {{ address.postal_code }}
                                </p>
                                <p class="text-xs text-ink-mute">
                                    {{ address.country?.name }}
                                </p>
                                <span
                                    v-if="address.shipping_default"
                                    class="mt-2 inline-flex items-center rounded-full bg-brand-soft px-2.5 py-0.5 font-mono text-[11px] font-semibold text-brand-deep"
                                >
                                    {{ t('shop.checkout.default') }}
                                </span>
                            </button>
                        </div>

                        <button
                            v-if="selectedAddressId"
                            type="button"
                            class="mt-3 text-sm text-ink-mute underline underline-offset-4 transition hover:text-brand"
                            @click="clearAddress"
                        >
                            {{ t('shop.checkout.use_new_address') }}
                        </button>

                        <hr class="my-6 border-rule" />
                    </div>

                    <form class="space-y-5" @submit.prevent="submitAddress">
                        <h2 class="text-lg">
                            {{ t('shop.checkout.shipping_address') }}
                        </h2>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="grid gap-2">
                                <Label for="first_name">
                                    {{ t('shop.checkout.first_name') }}
                                </Label>
                                <Input
                                    id="first_name"
                                    v-model="addressForm.first_name"
                                />
                                <p
                                    v-if="addressForm.errors.first_name"
                                    class="text-xs text-destructive"
                                >
                                    {{ addressForm.errors.first_name }}
                                </p>
                            </div>
                            <div class="grid gap-2">
                                <Label for="last_name">
                                    {{ t('shop.checkout.last_name') }}
                                </Label>
                                <Input
                                    id="last_name"
                                    v-model="addressForm.last_name"
                                />
                                <p
                                    v-if="addressForm.errors.last_name"
                                    class="text-xs text-destructive"
                                >
                                    {{ addressForm.errors.last_name }}
                                </p>
                            </div>
                        </div>

                        <div class="grid gap-2">
                            <Label for="street_address">
                                {{ t('shop.checkout.address') }}
                            </Label>
                            <Input
                                id="street_address"
                                v-model="addressForm.street_address"
                            />
                            <p
                                v-if="addressForm.errors.street_address"
                                class="text-xs text-destructive"
                            >
                                {{ addressForm.errors.street_address }}
                            </p>
                        </div>

                        <div class="grid gap-2">
                            <Label for="street_address_plus">
                                {{ t('shop.checkout.address_line_2') }}
                            </Label>
                            <Input
                                id="street_address_plus"
                                v-model="addressForm.street_address_plus"
                            />
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="grid gap-2">
                                <Label for="city">
                                    {{ t('shop.checkout.city') }}
                                </Label>
                                <Input id="city" v-model="addressForm.city" />
                                <p
                                    v-if="addressForm.errors.city"
                                    class="text-xs text-destructive"
                                >
                                    {{ addressForm.errors.city }}
                                </p>
                            </div>
                            <div class="grid gap-2">
                                <Label for="postal_code">
                                    {{ t('shop.checkout.postal_code') }}
                                </Label>
                                <Input
                                    id="postal_code"
                                    v-model="addressForm.postal_code"
                                />
                                <p
                                    v-if="addressForm.errors.postal_code"
                                    class="text-xs text-destructive"
                                >
                                    {{ addressForm.errors.postal_code }}
                                </p>
                            </div>
                            <div class="grid gap-2">
                                <Label for="state">
                                    {{ t('shop.checkout.state') }}
                                </Label>
                                <Input id="state" v-model="addressForm.state" />
                                <p
                                    v-if="addressForm.errors.state"
                                    class="text-xs text-destructive"
                                >
                                    {{ addressForm.errors.state }}
                                </p>
                            </div>
                            <div class="grid gap-2">
                                <Label for="country">
                                    {{ t('shop.checkout.country') }}
                                </Label>
                                <Input
                                    id="country"
                                    :value="zone?.country_name ?? ''"
                                    readonly
                                />
                            </div>
                        </div>

                        <div class="grid gap-2">
                            <Label for="phone_number">
                                {{ t('shop.checkout.phone') }}
                            </Label>
                            <Input
                                id="phone_number"
                                v-model="addressForm.phone_number"
                                type="tel"
                            />
                        </div>

                        <Button
                            type="submit"
                            :disabled="addressForm.processing"
                        >
                            {{ t('shop.checkout.continue_delivery') }}
                        </Button>
                    </form>
                </template>

                <template v-else-if="step === 2">
                    <div v-if="!deliveryOptions.length">
                        <div
                            class="flex items-center gap-4 rounded-lg border border-rule bg-paper p-5"
                        >
                            <ShoppingBag
                                class="size-5 text-ink-faint"
                                aria-hidden="true"
                            />
                            <p class="text-sm text-ink-soft">
                                {{ t('shop.checkout.no_delivery') }}
                            </p>
                        </div>
                        <button
                            type="button"
                            class="mt-4 text-sm text-ink-mute transition hover:text-brand"
                            @click="goToStep(1)"
                        >
                            {{ t('shop.checkout.return_shipping') }}
                        </button>
                    </div>

                    <form
                        v-else
                        class="space-y-5"
                        @submit.prevent="submitShipping"
                    >
                        <h2 class="text-lg">
                            {{ t('shop.checkout.delivery_method') }}
                        </h2>
                        <p
                            v-if="shippingForm.errors.service_code"
                            class="text-xs text-destructive"
                        >
                            {{ shippingForm.errors.service_code }}
                        </p>

                        <RadioGroup
                            v-model="shippingForm.service_code"
                            class="gap-3"
                        >
                            <label
                                v-for="option in deliveryOptions"
                                :key="option.service_code"
                                :class="[
                                    'flex cursor-pointer items-center justify-between gap-4 rounded-lg border bg-paper p-5 transition',
                                    shippingForm.service_code ===
                                    String(option.service_code)
                                        ? 'border-brand ring-1 ring-brand'
                                        : 'border-rule hover:border-brand-line',
                                ]"
                            >
                                <div class="flex items-start gap-3">
                                    <RadioGroupItem
                                        :value="String(option.service_code)"
                                        class="mt-1"
                                    />
                                    <img
                                        v-if="option.carrier_logo"
                                        :src="option.carrier_logo"
                                        :alt="option.carrier_name ?? ''"
                                        class="mt-0.5 size-6 rounded-full object-cover"
                                    />
                                    <div class="flex flex-col">
                                        <span
                                            class="font-heading text-sm font-semibold text-ink"
                                        >
                                            {{ option.service_name }}
                                        </span>
                                        <span
                                            v-if="option.estimated_days"
                                            class="text-sm text-ink-mute"
                                        >
                                            {{
                                                t(
                                                    'shop.checkout.days_delivery',
                                                    {
                                                        days: option.estimated_days,
                                                    },
                                                )
                                            }}
                                        </span>
                                        <span
                                            v-else-if="option.description"
                                            class="text-sm text-ink-mute"
                                        >
                                            {{ option.description }}
                                        </span>
                                    </div>
                                </div>
                                <span
                                    class="font-heading text-sm font-bold text-ink"
                                >
                                    {{
                                        formatMoney(
                                            option.amount,
                                            option.currency,
                                        )
                                    }}
                                </span>
                            </label>
                        </RadioGroup>

                        <Button
                            type="submit"
                            :disabled="
                                !shippingForm.service_code ||
                                shippingForm.processing
                            "
                        >
                            {{ t('shop.checkout.continue_payment') }}
                        </Button>
                    </form>
                </template>

                <template v-else>
                    <div class="space-y-5">
                        <div>
                            <h2 class="text-lg">
                                {{ t('shop.checkout.payment_method') }}
                            </h2>
                            <p class="mt-1 text-sm text-ink-mute">
                                {{ t('shop.checkout.secure_transactions') }}
                            </p>
                        </div>

                        <p
                            v-if="paymentForm.errors.payment_method_id"
                            class="text-xs text-destructive"
                        >
                            {{ paymentForm.errors.payment_method_id }}
                        </p>

                        <p
                            v-if="!paymentOptions.length"
                            class="text-sm text-ink-soft"
                        >
                            {{ t('shop.checkout.no_payment_methods') }}
                        </p>

                        <template v-else>
                            <div class="flex flex-col gap-1">
                                <label
                                    v-for="method in paymentOptions"
                                    :key="method.id"
                                    :class="[
                                        'flex cursor-pointer items-center justify-between gap-6 rounded-md px-3 py-3 transition',
                                        paymentForm.payment_method_id ===
                                        method.id
                                            ? 'bg-brand-soft'
                                            : 'hover:bg-muted',
                                    ]"
                                >
                                    <div class="flex items-center gap-3">
                                        <span
                                            :class="[
                                                'grid size-4 place-items-center rounded-full border-2 transition',
                                                paymentForm.payment_method_id ===
                                                method.id
                                                    ? 'border-brand'
                                                    : 'border-rule-strong',
                                            ]"
                                        >
                                            <span
                                                v-if="
                                                    paymentForm.payment_method_id ===
                                                    method.id
                                                "
                                                class="size-2 rounded-full bg-primary"
                                            />
                                        </span>
                                        <input
                                            v-model="
                                                paymentForm.payment_method_id
                                            "
                                            type="radio"
                                            :value="method.id"
                                            name="payment_method_id"
                                            class="sr-only"
                                        />
                                        <span
                                            class="font-heading text-sm font-semibold text-ink"
                                        >
                                            {{ method.title }}
                                        </span>
                                    </div>
                                    <img
                                        v-if="method.logo"
                                        :src="method.logo"
                                        :alt="method.title"
                                        class="h-5 w-auto object-contain"
                                    />
                                </label>
                            </div>

                            <div
                                v-show="isStripeSelected && stripeData"
                                class="space-y-4 pt-2"
                            >
                                <div class="flex items-center gap-3">
                                    <h3
                                        class="font-mono text-xs tracking-[0.08em] text-ink-mute uppercase"
                                    >
                                        {{ t('shop.checkout.card_details') }}
                                    </h3>
                                    <span class="h-px flex-1 bg-rule" />
                                </div>

                                <StripePaymentForm
                                    v-if="stripeMounted && stripeData"
                                    :key="stripeData.client_secret"
                                    :client-secret="stripeData.client_secret"
                                    :publishable-key="
                                        stripeData.publishable_key
                                    "
                                    :return-url="stripeData.return_url"
                                    :total="total"
                                />
                            </div>

                            <div
                                v-if="currentPaymentMethod && !isStripeSelected"
                                class="flex flex-col gap-3 border-t border-rule pt-5"
                            >
                                <div
                                    class="flex flex-wrap items-center justify-between gap-4"
                                >
                                    <div class="flex flex-col">
                                        <span
                                            class="font-mono text-xs text-ink-mute"
                                        >
                                            {{ t('shop.checkout.total') }}
                                            {{ taxLabel }}
                                        </span>
                                        <span
                                            class="font-heading text-xl font-extrabold text-ink"
                                        >
                                            {{ formatMoney(total, currency) }}
                                        </span>
                                    </div>
                                    <Button
                                        type="button"
                                        size="lg"
                                        :disabled="paymentForm.processing"
                                        @click="placeOrder"
                                    >
                                        {{
                                            paymentForm.processing
                                                ? t('shop.checkout.processing')
                                                : t('shop.checkout.place_order')
                                        }}
                                    </Button>
                                </div>
                                <p
                                    class="inline-flex items-center gap-1.5 font-mono text-xs text-ink-mute"
                                >
                                    <Lock class="size-3" aria-hidden="true" />
                                    {{ t('shop.checkout.secure_encrypted') }}
                                </p>
                            </div>

                            <div
                                v-if="
                                    isStripeSelected &&
                                    !stripeData &&
                                    preparingStripe
                                "
                                class="flex items-center gap-2 pt-3 text-sm text-ink-mute"
                            >
                                <Spinner class="size-4" />
                                {{ t('shop.checkout.preparing_payment') }}
                            </div>
                        </template>
                    </div>
                </template>
            </div>

            <div
                class="self-start lg:sticky"
                :style="{ top: 'calc(var(--header-h) + var(--nav-h) + 1rem)' }"
            >
                <div class="rounded-lg border border-rule bg-paper p-6">
                    <h2 class="text-lg">
                        {{ t('shop.checkout.order_summary') }}
                    </h2>

                    <ul
                        v-if="cart"
                        role="list"
                        class="mt-4 divide-y divide-rule"
                    >
                        <li
                            v-for="line in cart.lines"
                            :key="line.id"
                            class="flex gap-3 py-3"
                        >
                            <div
                                class="size-14 shrink-0 overflow-hidden rounded-sm bg-muted"
                            >
                                <img
                                    v-if="lineImage(line)"
                                    :src="lineImage(line) as string"
                                    :alt="lineName(line)"
                                    class="size-full object-cover"
                                />
                            </div>
                            <div class="flex flex-1 justify-between gap-3">
                                <div class="min-w-0">
                                    <p
                                        class="font-heading text-sm font-semibold text-ink"
                                    >
                                        {{ lineName(line) }}
                                    </p>
                                    <p class="font-mono text-xs text-ink-mute">
                                        {{
                                            t('shop.checkout.qty', {
                                                quantity: line.quantity,
                                            })
                                        }}
                                    </p>
                                </div>
                                <p
                                    class="font-heading text-sm font-bold text-ink"
                                >
                                    {{
                                        formatMoney(
                                            line.unit_price_amount *
                                                line.quantity,
                                            currency,
                                        )
                                    }}
                                </p>
                            </div>
                        </li>
                    </ul>

                    <dl
                        class="mt-4 space-y-3 border-t border-rule pt-4 text-sm text-ink-mute"
                    >
                        <div
                            class="flex items-center justify-between border-b border-rule pb-3"
                        >
                            <dt>{{ t('shop.checkout.tax') }}</dt>
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
                            <dt>{{ t('shop.checkout.delivery') }}</dt>
                            <dd class="font-heading font-semibold text-ink">
                                <template v-if="selectedDelivery">
                                    {{
                                        selectedDelivery.amount > 0
                                            ? formatMoney(
                                                  selectedDelivery.amount,
                                                  selectedDelivery.currency,
                                              )
                                            : t('shop.checkout.free')
                                    }}
                                </template>
                                <template v-else>
                                    {{ t('shop.checkout.delivery_calculated') }}
                                </template>
                            </dd>
                        </div>

                        <div
                            v-if="cartContext && cartContext.discountTotal > 0"
                            class="flex items-center justify-between border-b border-rule pb-3"
                        >
                            <dt>{{ t('shop.checkout.discount') }}</dt>
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
                                {{ t('shop.checkout.total') }} {{ taxLabel }}
                            </dt>
                            <dd
                                class="font-heading text-xl font-extrabold text-ink"
                            >
                                {{ formatMoney(total, currency) }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </Container>
</template>
