<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    ChevronDown,
    LayoutGrid,
    Menu,
    Search,
    ShoppingBag,
    User,
    X,
} from 'lucide-vue-next';
import { computed, onBeforeUnmount, ref } from 'vue';
import AnnouncementBar from '@/components/shop/announcement-bar.vue';
import BrandIcon from '@/components/shop/brand-icon.vue';
import Container from '@/components/shop/container.vue';
import {
    NavigationMenu,
    NavigationMenuItem,
    NavigationMenuLink,
    NavigationMenuList,
    navigationMenuTriggerStyle,
} from '@/components/ui/navigation-menu';
import { useShop } from '@/composables/useShop';
import { useTrans } from '@/composables/useTrans';
import { cn } from '@/lib/utils';
import { dashboard, home, login, logout, register } from '@/routes';
import * as shop from '@/routes/shop';
import type { NavCategory } from '@/types/shop';

const page = usePage();
const { cartCount } = useShop();
const { t } = useTrans();

const mobileOpen = ref<boolean>(false);
const categoriesOpen = ref<boolean>(false);
const currentUrl = computed<string>(() => page.url ?? '');

const navCategories = computed<NavCategory[]>(
    () => page.props.shop?.nav_categories ?? [],
);

const isHomeActive = computed<boolean>(
    () => currentUrl.value === '/' || currentUrl.value === '',
);

const isShopActive = computed<boolean>(() => {
    const url = currentUrl.value;

    return url === '/shop' || url.startsWith('/shop?');
});

const isCategoriesActive = computed<boolean>(() =>
    currentUrl.value.startsWith('/categories'),
);

let categoriesCloseTimer: ReturnType<typeof setTimeout> | null = null;

function openCategoriesMenu(): void {
    if (categoriesCloseTimer !== null) {
        clearTimeout(categoriesCloseTimer);
        categoriesCloseTimer = null;
    }

    categoriesOpen.value = true;
}

function scheduleCloseCategoriesMenu(): void {
    if (categoriesCloseTimer !== null) {
        clearTimeout(categoriesCloseTimer);
    }

    categoriesCloseTimer = setTimeout(() => {
        categoriesOpen.value = false;
        categoriesCloseTimer = null;
    }, 120);
}

function closeCategoriesMenu(): void {
    if (categoriesCloseTimer !== null) {
        clearTimeout(categoriesCloseTimer);
        categoriesCloseTimer = null;
    }

    categoriesOpen.value = false;
}

function isCategoryActive(slug: string): boolean {
    return currentUrl.value.startsWith(`/categories/${slug}`);
}

onBeforeUnmount(() => {
    if (categoriesCloseTimer !== null) {
        clearTimeout(categoriesCloseTimer);
    }
});
</script>

