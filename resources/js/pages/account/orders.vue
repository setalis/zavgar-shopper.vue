<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ShoppingBag } from 'lucide-vue-next';
import { computed } from 'vue';
import OrderStatusBadge from '@/components/account/order-status-badge.vue';
import ProductPagination from '@/components/shop/product-pagination.vue';
import { Button } from '@/components/ui/button';
import { useTrans } from '@/composables/useTrans';
import { formatMoney } from '@/lib/format';
import { orders as accountOrders } from '@/routes/account';
import { show as ordersShow } from '@/routes/account/orders';
import * as shop from '@/routes/shop';

type OrderItem = {
    id: number;
    name: string;
    sku: string | null;
    quantity: number;
    unit_price_amount: number;
    product?: {
        slug?: string;
        thumbnail?: string | null;
        images?: Array<{ url: string }>;
    } | null;
};

type Address = {
    street_address?: string | null;
    street_address_plus?: string | null;
    city?: string | null;
    postal_code?: string | null;
    country_name?: string | null;
};

type Order = {
    id: number;
    number: string;
    created_at: string;
    updated_at: string;
    price_amount: number;
    currency_code: string;
    status: string;
    payment_status: string;
    shipping_status: string;
    shipping_address: Address | null;
    items: OrderItem[];
};

type Paginated<T> = {
    data: T[];
    total: number;
    current_page: number;
    last_page: number;
    links: Array<{ url: string | null; label: string; active: boolean }>;
};

const props = defineProps<{
    orders: Paginated<Order>;
    filters: { tab: string };
}>();

const { t } = useTrans();

const tabs = computed(() => [
    { value: 'all', label: t('account.orders.tabs.all') },
    { value: 'not-shipped', label: t('account.orders.tabs.not_shipped') },
    { value: 'cancelled', label: t('account.orders.tabs.cancelled') },
]);

const activeTab = computed<string>(() => props.filters.tab || 'all');

function changeTab(value: string): void {
    router.get(
        accountOrders.url(),
        { tab: value },
        { preserveScroll: true, preserveState: true, replace: true },
    );
}

function formatDate(value: string, format: 'short' | 'long' = 'long'): string {
    const date = new Date(value);

    return format === 'short'
        ? date.toLocaleDateString('en-US', { month: 'short', day: '2-digit' })
        : date.toLocaleDateString('en-US', {
              month: 'short',
              day: '2-digit',
              year: 'numeric',
          });
}

function itemThumbnail(item: OrderItem): string | null {
    return item.product?.thumbnail ?? item.product?.images?.[0]?.url ?? null;
}

function shippingLabel(order: Order): string {
    const date = formatDate(order.updated_at, 'short');

    if (order.shipping_status === 'delivered') {
        return t('account.orders.shipping.delivered', { date });
    }

    if (
        order.shipping_status === 'shipped' ||
        order.shipping_status === 'partially_shipped'
    ) {
        return t('account.orders.shipping.shipped', { date });
    }

    if (
        order.shipping_status === 'returned' ||
        order.shipping_status === 'partially_returned'
    ) {
        return t('account.orders.shipping.returned');
    }

    if (order.status === 'cancelled') {
        return t('account.orders.shipping.cancelled');
    }

    if (order.status === 'completed') {
        return t('account.orders.shipping.completed', { date });
    }

    return t('account.orders.shipping.processing');
}
</script>

