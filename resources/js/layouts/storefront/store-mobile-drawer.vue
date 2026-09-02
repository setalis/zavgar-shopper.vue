<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { LayoutGrid } from 'lucide-vue-next';
import { computed } from 'vue';
import BrandIcon from '@/components/shop/brand-icon.vue';
import LocaleSwitcher from '@/components/shop/locale-switcher.vue';
import { Button } from '@/components/ui/button';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
} from '@/components/ui/sheet';
import { useTrans } from '@/composables/useTrans';
import { dashboard, home, login, logout, register } from '@/routes';
import * as shop from '@/routes/shop';
import type { NavCategory } from '@/types/shop';

const open = defineModel<boolean>('open', { required: true });

const page = usePage();
const { t } = useTrans();

const navCategories = computed<NavCategory[]>(
    () => page.props.shop?.nav_categories ?? [],
);

const links = computed(() => [
    { href: home.url(), label: t('shop.nav.home') },
    { href: shop.index.url(), label: t('shop.nav.shop') },
    { href: shop.categories.url(), label: t('shop.nav.categories') },
    { href: shop.wishlist.url(), label: t('shop.nav.wishlist') },
    { href: shop.cart.url(), label: t('shop.nav.cart') },
    { href: shop.contact.url(), label: t('shop.nav.contact') },
]);

function close(): void {
    open.value = false;
}
</script>

<template>
    <Sheet v-model:open="open">
        <SheetContent side="left" class="w-[88vw] gap-0 sm:max-w-sm">
            <SheetHeader class="border-b border-rule p-5">
                <SheetTitle class="flex items-center gap-2">
                    <BrandIcon class="h-8 w-auto fill-current text-brand" />
                    <span
                        class="font-heading text-xl font-extrabold tracking-[-0.02em]"
                    >
                        {{ page.props.name }}
                    </span>
                </SheetTitle>
                <SheetDescription class="sr-only">
                    {{ t('shop.nav.primary') }}
                </SheetDescription>
            </SheetHeader>

            <div class="flex-1 overflow-y-auto p-5">
                <div class="flex flex-col">
                    <Link
                        v-for="link in links"
                        :key="link.href"
                        :href="link.href"
                        class="border-b border-rule py-3.5 font-heading text-md font-semibold text-ink transition hover:text-brand"
                        @click="close"
                    >
                        {{ link.label }}
                    </Link>
                </div>

                <div v-if="navCategories.length > 0" class="mt-6 space-y-4">
                    <p
                        class="font-mono text-xs tracking-[0.08em] text-ink-faint uppercase"
                    >
                        {{ t('shop.nav.all_categories') }}
                    </p>

                    <div
                        v-for="category in navCategories"
                        :key="category.id"
                        class="space-y-1.5"
                    >
                        <Link
                            :href="
                                shop.category.url({ category: category.slug })
                            "
                            class="flex items-center gap-2 text-sm font-semibold text-ink transition hover:text-brand"
                            @click="close"
                        >
                            <img
                                v-if="category.thumbnail"
                                :src="category.thumbnail"
                                :alt="category.name"
                                class="size-5 rounded-sm object-cover"
                            />
                            <LayoutGrid
                                v-else
                                class="size-4 shrink-0 text-ink-faint"
                                aria-hidden="true"
                            />
                            {{ category.name }}
                        </Link>
                        <Link
                            v-for="child in category.children ?? []"
                            :key="child.id"
                            :href="shop.category.url({ category: child.slug })"
                            class="block pl-7 text-sm text-ink-mute transition hover:text-brand"
                            @click="close"
                        >
                            {{ child.name }}
                        </Link>
                    </div>
                </div>

                <div class="mt-6 space-y-3 border-t border-rule pt-5">
                    <template v-if="page.props.auth.user">
                        <Link
                            :href="dashboard.url()"
                            class="block text-sm text-ink-soft transition hover:text-brand"
                            @click="close"
                        >
                            {{ t('shop.nav.my_account') }}
                        </Link>
                        <form method="POST" :action="logout.url()">
                            <button
                                type="submit"
                                class="text-sm text-destructive transition hover:opacity-80"
                            >
                                {{ t('shop.nav.log_out') }}
                            </button>
                        </form>
                    </template>
                    <template v-else>
                        <Link
                            :href="login.url()"
                            class="block text-sm text-ink-soft transition hover:text-brand"
                            @click="close"
                        >
                            {{ t('shop.nav.log_in') }}
                        </Link>
                        <Link
                            :href="register.url()"
                            class="block text-sm text-ink-soft transition hover:text-brand"
                            @click="close"
                        >
                            {{ t('shop.nav.create_account') }}
                        </Link>
                    </template>
                </div>
            </div>

            <div
                class="flex items-center justify-between gap-3 border-t border-rule bg-muted p-5"
            >
                <LocaleSwitcher class="text-ink-mute" />
                <Button as-child size="sm">
                    <Link :href="shop.cart.url()" @click="close">
                        {{ t('shop.cart.view') }}
                    </Link>
                </Button>
            </div>
        </SheetContent>
    </Sheet>
</template>
