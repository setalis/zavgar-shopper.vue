<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { useFormat } from '@/composables/useFormat';
import { useShop } from '@/composables/useShop';
import { useTrans } from '@/composables/useTrans';
import * as shop from '@/routes/shop';
import type { Product } from '@/types/shop';

const props = defineProps<{ product: Product }>();

const { t } = useTrans();
const { currency } = useShop();
const { money } = useFormat();

const href = computed<string>(() =>
    shop.product.url({ product: props.product.slug }),
);

const thumbnail = computed<string | null>(
    () => props.product.thumbnail ?? props.product.images?.[0]?.url ?? null,
);

const price = computed(() => props.product.storefront_price ?? null);
</script>

<template>
    <article
        class="grid grid-cols-[76px_1fr] items-center gap-4 rounded-lg border border-rule bg-paper p-4 transition hover:border-brand-line"
    >
        <div class="size-[76px] overflow-hidden rounded-sm bg-muted">
            <img
                v-if="thumbnail"
                :src="thumbnail"
                :alt="product.name"
                loading="lazy"
                class="size-full object-cover object-center"
            />
        </div>

        <div class="min-w-0">
            <span
                class="mb-1 inline-flex items-center gap-1 font-mono text-[10px] text-ink-mute"
            >
                {{ t('shop.product.just_for_you') }}
            </span>
            <h3
                class="mb-1 truncate font-heading text-sm leading-tight font-semibold text-ink"
            >
                <Link :href="href" class="transition hover:text-brand">
                    {{ product.name }}
                </Link>
            </h3>
            <p
                v-if="price"
                class="mb-1.5 font-heading text-sm font-bold text-ink"
            >
                {{ money(price.amount, currency) }}
            </p>

            <Button as-child size="sm" block class="rounded-sm text-[11px]">
                <Link :href="href">{{ t('shop.product.view') }}</Link>
            </Button>
        </div>
    </article>
</template>