<template>
    <Head :title="t('account.orders.title')" />

    <div class="flex items-center gap-3">
        <h1 class="font-heading text-2xl font-bold text-ink">
            {{ t('account.orders.heading') }}
        </h1>
        <span
            v-if="orders.total > 0"
            class="inline-flex items-center justify-center rounded-full bg-muted px-2.5 py-0.5 text-xs font-medium text-ink-mute"
        >
            {{ orders.total }}
        </span>
    </div>

    <div class="mt-6 flex items-center justify-between">
        <div class="flex items-center gap-1 rounded-lg border border-rule p-1">
            <button
                v-for="tab in tabs"
                :key="tab.value"
                type="button"
                :class="[
                    'rounded-md px-3 py-1.5 text-sm font-medium transition',
                    activeTab === tab.value
                        ? 'bg-primary text-paper'
                        : 'text-ink-mute hover:text-ink',
                ]"
                @click="changeTab(tab.value)"
            >
                {{ tab.label }}
            </button>
        </div>
    </div>

    <div
        v-if="!orders.data.length"
        class="mt-12 flex flex-col items-center justify-center text-center"
    >
        <ShoppingBag class="size-12 text-ink-faint" aria-hidden="true" />
        <h3 class="mt-4 text-sm font-medium text-ink">
            {{ t('account.orders.empty.title') }}
        </h3>
        <p class="mt-1 text-sm text-ink-mute">
            {{ t('account.orders.empty.description') }}
        </p>
        <Link :href="shop.index.url()" class="mt-6">
            <Button>{{ t('account.orders.empty.cta') }}</Button>
        </Link>
    </div>

    <template v-else>
        <div class="mt-6 space-y-6">
            <div
                v-for="order in orders.data"
                :key="order.id"
                class="overflow-hidden rounded-xl border border-rule"
            >
                <div
                    class="flex flex-wrap items-start justify-between gap-4 border-b border-rule bg-muted px-5 py-4"
                >
                    <div class="flex flex-wrap items-center gap-8 text-sm">
                        <div>
                            <dt class="text-xs text-ink-mute">
                                {{ t('account.orders.order_placed') }}
                            </dt>
                            <dd class="mt-0.5 font-medium text-ink">
                                {{ formatDate(order.created_at) }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs text-ink-mute">
                                {{ t('account.orders.total') }}
                            </dt>
                            <dd class="mt-0.5 font-medium text-ink">
                                {{
                                    formatMoney(
                                        order.price_amount,
                                        order.currency_code,
                                    )
                                }}
                            </dd>
                        </div>
                        <div v-if="order.shipping_address" class="max-w-xs">
                            <dt class="text-xs text-ink-mute">
                                {{ t('account.orders.ship_to') }}
                            </dt>
                            <dd class="mt-0.5 font-medium text-ink">
                                <span>{{
                                    order.shipping_address.street_address
                                }}</span>
                                <span class="text-xs font-normal text-ink-soft">
                                    <span
                                        v-if="
                                            order.shipping_address.postal_code
                                        "
                                        >{{
                                            order.shipping_address.postal_code
                                        }}
                                    </span>
                                    <span v-if="order.shipping_address.city">{{
                                        order.shipping_address.city
                                    }}</span>
                                    <span
                                        v-if="
                                            order.shipping_address.country_name
                                        "
                                        >,
                                        {{
                                            order.shipping_address.country_name
                                        }}</span
                                    >
                                </span>
                            </dd>
                        </div>
                    </div>
                    <div class="flex flex-col items-end gap-1.5 text-sm">
                        <span class="font-medium text-ink">{{
                            t('account.orders.order_number', {
                                number: order.number,
                            })
                        }}</span>
                        <Link
                            :href="ordersShow.url(order.id)"
                            class="text-sm font-medium text-ink hover:underline"
                        >
                            {{ t('account.orders.view_details') }}
                        </Link>
                    </div>
                </div>

                <div class="px-5 py-4">
                    <div class="flex flex-wrap items-center gap-3">
                        <h3
                            class="font-heading text-base font-semibold text-ink"
                        >
                            {{ shippingLabel(order) }}
                        </h3>
                        <template v-if="order.status === 'cancelled'">
                            <OrderStatusBadge
                                :status="order.status"
                                type="order"
                            />
                        </template>
                        <template v-else>
                            <OrderStatusBadge
                                :status="order.payment_status"
                                type="payment"
                            />
                            <OrderStatusBadge
                                :status="order.shipping_status"
                                type="shipping"
                            />
                        </template>
                    </div>

                    <div class="mt-4 space-y-4">
                        <div
                            v-for="item in order.items"
                            :key="item.id"
                            class="flex gap-4"
                        >
                            <div
                                class="size-24 shrink-0 overflow-hidden rounded-lg bg-muted ring-1 ring-rule"
                            >
                                <img
                                    v-if="itemThumbnail(item)"
                                    :src="itemThumbnail(item)!"
                                    :alt="item.name"
                                    loading="lazy"
                                    class="size-full object-cover object-center"
                                />
                            </div>
                            <div class="min-w-0 flex-1">
                                <Link
                                    v-if="item.product?.slug"
                                    :href="
                                        shop.product.url({
                                            product: item.product.slug,
                                        })
                                    "
                                    class="line-clamp-2 font-heading text-sm font-medium text-ink hover:underline"
                                >
                                    {{ item.name }}
                                </Link>
                                <p
                                    v-else
                                    class="line-clamp-2 font-heading text-sm font-medium text-ink"
                                >
                                    {{ item.name }}
                                </p>
                                <p
                                    v-if="item.sku"
                                    class="mt-0.5 text-xs text-ink-mute"
                                >
                                    {{
                                        t('account.orders.sku', {
                                            sku: item.sku,
                                        })
                                    }}
                                </p>
                                <p class="mt-1 text-sm text-ink-mute">
                                    {{
                                        t('account.orders.quantity_price', {
                                            quantity: item.quantity,
                                            price: formatMoney(
                                                item.unit_price_amount,
                                                order.currency_code,
                                            ),
                                        })
                                    }}
                                </p>
                            </div>
                            <p class="shrink-0 text-sm font-medium text-ink">
                                {{
                                    formatMoney(
                                        item.unit_price_amount * item.quantity,
                                        order.currency_code,
                                    )
                                }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <ProductPagination
            :links="orders.links"
            :label="t('account.orders.pagination')"
        />
    </template>
</template>
