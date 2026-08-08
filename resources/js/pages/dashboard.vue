<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { MapPin, ShoppingBag, User } from 'lucide-vue-next';
import Card from '@/components/shop/card.vue';
import { useTrans } from '@/composables/useTrans';
import {
    addresses as accountAddresses,
    orders as accountOrders,
} from '@/routes/account';
import * as profile from '@/routes/profile';

const page = usePage();
const { t } = useTrans();

const firstName =
    (page.props.auth.user as { first_name?: string; name?: string } | null)
        ?.first_name ??
    (page.props.auth.user as { name?: string } | null)?.name ??
    '';
</script>

<template>
    <Head :title="t('dashboard.title')" />

    <h1 class="font-heading text-2xl font-bold text-zinc-900 dark:text-white">
        <template v-if="firstName">
            {{ t('dashboard.welcome_name', { name: firstName }) }}
        </template>
        <template v-else>
            {{ t('dashboard.welcome') }}
        </template>
    </h1>

    <div class="mt-8 grid gap-4 sm:grid-cols-3">
        <Link :href="accountOrders.url()" class="block">
            <Card class="flex items-center gap-3">
                <div
                    class="flex size-10 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800"
                >
                    <ShoppingBag
                        class="size-5 text-zinc-600 dark:text-zinc-400"
                        aria-hidden="true"
                    />
                </div>
                <div>
                    <p
                        class="text-sm font-medium text-zinc-900 dark:text-white"
                    >
                        {{ t('dashboard.orders.title') }}
                    </p>
                    <p class="text-xs text-zinc-500">
                        {{ t('dashboard.orders.description') }}
                    </p>
                </div>
            </Card>
        </Link>

        <Link :href="accountAddresses.url()" class="block">
            <Card class="flex items-center gap-3">
                <div
                    class="flex size-10 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800"
                >
                    <MapPin
                        class="size-5 text-zinc-600 dark:text-zinc-400"
                        aria-hidden="true"
                    />
                </div>
                <div>
                    <p
                        class="text-sm font-medium text-zinc-900 dark:text-white"
                    >
                        {{ t('dashboard.addresses.title') }}
                    </p>
                    <p class="text-xs text-zinc-500">
                        {{ t('dashboard.addresses.description') }}
                    </p>
                </div>
            </Card>
        </Link>

        <Link :href="profile.edit.url()" class="block">
            <Card class="flex items-center gap-3">
                <div
                    class="flex size-10 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800"
                >
                    <User
                        class="size-5 text-zinc-600 dark:text-zinc-400"
                        aria-hidden="true"
                    />
                </div>
                <div>
                    <p
                        class="text-sm font-medium text-zinc-900 dark:text-white"
                    >
                        {{ t('dashboard.profile.title') }}
                    </p>
                    <p class="text-xs text-zinc-500">
                        {{ t('dashboard.profile.description') }}
                    </p>
                </div>
            </Card>
        </Link>
    </div>
</template>
