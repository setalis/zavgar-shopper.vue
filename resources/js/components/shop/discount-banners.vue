<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight } from 'lucide-vue-next';
import Container from '@/components/shop/container.vue';
import { useTrans } from '@/composables/useTrans';
import * as shop from '@/routes/shop';

const { t } = useTrans();

/**
 * Presentational promo pair from the template — the shop has no campaign model
 * driving discounts, so copy is fixed and both cards link into the catalogue.
 */
const banners = [
    {
        key: 'watch',
        surface: 'bg-linear-to-br from-ink to-[#1e293b]',
        image: 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=520&q=80&auto=format&fit=crop',
    },
    {
        key: 'audio',
        surface: 'bg-linear-to-br from-card-purple to-card-purple-2',
        image: 'https://images.unsplash.com/photo-1606220945770-b5b6c2c55bf1?w=520&q=80&auto=format&fit=crop',
    },
] as const;
</script>

<template>
    <Container>
        <div class="grid gap-4 md:grid-cols-2">
            <article
                v-for="banner in banners"
                :key="banner.key"
                :class="[
                    'relative flex min-h-[260px] flex-col justify-center overflow-hidden rounded-xl p-10 text-paper',
                    banner.surface,
                ]"
            >
                <p class="mb-5 font-mono text-xs tracking-[0.08em] opacity-70">
                    {{ t(`shop.discount.${banner.key}.meta`) }}
                </p>
                <h3 class="mb-2 max-w-[18ch] text-xl leading-[1.1] md:text-2xl">
                    {{ t(`shop.discount.${banner.key}.title`) }}
                    <span class="block text-3xl font-extrabold text-amber">
                        {{ t(`shop.discount.${banner.key}.percentage`) }}
                    </span>
                </h3>
                <Link
                    :href="shop.index.url()"
                    class="inline-flex items-center gap-2 self-start border-b-[1.5px] border-current pb-1 text-sm font-semibold transition-all hover:gap-3.5"
                >
                    {{ t('shop.hero.shop_now') }}
                    <ArrowRight class="size-4" aria-hidden="true" />
                </Link>

                <img
                    :src="banner.image"
                    alt=""
                    class="pointer-events-none absolute -right-2.5 -bottom-2.5 w-1/2 max-w-[240px] rounded-lg drop-shadow-xl"
                />
            </article>
        </div>
    </Container>
</template>
