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
import { dashboard, login, logout, register } from '@/routes';
import * as shop from '@/routes/shop';
import type { NavCategory, NavMenuItem } from '@/types/shop';

const open = defineModel<boolean>('open', { required: true });

const page = usePage();
const { t } = useTrans();

const navCategories = computed<NavCategory[]>(
    () => page.props.shop?.nav_categories ?? [],
);

const navMenu = computed<NavMenuItem[]>(
    () => page.props.shop?.nav_menu ?? [],
);

function close(): void {
    open.value = false;
}

function isExternal(href: string): boolean {
    return href.startsWith('http://') || href.startsWith('https://');
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
                <div v-if="navMenu.length > 0" class="flex flex-col">
                    <div
                        v-for="item in navMenu"
                        :key="item.id"
                        class="border-b border-rule py-3.5"
                    >
                        <a
                            v-if="isExternal(item.href)"
                            :href="item.href"
                            rel="noopener noreferrer"
                            class="font-heading text-md font-semibold text-ink transition hover:text-brand"
                            @click="close"
                        >
                            {{ item.title }}
                        </a>
                        <Link
                            v-else
                            :href="item.href"
                            class="font-heading text-md font-semibold text-ink transition hover:text-brand"
                            @click="close"
                        >
                            {{ item.title }}
                        </Link>

                        <div
                            v-if="item.children.length"
                            class="mt-2 space-y-1.5"
                        >
                            <div
                                v-for="child in item.children"
                                :key="child.id"
                            >
                                <a
                                    v-if="isExternal(child.href)"
                                    :href="child.href"
                                    rel="noopener noreferrer"
                                    class="block pl-4 text-sm font-medium text-ink-soft transition hover:text-brand"
                                    @click="close"
                                >
                                    {{ child.title }}
                                </a>
                                <Link
                                    v-else
                                    :href="child.href"
                                    class="block pl-4 text-sm font-medium text-ink-soft transition hover:text-brand"
                                    @click="close"
                                >
                                    {{ child.title }}
                                </Link>

                                <div
                                    v-if="child.children.length"
                                    class="mt-1 space-y-1"
                                >
                                    <template
                                        v-for="grandchild in child.children"
                                        :key="grandchild.id"
                                    >
                                        <a
                                            v-if="isExternal(grandchild.href)"
                                            :href="grandchild.href"
                                            rel="noopener noreferrer"
                                            class="block pl-8 text-sm text-ink-mute transition hover:text-brand"
                                            @click="close"
                                        >
                                            {{ grandchild.title }}
                                        </a>
                                        <Link
                                            v-else
                                            :href="grandchild.href"
                                            class="block pl-8 text-sm text-ink-mute transition hover:text-brand"
                                            @click="close"
                                        >
                                            {{ grandchild.title }}
                                        </Link>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
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
