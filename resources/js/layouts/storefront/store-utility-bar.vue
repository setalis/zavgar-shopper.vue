<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import Container from '@/components/shop/container.vue';
import LocaleSwitcher from '@/components/shop/locale-switcher.vue';
import { useTrans } from '@/composables/useTrans';
import { orders as accountOrders } from '@/routes/account';
import * as shop from '@/routes/shop';

const { t } = useTrans();
</script>

<template>
    <div class="bg-ink font-mono text-xs tracking-[0.04em] text-paper">
        <Container
            class="flex flex-wrap items-center justify-between gap-3 py-2.5"
        >
            <span class="inline-flex items-center gap-2">
                <span
                    class="rounded-full bg-rose px-2 py-[3px] text-[11px] font-medium tracking-[0.06em]"
                >
                    {{ t('shop.utility.tag') }}
                </span>
                {{ t('shop.utility.promo') }}
            </span>

            <span
                class="hidden items-center gap-5 text-paper/70 md:inline-flex"
            >
                <Link
                    :href="accountOrders.url()"
                    class="transition hover:text-paper"
                >
                    {{ t('shop.utility.track_order') }}
                </Link>
                <Link
                    :href="shop.contact.url()"
                    class="transition hover:text-paper"
                >
                    {{ t('shop.utility.help') }}
                </Link>
                <LocaleSwitcher />
            </span>
        </Container>
    </div>
</template>
