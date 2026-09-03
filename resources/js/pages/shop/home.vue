<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import BentoHero from '@/components/shop/bento-hero.vue';
import BrandStrip from '@/components/shop/brand-strip.vue';
import CategoryTile from '@/components/shop/category-tile.vue';
import CollectionBanner from '@/components/shop/collection-banner.vue';
import CompactProductCard from '@/components/shop/compact-product-card.vue';
import Container from '@/components/shop/container.vue';
import DiscountBanners from '@/components/shop/discount-banners.vue';
import NewsletterBanner from '@/components/shop/newsletter-banner.vue';
import ProductCard from '@/components/shop/product-card.vue';
import SectionHead from '@/components/shop/section-head.vue';
import TrustBadges from '@/components/shop/trust-badges.vue';
import { useTrans } from '@/composables/useTrans';
import * as shop from '@/routes/shop';
import type { Category, Collection, HomepageBanner, Product } from '@/types/shop';

const props = defineProps<{
    bentoBanners: HomepageBanner[];
    featuredProducts: Product[];
    latestProducts: Product[];
    featuredCollections: Collection[];
    categories: Category[];
}>();

const { t } = useTrans();

type TrendingTab = 'featured' | 'latest';

const activeTab = ref<TrendingTab>(
    props.featuredProducts.length > 0 ? 'featured' : 'latest',
);

const tabs = computed(() =>
    (
        [
            { value: 'featured', products: props.featuredProducts },
            { value: 'latest', products: props.latestProducts },
        ] as const
    ).filter((tab) => tab.products.length > 0),
);

const visibleProducts = computed<Product[]>(() =>
    (activeTab.value === 'featured'
        ? props.featuredProducts
        : props.latestProducts
    ).slice(0, 10),
);

const compactProducts = computed<Product[]>(() =>
    props.latestProducts.slice(0, 4),
);
</script>

<template>
    <Head :title="t('shop.home.title')" />

    <BentoHero :banners="bentoBanners" />

    <section v-if="tabs.length" class="py-3 md:pb-20 md:pb-0">
        <Container>
            <SectionHead
                :title="t('shop.home.trending.title')"
                :view-all-href="shop.index.url()"
                :view-all-label="t('shop.home.featured.view_all')"
            />

            <div
                v-if="tabs.length > 1"
                class="mb-7 flex gap-7 border-b border-rule"
                role="tablist"
                :aria-label="t('shop.home.trending.title')"
            >
                <button
                    v-for="tab in tabs"
                    :key="tab.value"
                    type="button"
                    role="tab"
                    :aria-selected="activeTab === tab.value"
                    :class="[
                        'relative py-3 text-sm font-semibold transition',
                        activeTab === tab.value
                            ? 'text-brand after:absolute after:inset-x-0 after:-bottom-px after:h-0.5 after:rounded-sm after:bg-brand'
                            : 'text-ink-mute hover:text-ink',
                    ]"
                    @click="activeTab = tab.value"
                >
                    {{ t(`shop.home.trending.tabs.${tab.value}`) }}
                </button>
            </div>

            <div
                class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5"
            >
                <ProductCard
                    v-for="product in visibleProducts"
                    :key="product.id"
                    :product="product"
                />
            </div>
        </Container>
    </section>

    <DiscountBanners />

    <section v-if="categories.length" class="py-14 md:py-20">
        <Container>
            <SectionHead
                :title="t('shop.home.categories.title')"
                :description="t('shop.home.categories.subtitle')"
                :view-all-href="shop.categories.url()"
                :view-all-label="t('shop.home.categories.view_all')"
            />

            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
                <CategoryTile
                    v-for="category in categories"
                    :key="category.id"
                    :category="category"
                />
            </div>
        </Container>
    </section>

    <section v-if="featuredCollections.length" class="pb-14 md:pb-20">
        <Container>
            <SectionHead :title="t('shop.home.collections.title')" />

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <CollectionBanner
                    v-for="collection in featuredCollections"
                    :key="collection.id"
                    :collection="collection"
                />
            </div>
        </Container>
    </section>

    <section v-if="compactProducts.length" class="pb-14 md:pb-20">
        <Container>
            <SectionHead
                :title="t('shop.home.just_for_you.title')"
                :view-all-href="shop.index.url()"
                :view-all-label="t('shop.home.featured.view_all')"
            />

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <CompactProductCard
                    v-for="product in compactProducts"
                    :key="product.id"
                    :product="product"
                />
            </div>
        </Container>
    </section>

    <BrandStrip />

    <NewsletterBanner />

    <section class="pb-14 md:pb-20">
        <Container>
            <TrustBadges />
        </Container>
    </section>
</template>
