<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { ChevronDown, ChevronRight, LayoutGrid, Menu } from 'lucide-vue-next';
import { computed, onBeforeUnmount, ref } from 'vue';
import Container from '@/components/shop/container.vue';
import { useTrans } from '@/composables/useTrans';
import { cn } from '@/lib/utils';
import * as shop from '@/routes/shop';
import type { NavCategory, NavMenuItem } from '@/types/shop';

const page = usePage();
const { t } = useTrans();

const megaOpen = ref<boolean>(false);
const menuMegaId = ref<number | null>(null);
const currentPath = computed<string>(() => (page.url ?? '').split('?')[0]);

const navCategories = computed<NavCategory[]>(
    () => page.props.shop?.nav_categories ?? [],
);

const navMenu = computed<NavMenuItem[]>(
    () => page.props.shop?.nav_menu ?? [],
);

const megaMenuItems = computed<NavMenuItem[]>(() =>
    navMenu.value.filter((item) => item.children.length > 0),
);

let closeTimer: ReturnType<typeof setTimeout> | null = null;

function clearCloseTimer(): void {
    if (closeTimer !== null) {
        clearTimeout(closeTimer);
        closeTimer = null;
    }
}

function openCategoriesMega(): void {
    clearCloseTimer();
    menuMegaId.value = null;
    megaOpen.value = true;
}

function openMenuMega(item: NavMenuItem): void {
    clearCloseTimer();
    megaOpen.value = false;
    menuMegaId.value = item.children.length > 0 ? item.id : null;
}

function scheduleCloseMega(): void {
    clearCloseTimer();

    closeTimer = setTimeout(() => {
        megaOpen.value = false;
        menuMegaId.value = null;
        closeTimer = null;
    }, 120);
}

function closeMega(): void {
    clearCloseTimer();
    megaOpen.value = false;
    menuMegaId.value = null;
}

function isActive(href: string): boolean {
    return currentPath.value === href || currentPath.value.startsWith(`${href}/`);
}

function isCategoryActive(slug: string): boolean {
    return currentPath.value.startsWith(`/categories/${slug}`);
}

function isExternal(href: string): boolean {
    return href.startsWith('http://') || href.startsWith('https://');
}

function hasChildren(item: Pick<NavMenuItem, 'children'>): boolean {
    return item.children.length > 0;
}

