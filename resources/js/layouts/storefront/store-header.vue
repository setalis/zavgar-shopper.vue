<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { Heart, Menu, Search, ShoppingBag, User } from 'lucide-vue-next';
import { ref } from 'vue';
import BrandIcon from '@/components/shop/brand-icon.vue';
import Container from '@/components/shop/container.vue';
import { useShop } from '@/composables/useShop';
import { useTrans } from '@/composables/useTrans';
import { dashboard, home, login } from '@/routes';
import * as shop from '@/routes/shop';

const emit = defineEmits<{ openMenu: [] }>();

const page = usePage();
const { cartCount } = useShop();
const { t } = useTrans();

const term = ref<string>('');

function submitSearch(): void {
    const query = term.value.trim();

    if (query.length === 0) {
        return;
    }

    router.get(shop.search.url(), { q: query }, { preserveState: false });
}
</script>

<template>
    <header
        class="sticky top-0 z-60 border-b border-rule bg-paper"
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

            <form
                role="search"
                class="relative mx-auto hidden w-full max-w-[560px] md:block"
                @submit.prevent="submitSearch"
            >
                <input
                    v-model="term"
                    type="search"
                    :placeholder="t('shop.search.placeholder')"
                    :aria-label="t('shop.search.aria_label')"
                    class="w-full rounded-full border border-rule-strong bg-muted py-3.5 pr-14 pl-5 text-base transition placeholder:text-ink-faint focus:border-brand focus:bg-paper focus:ring-4 focus:ring-brand/12 focus:outline-none"
                />
                <button
                    type="submit"
                    class="absolute top-1/2 right-[5px] grid size-[42px] -translate-y-1/2 place-items-center rounded-full bg-primary text-paper transition hover:bg-brand-deep"
                    :aria-label="t('shop.nav.search')"
                >
                    <Search class="size-[18px]" aria-hidden="true" />
                </button>
            </form>

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

                <span
                    class="hidden size-11 place-items-center rounded-full bg-muted text-ink-faint md:grid"
                    :aria-label="t('shop.nav.wishlist')"
                    :title="t('shop.nav.wishlist_soon')"
                >
                    <Heart class="size-5" aria-hidden="true" />
                </span>

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
