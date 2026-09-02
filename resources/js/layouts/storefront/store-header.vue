<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Heart, Menu, Search, ShoppingBag, User } from 'lucide-vue-next';
import BrandIcon from '@/components/shop/brand-icon.vue';
import Container from '@/components/shop/container.vue';
import HeaderSearch from '@/components/shop/header-search.vue';
import { useShop } from '@/composables/useShop';
import { useTrans } from '@/composables/useTrans';
import { dashboard, home, login } from '@/routes';
import * as shop from '@/routes/shop';

const emit = defineEmits<{ openMenu: [] }>();

const page = usePage();
const { cartCount, wishlistCount } = useShop();
const { t } = useTrans();
</script>

<template>
    <header
        class="sticky top-0 z-60 overflow-visible border-b border-rule bg-paper"
        :style="{ height: 'var(--header-h)' }"
    >
        <Container
            class="grid h-full grid-cols-[auto_1fr_auto] items-center gap-4 md:gap-10"
        >
            <Link
                :href="home.url()"
                class="inline-flex items-center gap-2 font-heading text-xl font-extrabold tracking-[-0.02em] text-ink md:text-2xl"
            >
                <BrandIcon class="h-9 w-auto fill-current text-brand" />
                <span class="sr-only md:not-sr-only">
                    {{ page.props.name }}
                </span>
            </Link>

            <HeaderSearch />

            <div class="inline-flex items-center justify-end gap-2">
                <Link
                    :href="shop.search.url()"
                    class="grid size-11 place-items-center rounded-full bg-muted text-ink transition hover:bg-brand-soft hover:text-brand md:hidden"
                    :aria-label="t('shop.nav.search')"
                >
                    <Search class="size-5" aria-hidden="true" />
                </Link>

                <Link
                    v-if="page.props.auth.user"
                    :href="dashboard.url()"
                    class="hidden size-11 place-items-center rounded-full bg-muted text-ink transition hover:bg-brand-soft hover:text-brand md:grid"
                    :aria-label="t('shop.nav.account')"
                >
                    <User class="size-5" aria-hidden="true" />
                </Link>
                <Link
                    v-else
                    :href="login.url()"
                    class="hidden size-11 place-items-center rounded-full bg-muted text-ink transition hover:bg-brand-soft hover:text-brand md:grid"
                    :aria-label="t('shop.nav.sign_in')"
                >
                    <User class="size-5" aria-hidden="true" />
                </Link>

                <Link
                    :href="shop.wishlist.url()"
                    class="relative hidden size-11 place-items-center rounded-full bg-muted text-ink transition hover:bg-brand-soft hover:text-brand md:grid"
                    :aria-label="t('shop.nav.wishlist')"
                >
                    <Heart class="size-5" aria-hidden="true" />
                    <span
                        v-if="wishlistCount > 0"
                        class="absolute top-1 right-1 grid h-[18px] min-w-[18px] place-items-center rounded-full border-2 border-paper bg-primary px-1 font-mono text-[10px] font-semibold text-paper"
                    >
                        {{ wishlistCount }}
                    </span>
                </Link>

                <Link
                    :href="shop.cart.url()"
                    class="relative grid size-11 place-items-center rounded-full bg-ink text-paper transition hover:bg-primary"
                    :aria-label="t('shop.nav.cart')"
                >
                    <ShoppingBag class="size-5" aria-hidden="true" />
                    <span
                        v-if="cartCount > 0"
                        class="absolute top-1 right-1 grid h-[18px] min-w-[18px] place-items-center rounded-full border-2 border-paper bg-primary px-1 font-mono text-[10px] font-semibold text-paper"
                    >
                        {{ cartCount }}
                    </span>
                </Link>

                <button
                    type="button"
                    class="grid size-11 place-items-center rounded-full text-ink transition hover:bg-muted lg:hidden"
                    :aria-label="t('shop.nav.open_menu')"
                    @click="emit('openMenu')"
                >
                    <Menu class="size-6" aria-hidden="true" />
                </button>
            </div>
        </Container>
    </header>
</template>
