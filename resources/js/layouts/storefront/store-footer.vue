<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    Facebook,
    Instagram,
    Linkedin,
    Twitter,
    Youtube,
} from 'lucide-vue-next';
import { computed } from 'vue';
import BrandIcon from '@/components/shop/brand-icon.vue';
import BrandShopper from '@/components/shop/brand-shopper.vue';
import BrandVue from '@/components/shop/brand-vue.vue';
import Container from '@/components/shop/container.vue';
import ZoneSelector from '@/components/shop/zone-selector.vue';
import { useTrans } from '@/composables/useTrans';
import { dashboard, home, login, register } from '@/routes';
import {
    addresses as accountAddresses,
    orders as accountOrders,
} from '@/routes/account';
import * as shop from '@/routes/shop';
import type { NavCategory } from '@/types/shop';

const page = usePage();
const { t } = useTrans();

const currentYear = new Date().getFullYear();

const footerCategories = computed<NavCategory[]>(
    () => page.props.shop?.footer_categories ?? [],
);

const socials = [
    { icon: Twitter, label: 'Twitter' },
    { icon: Linkedin, label: 'LinkedIn' },
    { icon: Instagram, label: 'Instagram' },
    { icon: Youtube, label: 'YouTube' },
    { icon: Facebook, label: 'Facebook' },
];

const helpLinks = computed(() => [
    { href: shop.contact.url(), label: t('shop.footer.help.contact') },
    { href: accountOrders.url(), label: t('shop.footer.help.track_order') },
    { href: shop.contact.url(), label: t('shop.footer.help.returns') },
    { href: shop.contact.url(), label: t('shop.footer.help.shipping') },
    { href: shop.contact.url(), label: t('shop.footer.help.warranty') },
]);

const resourceLinks = computed(() => [
    { href: shop.index.url(), label: t('shop.footer.resources.all_products') },
    {
        href: shop.categories.url(),
        label: t('shop.footer.resources.categories'),
    },
    { href: shop.search.url(), label: t('shop.footer.resources.search') },
    { href: shop.cart.url(), label: t('shop.footer.resources.cart') },
]);
</script>

<template>
    <footer
        aria-labelledby="footer-heading"
        class="bg-ink pt-20 pb-5 text-paper"
    >
        <h2 id="footer-heading" class="sr-only">
            {{ t('shop.footer.sr_only') }}
        </h2>

        <Container>
            <div
                class="grid gap-10 border-b border-white/8 pb-14 sm:grid-cols-2 lg:grid-cols-[1.6fr_1fr_1fr_1fr_1fr]"
            >
                <div>
                    <Link
                        :href="home.url()"
                        class="mb-4 inline-flex items-center gap-2 font-heading text-2xl font-extrabold tracking-[-0.02em] text-paper"
                    >
                        <BrandIcon class="h-9 w-auto fill-current text-paper" />
                        {{ page.props.name }}
                    </Link>
                    <p
                        class="max-w-[32ch] text-sm leading-relaxed text-paper/70"
                    >
                        {{ t('shop.footer.tagline') }}
                    </p>

                    <div class="mt-4 inline-flex gap-2">
                        <a
                            v-for="social in socials"
                            :key="social.label"
                            href="#"
                            :aria-label="social.label"
                            class="grid size-9 place-items-center rounded-full bg-white/8 text-paper transition hover:bg-primary"
                        >
                            <component
                                :is="social.icon"
                                class="size-4"
                                aria-hidden="true"
                            />
                        </a>
                    </div>

                    <div class="mt-8">
                        <ZoneSelector />
                    </div>
                </div>

                <div v-if="footerCategories.length">
                    <h4 class="mb-4 font-heading text-sm font-bold text-paper">
                        {{ t('shop.footer.categories_heading') }}
                    </h4>
                    <ul class="grid gap-2.5">
                        <li
                            v-for="category in footerCategories"
                            :key="category.id"
                        >
                            <Link
                                :href="
                                    shop.category.url({
                                        category: category.slug,
                                    })
                                "
                                class="text-sm text-paper/70 transition hover:text-paper"
                            >
                                {{ category.name }}
                            </Link>
                        </li>
                    </ul>
                </div>

                <div>
                    <h4 class="mb-4 font-heading text-sm font-bold text-paper">
                        {{ t('shop.footer.help_heading') }}
                    </h4>
                    <ul class="grid gap-2.5">
                        <li v-for="link in helpLinks" :key="link.label">
                            <Link
                                :href="link.href"
                                class="text-sm text-paper/70 transition hover:text-paper"
                            >
                                {{ link.label }}
                            </Link>
                        </li>
                    </ul>
                </div>

                <div>
                    <h4 class="mb-4 font-heading text-sm font-bold text-paper">
                        {{ t('shop.footer.resources_heading') }}
                    </h4>
                    <ul class="grid gap-2.5">
                        <li v-for="link in resourceLinks" :key="link.label">
                            <Link
                                :href="link.href"
                                class="text-sm text-paper/70 transition hover:text-paper"
                            >
                                {{ link.label }}
                            </Link>
                        </li>
                    </ul>
                </div>

                <div>
                    <h4 class="mb-4 font-heading text-sm font-bold text-paper">
                        {{ t('shop.footer.account') }}
                    </h4>
                    <ul class="grid gap-2.5">
                        <template v-if="page.props.auth.user">
                            <li>
                                <Link
                                    :href="dashboard.url()"
                                    class="text-sm text-paper/70 transition hover:text-paper"
                                >
                                    {{ t('shop.footer.my_account') }}
                                </Link>
                            </li>
                            <li>
                                <Link
                                    :href="accountOrders.url()"
                                    class="text-sm text-paper/70 transition hover:text-paper"
                                >
                                    {{ t('shop.footer.my_orders') }}
                                </Link>
                            </li>
                            <li>
                                <Link
                                    :href="accountAddresses.url()"
                                    class="text-sm text-paper/70 transition hover:text-paper"
                                >
                                    {{ t('shop.footer.my_addresses') }}
                                </Link>
                            </li>
                        </template>
                        <template v-else>
                            <li>
                                <Link
                                    :href="login.url()"
                                    class="text-sm text-paper/70 transition hover:text-paper"
                                >
                                    {{ t('shop.footer.log_in') }}
                                </Link>
                            </li>
                            <li>
                                <Link
                                    :href="register.url()"
                                    class="text-sm text-paper/70 transition hover:text-paper"
                                >
                                    {{ t('shop.footer.create_account') }}
                                </Link>
                            </li>
                        </template>
                    </ul>
                </div>
            </div>

            <div
                class="flex flex-wrap items-center justify-between gap-3 pt-5 font-mono text-xs tracking-[0.04em] text-paper/50"
            >
                <span>{{
                    t('shop.footer.copyright', { year: currentYear })
                }}</span>
                <a
                    href="https://laravelshopper.dev"
                    class="inline-flex items-center gap-x-2 transition hover:text-paper"
                    target="_blank"
                    rel="noopener"
                >
                    <span>{{ t('shop.footer.powered_by') }}</span>
                    <BrandShopper class="size-4 opacity-60" />
                    <span>&amp;</span>
                    <BrandVue class="size-4 opacity-60" />
                </a>
            </div>
        </Container>
    </footer>
</template>
