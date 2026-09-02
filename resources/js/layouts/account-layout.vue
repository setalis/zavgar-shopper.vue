<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import Container from '@/components/shop/container.vue';
import { useTrans } from '@/composables/useTrans';
import { dashboard, logout } from '@/routes';
import {
    addresses as accountAddresses,
    orders as accountOrders,
} from '@/routes/account';
import * as profile from '@/routes/profile';
import * as shop from '@/routes/shop';

type NavItem = { href: string; label: string; exact?: boolean };

const page = usePage();
const { t } = useTrans();

const items = computed<NavItem[]>(() => [
    { href: dashboard.url(), label: t('account.nav.overview'), exact: true },
    { href: accountOrders.url(), label: t('account.nav.orders') },
    { href: shop.wishlist.url(), label: t('account.nav.wishlist') },
    { href: accountAddresses.url(), label: t('account.nav.addresses') },
    { href: profile.edit.url(), label: t('account.nav.profile') },
]);

function isActive(item: NavItem): boolean {
    const path = (page.url ?? '').split('?')[0];

    return item.exact ? path === item.href : path.startsWith(item.href);
}
</script>

<template>
    <Container class="py-10 sm:py-16 lg:py-24">
        <div class="grid grid-cols-1 lg:grid-cols-5 lg:gap-x-12">
            <div class="lg:col-span-1">
                <h2
                    class="hidden font-heading text-xl leading-6 font-medium text-ink lg:block"
                >
                    {{ t('account.nav.title') }}
                </h2>

                <nav
                    role="navigation"
                    class="-mx-4 flex gap-2 overflow-x-auto px-4 pb-4 sm:-mx-6 sm:px-6 lg:hidden"
                >
                    <Link
                        v-for="item in items"
                        :key="item.href"
                        :href="item.href"
                        :class="[
                            'inline-block text-sm hover:underline hover:decoration-2',
                            isActive(item)
                                ? 'font-medium text-ink'
                                : 'text-ink-mute hover:text-ink',
                        ]"
                    >
                        {{ item.label }}
                    </Link>
                </nav>

                <nav
                    role="navigation"
                    class="mt-10 hidden flex-col space-y-4 lg:flex"
                >
                    <Link
                        v-for="item in items"
                        :key="item.href"
                        :href="item.href"
                        :class="[
                            'inline-block text-sm hover:underline hover:decoration-2',
                            isActive(item)
                                ? 'font-medium text-ink'
                                : 'text-ink-mute hover:text-ink',
                        ]"
                    >
                        {{ item.label }}
                    </Link>

                    <form method="POST" :action="logout.url()">
                        <button
                            type="submit"
                            class="text-sm text-destructive transition hover:opacity-80"
                        >
                            {{ t('account.nav.log_out') }}
                        </button>
                    </form>
                </nav>
            </div>

            <div class="lg:col-span-4">
                <slot />
            </div>
        </div>
    </Container>
</template>