onBeforeUnmount(() => {
    clearCloseTimer();
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
                @mouseenter="openCategoriesMega"
                @focus="openCategoriesMega"
                @click="closeMega"
            >
                <Menu class="size-4" aria-hidden="true" />
                {{ t('shop.nav.all_categories') }}
            </Link>

            <div class="mr-auto flex gap-10 uppercase" :aria-label="t('shop.nav.menu')">
                <template v-for="item in navMenu" :key="item.id">
                    <a
                        v-if="isExternal(item.href)"
                        :href="item.href"
                        rel="noopener noreferrer"
                        :class="
                            cn(
                                'inline-flex items-center gap-1 py-1.5 text-sm font-medium text-ink-soft transition hover:text-brand',
                            )
                        "
                        @mouseenter="openMenuMega(item)"
                        @focus="openMenuMega(item)"
                    >
                        {{ item.title }}
                        <ChevronDown
                            v-if="hasChildren(item)"
                            :class="
                                cn(
                                    'size-3.5 shrink-0 transition duration-200',
                                    menuMegaId === item.id && 'rotate-180',
                                )
                            "
                            aria-hidden="true"
                        />
                    </a>
                    <Link
                        v-else
                        :href="item.href"
                        :aria-current="isActive(item.href) ? 'page' : undefined"
                        :aria-expanded="
                            hasChildren(item)
                                ? menuMegaId === item.id
                                : undefined
                        "
                        :aria-controls="
                            hasChildren(item)
                                ? `storefront-menu-mega-${item.id}`
                                : undefined
                        "
                        :class="
                            cn(
                                'inline-flex items-center gap-1 py-1.5 text-sm font-medium transition hover:text-brand',
                                isActive(item.href)
                                    ? 'text-brand'
                                    : 'text-ink-soft',
                            )
                        "
                        @mouseenter="openMenuMega(item)"
                        @focus="openMenuMega(item)"
                        @click="closeMega"
                    >
                        {{ item.title }}
                        <ChevronDown
                            v-if="hasChildren(item)"
                            :class="
                                cn(
                                    'size-3.5 shrink-0 transition duration-200',
                                    menuMegaId === item.id && 'rotate-180',
                                )
                            "
                            aria-hidden="true"
                        />
                    </Link>
                </template>
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
                @mouseenter="openCategoriesMega"
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
                                <span class="min-w-0 flex-1">{{
                                    category.name
                                }}</span>
                                <ChevronRight
                                    v-if="category.children?.length"
                                    class="size-3.5 shrink-0 text-ink-faint"
                                    aria-hidden="true"
                                />
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

            <div
                v-for="item in megaMenuItems"
                v-show="menuMegaId === item.id"
                :id="`storefront-menu-mega-${item.id}`"
                :key="`mega-${item.id}`"
                class="absolute inset-x-0 top-full z-50 pt-1.5"
                @mouseenter="openMenuMega(item)"
            >
                <div
                    class="overflow-hidden rounded-xl border border-rule bg-popover shadow-lg"
                    :aria-label="item.title"
                >
                    <ul
                        class="grid w-full grid-cols-[repeat(auto-fill,minmax(11rem,1fr))] gap-6 p-5"
                    >
                        <li
                            v-for="child in item.children"
                            :key="child.id"
                        >
                            <a
                                v-if="isExternal(child.href)"
                                :href="child.href"
                                rel="noopener noreferrer"
                                class="flex items-center justify-between gap-2 rounded-md p-2 font-heading text-sm font-semibold transition-colors hover:bg-brand-soft hover:text-brand-deep"
                                @click="closeMega"
                            >
                                <span>{{ child.title }}</span>
                                <ChevronRight
                                    v-if="hasChildren(child)"
                                    class="size-3.5 shrink-0 text-ink-faint"
                                    aria-hidden="true"
                                />
                            </a>
                            <Link
                                v-else
                                :href="child.href"
                                :class="
                                    cn(
                                        'flex items-center justify-between gap-2 rounded-md p-2 font-heading text-sm font-semibold transition-colors hover:bg-brand-soft hover:text-brand-deep',
                                        isActive(child.href) &&
                                            'bg-brand-soft text-brand-deep',
                                    )
                                "
                                @click="closeMega"
                            >
                                <span>{{ child.title }}</span>
                                <ChevronRight
                                    v-if="hasChildren(child)"
                                    class="size-3.5 shrink-0 text-ink-faint"
                                    aria-hidden="true"
                                />
                            </Link>

                            <ul
                                v-if="child.children.length"
                                class="mt-1 space-y-0.5"
                            >
                                <li
                                    v-for="grandchild in child.children"
                                    :key="grandchild.id"
                                >
                                    <a
                                        v-if="isExternal(grandchild.href)"
                                        :href="grandchild.href"
                                        rel="noopener noreferrer"
                                        class="block rounded-md p-2 text-sm text-ink-mute transition-colors hover:bg-brand-soft hover:text-brand-deep"
                                        @click="closeMega"
                                    >
                                        {{ grandchild.title }}
                                    </a>
                                    <Link
                                        v-else
                                        :href="grandchild.href"
                                        :class="
                                            cn(
                                                'block rounded-md p-2 text-sm text-ink-mute transition-colors hover:bg-brand-soft hover:text-brand-deep',
                                                isActive(grandchild.href) &&
                                                    'bg-brand-soft text-brand-deep',
                                            )
                                        "
                                        @click="closeMega"
                                    >
                                        {{ grandchild.title }}
                                    </Link>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </Container>
    </nav>
</template>
