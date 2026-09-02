<script setup lang="ts">
import { Link, router, useHttp } from '@inertiajs/vue3';
import { onClickOutside } from '@vueuse/core';
import { Search } from 'lucide-vue-next';
import { onBeforeUnmount, ref, useTemplateRef, watch } from 'vue';
import SearchSuggestController from '@/actions/App/Http/Controllers/Shop/SearchSuggestController';
import PriceDisplay from '@/components/shop/price-display.vue';
import { Spinner } from '@/components/ui/spinner';
import { useTrans } from '@/composables/useTrans';
import * as shop from '@/routes/shop';
import type { StorefrontPrice } from '@/types/shop';

type SearchSuggestion = {
    id: number;
    name: string;
    slug: string;
    sku: string | null;
    thumbnail: string | null;
    storefront_price: StorefrontPrice | null;
};

const { t } = useTrans();
const http = useHttp();

const term = ref<string>('');
const open = ref<boolean>(false);
const loading = ref<boolean>(false);
const products = ref<SearchSuggestion[]>([]);
const root = useTemplateRef<HTMLFormElement>('root');

let debounceId: number | undefined;
let requestId = 0;

onClickOutside(root, () => {
    open.value = false;
});

watch(term, (value) => {
    window.clearTimeout(debounceId);

    const query = value.trim();

    if (query.length < 3) {
        requestId += 1;
        products.value = [];
        loading.value = false;
        open.value = false;

        return;
    }

    debounceId = window.setTimeout(() => {
        void fetchSuggestions(query);
    }, 300);
});

onBeforeUnmount(() => {
    window.clearTimeout(debounceId);
    requestId += 1;
});

async function fetchSuggestions(query: string): Promise<void> {
    const id = ++requestId;

    loading.value = true;
    open.value = true;
    products.value = [];

    try {
        const data = (await http.submit(
            SearchSuggestController.get({ query: { q: query } }),
        )) as { products: SearchSuggestion[] };

        if (id !== requestId) {
            return;
        }

        products.value = data.products;
    } catch {
        if (id !== requestId) {
            return;
        }

        products.value = [];
    } finally {
        if (id === requestId) {
            loading.value = false;
        }
    }
}

function submitSearch(): void {
    window.clearTimeout(debounceId);
    requestId += 1;
    loading.value = false;
    open.value = false;

    const query = term.value.trim();

    if (query.length === 0) {
        return;
    }

    router.get(shop.search.url(), { q: query }, { preserveState: false });
}

function closeSuggestions(): void {
    open.value = false;
}

function productHref(slug: string): string {
    return shop.product.url({ product: slug });
}
</script>

<template>
    <form
        ref="root"
        role="search"
        class="relative mx-auto hidden w-full max-w-[560px] md:block"
        @submit.prevent="submitSearch"
    >
        <input
            v-model="term"
            type="search"
            autocomplete="off"
            role="combobox"
            aria-autocomplete="list"
            aria-controls="header-search-results"
            :aria-expanded="open"
            :aria-busy="loading"
            :placeholder="t('shop.search.placeholder')"
            :aria-label="t('shop.search.aria_label')"
            class="w-full rounded-full border border-rule-strong bg-muted py-3.5 pr-14 pl-5 text-base transition placeholder:text-ink-faint focus:border-brand focus:bg-paper focus:ring-4 focus:ring-brand/12 focus:outline-none"
            @keydown.escape.prevent="closeSuggestions"
        />
        <button
            type="submit"
            class="absolute top-1/2 right-[5px] grid size-[42px] -translate-y-1/2 place-items-center rounded-full bg-primary text-paper transition hover:bg-brand-deep"
            :aria-label="t('shop.nav.search')"
        >
            <Search class="size-[18px]" aria-hidden="true" />
        </button>

        <div
            v-if="open"
            id="header-search-results"
            class="absolute top-full z-70 mt-2 w-full overflow-hidden rounded-lg border border-rule bg-paper shadow-lg"
            role="listbox"
            :aria-label="t('shop.search.heading')"
        >
            <div
                v-if="loading"
                class="flex items-center justify-center gap-2 py-8 text-sm text-ink-mute"
            >
                <Spinner class="size-[18px]" />
                <span>{{ t('shop.search.loading') }}</span>
            </div>

            <p
                v-else-if="!products.length"
                class="px-4 py-8 text-center text-sm text-ink-mute"
            >
                {{ t('shop.search.no_results') }}
            </p>

            <template v-else>
                <ul class="max-h-[min(24rem,70vh)] overflow-y-auto py-1">
                    <li
                        v-for="product in products"
                        :key="product.id"
                        role="option"
                    >
                        <Link
                            :href="productHref(product.slug)"
                            class="flex items-center gap-3 px-3 py-2.5 transition hover:bg-brand-soft"
                            @click="closeSuggestions"
                        >
                            <div
                                class="size-12 shrink-0 overflow-hidden rounded-sm bg-muted"
                            >
                                <img
                                    v-if="product.thumbnail"
                                    :src="product.thumbnail"
                                    :alt="product.name"
                                    class="size-full object-cover object-center"
                                />
                            </div>
                            <span
                                class="min-w-0 flex-1 truncate font-heading text-sm font-semibold text-ink"
                            >
                                {{ product.name }}
                            </span>
                            <div class="shrink-0 text-right">
                                <PriceDisplay
                                    :price="product.storefront_price"
                                    size="sm"
                                />
                            </div>
                        </Link>
                    </li>
                </ul>

                <Link
                    :href="shop.search.url({ query: { q: term.trim() } })"
                    class="block border-t border-rule px-4 py-3 text-center font-mono text-xs font-semibold tracking-[0.04em] text-brand transition hover:bg-brand-soft"
                    @click="closeSuggestions"
                >
                    {{ t('shop.search.view_all') }}
                </Link>
            </template>
        </div>
    </form>
</template>
