<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Tag } from 'lucide-vue-next';
import { computed } from 'vue';
import { useLocalizedRoute } from '@/composables/useLocalizedRoute';
import { useTrans } from '@/composables/useTrans';
import * as shop from '@/routes/shop';
import type { Brand } from '@/types/shop';

const props = defineProps<{
    brand: Brand;
}>();

const { t } = useTrans();
const { localized } = useLocalizedRoute();

const brandHref = computed<string>(() =>
    localized(shop.brand.url({ brand: props.brand.slug ?? '' })),
);

function productLabel(count: number): string {
    return t('shop.brands.product_count', {
        count,
        label:
            count === 1
                ? t('shop.brands.product')
                : t('shop.brands.products'),
    });
}
</script>

<template>
    <Link
        :href="brandHref"
        class="group flex flex-col rounded-lg border border-rule bg-paper p-5 text-center transition duration-200 ease-brand hover:-translate-y-[3px] hover:border-brand-line hover:bg-brand-soft"
    >
        <div
            class="mx-auto mb-3 grid h-16 w-full max-w-36 place-items-center overflow-hidden rounded-md bg-muted"
        >
            <img
                v-if="brand.thumbnail"
                :src="brand.thumbnail"
                :alt="brand.name"
                loading="lazy"
                class="max-h-14 max-w-full object-contain"
            />
            <Tag v-else class="size-7 text-ink-faint" aria-hidden="true" />
        </div>

        <span class="block font-heading text-sm font-bold text-ink">
            {{ brand.name }}
        </span>
        <span
            v-if="brand.products_count !== undefined"
            class="mt-1 block font-mono text-[11px] text-ink-mute"
        >
            {{ productLabel(brand.products_count) }}
        </span>
    </Link>
</template>