<template>
    <header
        class="sticky top-0 z-30 border-b border-zinc-200 bg-white/80 backdrop-blur-xl dark:border-white/10 dark:bg-zinc-900"
    >
        <AnnouncementBar :message="t('shop.announcement.promo')" />

        <Container
            class="relative"
            @mouseleave="scheduleCloseCategoriesMenu"
        >
            <div class="flex h-16 items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        class="-ml-2 rounded-md p-2 text-zinc-500 hover:text-zinc-900 lg:hidden dark:hover:text-white"
                        :aria-label="t('shop.nav.open_menu')"
                        @click="mobileOpen = !mobileOpen"
                    >
                        <Menu
                            v-if="!mobileOpen"
                            class="size-5"
                            aria-hidden="true"
                        />
                        <X v-else class="size-5" aria-hidden="true" />
                    </button>

                    <Link :href="home.url()" class="flex items-center gap-2">
                        <BrandIcon
                            class="h-8 w-auto fill-current text-black dark:text-white"
                        />
                    </Link>
                </div>

                <NavigationMenu
                    :viewport="false"
                    class="hidden max-w-none lg:flex"
                >
                    <NavigationMenuList>
                        <NavigationMenuItem>
                            <NavigationMenuLink
                                as-child
                                :active="isHomeActive"
                                :class="navigationMenuTriggerStyle()"
                            >
                                <Link :href="home.url()">
                                    {{ t('shop.nav.home') }}
                                </Link>
                            </NavigationMenuLink>
                        </NavigationMenuItem>

                        <NavigationMenuItem>
                            <NavigationMenuLink
                                as-child
                                :active="isShopActive"
                                :class="navigationMenuTriggerStyle()"
                            >
                                <Link :href="shop.index.url()">
                                    {{ t('shop.nav.shop') }}
                                </Link>
                            </NavigationMenuLink>
                        </NavigationMenuItem>

                        <NavigationMenuItem v-if="navCategories.length > 0">
                            <button
                                type="button"
                                :class="
                                    cn(
                                        navigationMenuTriggerStyle(),
                                        'group',
                                        (categoriesOpen ||
                                            isCategoriesActive) &&
                                            'bg-accent/50 text-accent-foreground',
                                    )
                                "
                                :aria-expanded="categoriesOpen"
                                aria-controls="storefront-categories-mega-menu"
                                @mouseenter="openCategoriesMenu"
                                @focus="openCategoriesMenu"
                                @click="
                                    categoriesOpen
                                        ? closeCategoriesMenu()
                                        : openCategoriesMenu()
                                "
                            >
                                {{ t('shop.nav.categories') }}
                                <ChevronDown
                                    class="relative top-px ml-1 size-3 transition duration-300"
                                    :class="
                                        categoriesOpen
                                            ? 'rotate-180'
                                            : undefined
                                    "
                                    aria-hidden="true"
                                />
                            </button>
                        </NavigationMenuItem>
                    </NavigationMenuList>
                </NavigationMenu>

                <div class="flex items-center gap-4">
                    <Link
                        :href="shop.search.url()"
                        class="text-zinc-500 transition hover:text-zinc-900 dark:hover:text-white"
                        :aria-label="t('shop.nav.search')"
                    >
                        <Search class="size-5" aria-hidden="true" />
                    </Link>

                    <Link
                        :href="shop.cart.url()"
                        class="relative text-zinc-500 transition hover:text-zinc-900 dark:hover:text-white"
                        :aria-label="t('shop.nav.cart')"
                    >
                        <ShoppingBag class="size-5" aria-hidden="true" />
                        <span
                            v-if="cartCount > 0"
                            class="absolute -top-2 -right-2 inline-flex h-4 min-w-4 items-center justify-center rounded-full bg-zinc-900 px-1 text-[10px] font-medium text-white dark:bg-white dark:text-zinc-900"
                        >
                            {{ cartCount }}
                        </span>
                    </Link>

                    <Link
                        v-if="page.props.auth.user"
                        :href="dashboard.url()"
                        class="hidden text-zinc-500 transition hover:text-zinc-900 lg:inline-flex dark:hover:text-white"
                        :aria-label="t('shop.nav.account')"
                    >
                        <User class="size-5" aria-hidden="true" />
                    </Link>
                    <Link
                        v-else
                        :href="login.url()"
                        class="hidden text-zinc-500 transition hover:text-zinc-900 lg:inline-flex dark:hover:text-white"
                        :aria-label="t('shop.nav.sign_in')"
                    >
                        <User class="size-5" aria-hidden="true" />
                    </Link>
                </div>
            </div>

            <div
                v-show="categoriesOpen && navCategories.length > 0"
                id="storefront-categories-mega-menu"
                class="absolute inset-x-0 top-full z-50 hidden pt-1.5 lg:block"
                @mouseenter="openCategoriesMenu"
            >
                <div
                    class="bg-popover text-popover-foreground overflow-hidden rounded-md border shadow"
                >
                    <ul
                        class="grid w-full grid-cols-[repeat(auto-fill,minmax(11rem,1fr))] gap-6 p-4"
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
                                        'hover:bg-accent hover:text-accent-foreground focus:bg-accent focus:text-accent-foreground flex items-center gap-2 rounded-sm p-2 text-sm font-medium transition-colors',
                                        isCategoryActive(category.slug) &&
                                            'bg-accent/50 text-accent-foreground',
                                    )
                                "
                                @click="closeCategoriesMenu"
                            >
                                <img
                                    v-if="category.thumbnail"
                                    :src="category.thumbnail"
                                    :alt="category.name"
                                    class="size-5 rounded object-cover"
                                />
                                <LayoutGrid
                                    v-else
                                    class="text-muted-foreground size-4 shrink-0"
                                    aria-hidden="true"
                                />
                                <span>{{ category.name }}</span>
                            </Link>

                            <ul
                                v-if="category.children?.length"
                                class="mt-1 space-y-1"
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
                                                'text-muted-foreground hover:bg-accent hover:text-accent-foreground focus:bg-accent focus:text-accent-foreground block rounded-sm p-2 text-sm transition-colors',
                                                isCategoryActive(child.slug) &&
                                                    'bg-accent/50 text-accent-foreground',
                                            )
                                        "
                                        @click="closeCategoriesMenu"
                                    >
                                        {{ child.name }}
                                    </Link>
                                </li>
                            </ul>
                        </li>
                    </ul>

                    <div class="border-border border-t px-4 py-3">
                        <Link
                            :href="shop.categories.url()"
                            class="hover:bg-accent hover:text-accent-foreground focus:bg-accent focus:text-accent-foreground flex items-center justify-between gap-2 rounded-sm p-2 text-sm transition-colors"
                            @click="closeCategoriesMenu"
                        >
                            <span class="font-medium">
                                {{ t('shop.nav.all_categories') }}
                            </span>
                            <span class="text-muted-foreground text-xs">
                                {{ t('shop.categories.subtitle') }}
                            </span>
                        </Link>
                    </div>
                </div>
            </div>
        </Container>

        <div
            v-if="mobileOpen"
            class="border-t border-zinc-200 lg:hidden dark:border-zinc-700"
        >
            <div class="mx-auto max-w-7xl space-y-3 px-4 py-4 sm:px-6">
                <Link
                    :href="home.url()"
                    class="block text-sm text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white"
                    @click="mobileOpen = false"
                >
                    {{ t('shop.nav.home') }}
                </Link>
                <Link
                    :href="shop.index.url()"
                    class="block text-sm text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white"
                    @click="mobileOpen = false"
                >
                    {{ t('shop.nav.shop') }}
                </Link>
                <Link
                    :href="shop.categories.url()"
                    class="block text-sm text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white"
                    @click="mobileOpen = false"
                >
                    {{ t('shop.nav.categories') }}
                </Link>

                <div
                    v-for="category in navCategories"
                    :key="category.id"
                    class="space-y-2"
                >
                    <Link
                        :href="shop.category.url({ category: category.slug })"
                        class="flex items-center gap-2 text-sm font-medium text-zinc-900 dark:text-white"
                        @click="mobileOpen = false"
                    >
                        <img
                            v-if="category.thumbnail"
                            :src="category.thumbnail"
                            :alt="category.name"
                            class="size-5 rounded object-cover"
                        />
                        <LayoutGrid
                            v-else
                            class="size-4 shrink-0 text-zinc-500"
                            aria-hidden="true"
                        />
                        {{ category.name }}
                    </Link>
                    <Link
                        v-for="child in category.children ?? []"
                        :key="child.id"
                        :href="shop.category.url({ category: child.slug })"
                        class="block pl-7 text-sm text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white"
                        @click="mobileOpen = false"
                    >
                        {{ child.name }}
                    </Link>
                </div>

                <div
                    class="space-y-3 border-t border-zinc-200 pt-3 dark:border-zinc-700"
                >
                    <template v-if="page.props.auth.user">
                        <Link
                            :href="dashboard.url()"
                            class="block text-sm text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white"
                            @click="mobileOpen = false"
                        >
                            {{ t('shop.nav.my_account') }}
                        </Link>
                        <form method="POST" :action="logout.url()">
                            <button
                                type="submit"
                                class="text-sm text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white"
                            >
                                {{ t('shop.nav.log_out') }}
                            </button>
                        </form>
                    </template>
                    <template v-else>
                        <Link
                            :href="login.url()"
                            class="block text-sm text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white"
                            @click="mobileOpen = false"
                        >
                            {{ t('shop.nav.log_in') }}
                        </Link>
                        <Link
                            :href="register.url()"
                            class="block text-sm text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white"
                            @click="mobileOpen = false"
                        >
                            {{ t('shop.nav.create_account') }}
                        </Link>
                    </template>
                </div>
            </div>
        </div>
    </header>
</template>
