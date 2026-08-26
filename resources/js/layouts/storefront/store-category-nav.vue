<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { LayoutGrid, Menu } from 'lucide-vue-next';
import { computed, onBeforeUnmount, ref } from 'vue';
import Container from '@/components/shop/container.vue';
import { useTrans } from '@/composables/useTrans';
import { cn } from '@/lib/utils';
import { home } from '@/routes';
import * as shop from '@/routes/shop';
import type { NavCategory } from '@/types/shop';

const page = usePage();
const { t } = useTrans();

const megaOpen = ref<boolean>(false);
const currentPath = computed<string>(() => (page.url ?? '').split('?')[0]);

const navCategories = computed<NavCategory[]>(
    () => page.props.shop?.nav_categories ?? [],
);

const links = computed(() => [
    { href: home.url(), label: t('shop.nav.home'), exact: true },
    { href: shop.index.url(), label: t('shop.nav.shop') },
    { href: shop.categories.url(), label: t('shop.nav.categories') },
    { href: shop.cart.url(), label: t('shop.nav.cart') },
    { href: shop.contact.url(), label: t('shop.nav.contact') },
]);

let closeTimer: ReturnType<typeof setTimeout> | null = null;

function openMega(): void {
    if (closeTimer !== null) {
        clearTimeout(closeTimer);
        closeTimer = null;
    }

    megaOpen.value = true;
}

function scheduleCloseMega(): void {
    if (closeTimer !== null) {
        clearTimeout(closeTimer);
    }

    closeTimer = setTimeout(() => {
        megaOpen.value = false;
        closeTimer = null;
    }, 120);
}

function closeMega(): void {
    if (closeTimer !== null) {
        clearTimeout(closeTimer);
        closeTimer = null;
    }

    megaOpen.value = false;
}

function isActive(href: string, exact = false): boolean {
    return exact
        ? currentPath.value === href
        : currentPath.value.startsWith(href);
}

function isCategoryActive(slug: string): boolean {
    return currentPath.value.startsWith(`/categories/${slug}`);
}

onBeforeUnmount(() => {
    if (closeTimer !== null) {
        clearTimeout(closeTimer);
    }
});
</script>

<template>
    <nav
        class="sticky z-55 hidden border-b border-rule bg-paper lg:block"
        :style="{ top: 'var(--header-h)' }"
        :aria-label="t('shop.nav.primary')"
    >
        <Container
            class="relative flex h-(--nav-h) items-center gap-10"
            @mouseleave="scheduleCloseMega"
        >
            <Link
                :href="shop.categories.url()"
                class="inline-flex items-center gap-2 rounded-sm bg-primary px-5 py-2.5 text-sm font-semibold text-paper transition hover:bg-brand-deep"
                :aria-expanded="megaOpen"
                aria-controls="storefront-categories-mega-menu"
                @mouseenter="openMega"
                @focus="openMega"
                @click="closeMega"
            >
                <Menu class="size-4" aria-hidden="true" />
                {{ t('shop.nav.all_categories') }}
            </Link>

            <div class="mr-auto flex gap-10">
                <Link
                    v-for="link in links"
                    :key="link.href"
                    :href="link.href"
                    :aria-current="
                        isActive(link.href, link.exact) ? 'page' : undefined
                    "
                    :class="
                        cn(
                            'inline-flex items-center gap-1 py-1.5 text-sm font-medium transition hover:text-brand',
                            isActive(link.href, link.exact)
                                ? 'text-brand'
                                : 'text-ink-soft',
                        )
                    "
                >
                    {{ link.label }}
                </Link>
            </div>

            <span
                class="inline-flex items-center gap-2 font-mono text-xs tracking-[0.04em] text-ink-mute"
            >
                {{ t('shop.nav.promo_prefix') }}
                <strong class="font-bold text-rose">
                    {{ t('shop.nav.promo_highlight') }}
                </strong>
                {{ t('shop.nav.promo_suffix') }}
            </span>

            <div
                v-show="megaOpen && navCategories.length > 0"
                id="storefront-categories-mega-menu"
                class="absolute inset-x-0 top-full z-50 pt-1.5"
                @mouseenter="openMega"
            >
                <div
                    class="overflow-hidden rounded-xl border border-rule bg-popover shadow-lg"
                >
                    <ul
                        class="grid w-full grid-cols-[repeat(auto-fill,minmax(11rem,1fr))] gap-6 p-5"
                    >
                        <li
                            v-for="category in navCategories"
                            :key="category.id"
                        >
                            <Link
                                :href="
                                    shop.category.url({
                                        category: category.slug,
                                    })
                                "
                                :class="
                                    cn(
                                        'flex items-center gap-2 rounded-md p-2 font-heading text-sm font-semibold transition-colors hover:bg-brand-soft hover:text-brand-deep',
                                        isCategoryActive(category.slug) &&
                                            'bg-brand-soft text-brand-deep',
                                    )
                                "
                                @click="closeMega"
                            >
                                <img
                                    v-if="category.thumbnail"
                                    :src="category.thumbnail"
                                    :alt="category.name"
                                    class="size-6 rounded-sm object-cover"
                                />
                                <LayoutGrid
                                    v-else
                                    class="size-4 shrink-0 text-ink-faint"
                                    aria-hidden="true"
                                />
                                <span>{{ category.name }}</span>
                            </Link>

                            <ul
                                v-if="category.children?.length"
                                class="mt-1 space-y-0.5"
                            >
                                <li
                                    v-for="child in category.children"
                                    :key="child.id"
                                >
                                    <Link
                                        :href="
                                            shop.category.url({
                                                category: child.slug,
                                            })
                                        "
                                        :class="
                                            cn(
                                                'block rounded-md p-2 text-sm text-ink-mute transition-colors hover:bg-brand-soft hover:text-brand-deep',
                                                isCategoryActive(child.slug) &&
                                                    'bg-brand-soft text-brand-deep',
                                            )
                                        "
                                        @click="closeMega"
                                    >
                                        {{ child.name }}
                                    </Link>
                                </li>
                            </ul>
                        </li>
                    </ul>

                    <div class="border-t border-rule px-5 py-3.5">
                        <Link
                            :href="shop.categories.url()"
                            class="flex items-center justify-between gap-2 rounded-md p-2 text-sm transition-colors hover:bg-brand-soft hover:text-brand-deep"
                            @click="closeMega"
                        >
                            <span class="font-semibold">
                                {{ t('shop.nav.all_categories') }}
                            </span>
                            <span class="font-mono text-xs text-ink-mute">
                                {{ t('shop.categories.subtitle') }}
                            </span>
                        </Link>
                    </div>
                </div>
            </div>
        </Container>
    </nav>
</template>
