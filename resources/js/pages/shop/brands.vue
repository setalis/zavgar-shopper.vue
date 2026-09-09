<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Search } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import BrandTile from '@/components/shop/brand-tile.vue';
import Container from '@/components/shop/container.vue';
import PageHead from '@/components/shop/page-head.vue';
import ProductPagination from '@/components/shop/product-pagination.vue';
import type { PaginatorLink } from '@/components/shop/product-pagination.vue';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useLocalizedRoute } from '@/composables/useLocalizedRoute';
import { useTrans } from '@/composables/useTrans';
import { cn } from '@/lib/utils';
import { home } from '@/routes';
import * as shop from '@/routes/shop';
import type { Brand } from '@/types/shop';

type Paginated<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    total: number;
    links: PaginatorLink[];
};

type Filters = {
    q: string;
    letter: string | null;
    sort: string;
    with_products: boolean;
};

const props = defineProps<{
    brands: Paginated<Brand>;
    availableLetters: string[];
    filters: Filters;
}>();

const { t } = useTrans();
const { localized } = useLocalizedRoute();

const search = ref<string>(props.filters.q);
const sort = ref<string>(props.filters.sort);
let debounceId: number | undefined;

const crumbs = computed(() => [
    { label: t('shop.nav.home'), href: localized(home.url()) },
    { label: t('shop.brands.heading') },
]);

const letters = computed<string[]>(() => [
    ...Array.from({ length: 26 }, (_, index) =>
        String.fromCharCode(65 + index),
    ),
    '0-9',
    'other',
]);

function visit(overrides: Partial<Filters> = {}): void {
    const next = { ...props.filters, ...overrides };

    router.get(
        localized(shop.brands.url()),
        {
            q: next.q || undefined,
            letter: next.letter ?? undefined,
            sort: next.sort === 'name' ? undefined : next.sort,
            with_products: next.with_products ? 1 : undefined,
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

function letterLabel(letter: string): string {
    if (letter === '0-9') {
        return t('shop.brands.letter_digits');
    }

    if (letter === 'other') {
        return t('shop.brands.letter_other');
    }

    return letter;
}

function isLetterAvailable(letter: string): boolean {
    return props.availableLetters.includes(letter);
}

watch(search, (value) => {
    window.clearTimeout(debounceId);
    debounceId = window.setTimeout(() => {
        visit({ q: value, letter: null });
    }, 300);
});

watch(sort, (value) => {
    visit({ sort: value });
});
</script>

<template>
    <Head :title="t('shop.brands.title')" />

    <PageHead
        :title="t('shop.brands.heading')"
        :description="t('shop.brands.subtitle')"
        :crumbs="crumbs"
    >
        <div class="relative mt-6 max-w-[560px]">
            <Search
                class="pointer-events-none absolute top-1/2 left-5 size-[18px] -translate-y-1/2 text-ink-faint"
                aria-hidden="true"
            />
            <input
                v-model="search"
                type="search"
                :placeholder="t('shop.brands.search_placeholder')"
                :aria-label="t('shop.brands.search_aria')"
                class="w-full rounded-full border border-rule-strong bg-paper py-3.5 pr-5 pl-13 text-base transition placeholder:text-ink-faint focus:border-brand focus:ring-4 focus:ring-brand/12 focus:outline-none"
            />
        </div>
    </PageHead>

    <Container class="py-10 md:py-14">
        <div class="mb-6 flex flex-col gap-4">
            <div
                class="flex flex-wrap gap-1.5"
                :aria-label="t('shop.brands.letters')"
            >
                <button
                    type="button"
                    :class="
                        cn(
                            'rounded-sm px-2.5 py-1 font-mono text-xs tracking-[0.04em] transition',
                            filters.letter === null
                                ? 'bg-primary text-paper'
                                : 'bg-muted text-ink-soft hover:bg-brand-soft hover:text-brand-deep',
                        )
                    "
                    @click="visit({ letter: null })"
                >
                    {{ t('shop.brands.letter_all') }}
                </button>
                <button
                    v-for="letter in letters"
                    :key="letter"
                    type="button"
                    :disabled="!isLetterAvailable(letter)"
                    :class="
                        cn(
                            'rounded-sm px-2.5 py-1 font-mono text-xs tracking-[0.04em] transition',
                            filters.letter === letter
                                ? 'bg-primary text-paper'
                                : 'bg-muted text-ink-soft hover:bg-brand-soft hover:text-brand-deep',
                            !isLetterAvailable(letter) &&
                                'cursor-not-allowed opacity-40 hover:bg-muted hover:text-ink-soft',
                        )
                    "
                    @click="visit({ letter })"
                >
                    {{ letterLabel(letter) }}
                </button>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3">
                <span class="font-mono text-xs tracking-[0.04em] text-ink-mute">
                    {{
                        t('shop.brands.results_count', {
                            count: brands.total,
                        })
                    }}
                </span>

                <div class="flex flex-wrap items-center gap-3">
                    <Button
                        variant="outline"
                        size="sm"
                        :class="
                            cn(
                                'rounded-sm',
                                filters.with_products &&
                                    'border-brand text-brand',
                            )
                        "
                        :aria-pressed="filters.with_products"
                        @click="
                            visit({ with_products: !filters.with_products })
                        "
                    >
                        {{ t('shop.brands.with_products') }}
                    </Button>

                    <Select v-model="sort">
                        <SelectTrigger
                            class="w-auto rounded-sm"
                            :aria-label="t('shop.brands.sort_aria')"
                        >
                            <SelectValue
                                :placeholder="t('shop.brands.sort_placeholder')"
                            />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="name">
                                {{ t('shop.brands.sort.name') }}
                            </SelectItem>
                            <SelectItem value="latest">
                                {{ t('shop.brands.sort.newest') }}
                            </SelectItem>
                            <SelectItem value="products">
                                {{ t('shop.brands.sort.products') }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>
        </div>

        <div
            v-if="!brands.data.length"
            class="flex flex-col items-center justify-center rounded-lg border border-rule bg-paper py-20 text-center"
        >
            <Search class="size-10 text-ink-faint" aria-hidden="true" />
            <h3 class="mt-4 font-heading text-md font-bold text-ink">
                {{ t('shop.brands.empty') }}
            </h3>
        </div>

        <template v-else>
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
                <BrandTile
                    v-for="brand in brands.data"
                    :key="brand.id"
                    :brand="brand"
                />
            </div>

            <ProductPagination
                :links="brands.links"
                :label="t('shop.brands.pagination')"
            />
        </template>
    </Container>
</template>
