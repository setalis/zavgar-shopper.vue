<script setup lang="ts">
import ProductReviewController from '@/actions/App/Http/Controllers/Shop/ProductReviewController';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import {
    Heart,
    MessageCircle,
    RotateCcw,
    ShieldCheck,
    Star,
    Truck,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import InputError from '@/components/input-error.vue';
import BrandLink from '@/components/shop/brand-link.vue';
import Container from '@/components/shop/container.vue';
import ProductCard from '@/components/shop/product-card.vue';
import QtyStepper from '@/components/shop/qty-stepper.vue';
import SectionHead from '@/components/shop/section-head.vue';
import StarRating from '@/components/shop/star-rating.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Progress } from '@/components/ui/progress';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Textarea } from '@/components/ui/textarea';
import { ToggleGroup, ToggleGroupItem } from '@/components/ui/toggle-group';
import { useCart } from '@/composables/useCart';
import { useFormat } from '@/composables/useFormat';
import { useShop } from '@/composables/useShop';
import { useTrans } from '@/composables/useTrans';
import { home, login } from '@/routes';
import * as shop from '@/routes/shop';
import type { Product, StorefrontPrice, VariantOptions } from '@/types/shop';

type ProductReview = {
    id: number;
    rating: number;
    title: string | null;
    content: string | null;
    is_recommended: boolean;
    created_at: string;
    author?: {
        first_name?: string | null;
        last_name?: string | null;
    } | null;
};

type ReviewForm = {
    rating: number;
    title: string;
    content: string;
    is_recommended: boolean;
};

const props = defineProps<{
    product: Product;
    variantOptions: VariantOptions | null;
    canReview: boolean;
}>();

const page = usePage();
const cart = useCart();
const { t } = useTrans();
const { currency, taxLabel } = useShop();
const { money } = useFormat();

const selectedOptions = ref<Record<number, number>>({});
const quantity = ref<number>(1);
const adding = ref<boolean>(false);
const manualImage = ref<string | null>(null);

const hasVariants = computed<boolean>(() =>
    Boolean(props.variantOptions?.hasStructuredAttributes),
);

const selectedVariantId = computed<number | null>(() => {
    if (!props.variantOptions || !hasVariants.value) {
        return null;
    }

    const required = props.variantOptions.productOptions.length;

    if (Object.keys(selectedOptions.value).length !== required) {
        return null;
    }

    const key = Object.values(selectedOptions.value)
        .sort((a, b) => a - b)
        .join('-');

    return props.variantOptions.variantIndex[key] ?? null;
});

const selectedVariant = computed(() =>
    selectedVariantId.value && props.product.variants
        ? (props.product.variants.find(
              (v) => v.id === selectedVariantId.value,
          ) ?? null)
        : null,
);

function mediaFileKey(url: string): string {
    const path = url.split('?')[0] ?? url;
    const segments = path.split('/');

    return segments[segments.length - 1] ?? path;
}

function mediaUrls(
    thumbnail: string | null | undefined,
    images: { url: string }[] | undefined,
): string[] {
    const urls: string[] = [];
    const keys = new Set<string>();

    const push = (url: string): void => {
        if (url === '') {
            return;
        }

        const key = mediaFileKey(url);

        if (keys.has(key) || urls.includes(url)) {
            return;
        }

        keys.add(key);
        urls.push(url);
    };

    if (thumbnail) {
        push(thumbnail);
    }

    for (const image of images ?? []) {
        push(image.url);
    }

    return urls;
}

const productGallery = computed<string[]>(() =>
    mediaUrls(props.product.thumbnail, props.product.images),
);

const variantGallery = computed<string[]>(() => {
    const variant = selectedVariant.value;

    if (!variant) {
        return [];
    }

    return mediaUrls(variant.thumbnail, variant.images);
});

const gallery = computed<string[]>(() =>
    variantGallery.value.length > 0
        ? variantGallery.value
        : productGallery.value,
);

const activeImage = computed<string | null>(() => {
    if (manualImage.value && gallery.value.includes(manualImage.value)) {
        return manualImage.value;
    }

    return gallery.value[0] ?? null;
});

function selectGalleryImage(url: string): void {
    manualImage.value = url;
}

function selectOption(optionId: number, valueId: string): void {
    if (valueId === '') {
        return;
    }

    manualImage.value = null;
    selectedOptions.value = {
        ...selectedOptions.value,
        [optionId]: Number(valueId),
    };
}

const displayPrice = computed<StorefrontPrice | null>(() => {
    const selected = selectedVariant.value?.prices?.[0];

    if (selected?.amount != null) {
        return {
            amount: selected.amount,
            compare_amount: selected.compare_amount ?? null,
            from: false,
        };
    }

    return props.product.storefront_price ?? null;
});

const savedAmount = computed<number | null>(() => {
    const price = displayPrice.value;

    if (!price?.compare_amount || price.compare_amount <= price.amount) {
        return null;
    }

    return price.compare_amount - price.amount;
});

const outOfStock = computed<boolean>(() => {
    if (hasVariants.value) {
        if (selectedVariant.value) {
            return (
                selectedVariant.value.stock <= 0 &&
                !selectedVariant.value.allow_backorder
            );
        }

        return (props.product.variants ?? []).every(
            (variant) => variant.stock <= 0 && !variant.allow_backorder,
        );
    }

    const stock = (props.product as { stock?: number }).stock ?? 0;

    return stock <= 0 && !props.product.allow_backorder;
});

const reviews = computed<ProductReview[]>(
    () => (props.product.reviews as ProductReview[] | undefined) ?? [],
);

const reviewForm = useForm<ReviewForm>({
    rating: 5,
    title: '',
    content: '',
    is_recommended: false,
});

const ratingStars = computed<number[]>(() => [1, 2, 3, 4, 5]);

const isAuthenticated = computed<boolean>(() => Boolean(page.props.auth.user));

const averageRating = computed<number>(() => {
    if (reviews.value.length === 0) {
        return 0;
    }

    return (
        reviews.value.reduce((total, review) => total + review.rating, 0) /
        reviews.value.length
    );
});

const ratingDistribution = computed(() =>
    [5, 4, 3, 2, 1].map((score) => {
        const matching = reviews.value.filter(
            (review) => Math.round(review.rating) === score,
        ).length;

        return {
            score,
            count: matching,
            percentage:
                reviews.value.length === 0
                    ? 0
                    : Math.round((matching / reviews.value.length) * 100),
        };
    }),
);

const specs = computed(() =>
    [
        { label: t('shop.product.specs.sku'), value: props.product.sku },
        {
            label: t('shop.product.specs.brand'),
            value: props.product.brand?.name,
        },
        {
            label: t('shop.product.specs.barcode'),
            value: props.product.barcode,
        },
        {
            label: t('shop.product.specs.weight'),
            value: props.product.weight_value
                ? `${props.product.weight_value} ${props.product.weight_unit ?? ''}`.trim()
                : null,
        },
        {
            label: t('shop.product.specs.dimensions'),
            value:
                props.product.width_value && props.product.height_value
                    ? `${props.product.width_value} × ${props.product.height_value} × ${props.product.depth_value ?? 0} ${props.product.width_unit ?? ''}`.trim()
                    : null,
        },
        {
            label: t('shop.product.specs.availability'),
            value: outOfStock.value
                ? t('shop.product.out_of_stock')
                : t('shop.product.in_stock'),
        },
    ].filter((spec) => Boolean(spec.value)),
);

const features = computed(() => [
    { icon: Truck, key: 'free_shipping' },
    { icon: ShieldCheck, key: 'secure_payment' },
    { icon: RotateCcw, key: 'easy_returns' },
    { icon: MessageCircle, key: 'support' },
]);

function addToCart(): void {
    if (adding.value || outOfStock.value) {
        return;
    }

    adding.value = true;
    cart.add({
        product_id: props.product.id,
        variant_id: selectedVariantId.value,
        quantity: quantity.value,
    });
    setTimeout(() => (adding.value = false), 800);
}

function reviewAuthorName(review: ProductReview): string {
    const author = review.author;
    const name = [author?.first_name, author?.last_name]
        .filter(Boolean)
        .join(' ');

    return name || t('shop.product.review_form.anonymous');
}

function formatReviewDate(value: string): string {
    return new Intl.DateTimeFormat(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    }).format(new Date(value));
}

function setReviewRating(rating: number): void {
    reviewForm.rating = rating;
}

function submitReview(): void {
    reviewForm.post(ProductReviewController.store.url(props.product), {
        preserveScroll: true,
        onSuccess: () => {
            reviewForm.reset();
            reviewForm.rating = 5;
            reviewForm.is_recommended = false;
        },
    });
}
</script>

<template>
    <Head :title="product.name" />

    <Container class="py-8 md:py-12">
        <nav
            class="mb-8 flex flex-wrap items-center gap-1.5 font-mono text-xs tracking-[0.04em] text-ink-mute"
            :aria-label="t('shop.product.breadcrumb')"
        >
            <Link :href="home.url()" class="transition hover:text-brand">
                {{ t('shop.nav.home') }}
            </Link>
            <span aria-hidden="true" class="text-ink-faint">/</span>
            <Link :href="shop.index.url()" class="transition hover:text-brand">
                {{ t('shop.nav.shop') }}
            </Link>
            <span aria-hidden="true" class="text-ink-faint">/</span>
            <span aria-current="page" class="text-ink">{{ product.name }}</span>
        </nav>

        <div class="grid gap-10 lg:grid-cols-2 lg:gap-14">
            <div
                :class="[
                    'grid gap-4',
                    gallery.length > 1 ? 'md:grid-cols-[80px_1fr]' : '',
                ]"
            >
                <div
                    v-if="gallery.length > 1"
                    class="order-2 grid grid-cols-4 gap-2 md:order-1 md:grid-cols-1"
                >
                    <button
                        v-for="url in gallery.slice(0, 5)"
                        :key="url"
                        type="button"
                        :class="[
                            'aspect-square overflow-hidden rounded-sm border bg-muted transition',
                            activeImage === url
                                ? 'border-brand'
                                : 'border-rule hover:border-brand-line',
                        ]"
                        :aria-current="activeImage === url ? 'true' : undefined"
                        @click="selectGalleryImage(url)"
                    >
                        <img
                            :src="url"
                            alt=""
                            class="size-full object-cover object-center"
                        />
                    </button>
                </div>

                <div
                    class="order-1 aspect-square w-full min-w-0 overflow-hidden rounded-xl border border-rule bg-muted md:order-2"
                >
                    <img
                        v-if="activeImage"
                        :src="activeImage"
                        :alt="product.name"
                        class="size-full object-cover object-center"
                    />
                </div>
            </div>

            <div>
                <p
                    v-if="product.brand"
                    class="mb-2 font-mono text-xs tracking-[0.08em] text-brand uppercase"
                >
                    <BrandLink :brand="product.brand" />
                </p>

                <h1 class="mb-3 text-2xl leading-[1.1] md:text-3xl">
                    {{ product.name }}
                </h1>

                <div
                    v-if="reviews.length"
                    class="mb-4 inline-flex items-center gap-3 text-sm text-ink-mute"
                >
                    <StarRating
                        :rating="averageRating"
                        :count="reviews.length"
                        size="md"
                    />
                </div>

                <div
                    class="mt-5 flex flex-wrap items-baseline gap-4 border-t border-rule pt-5"
                >
                    <template v-if="displayPrice">
                        <span
                            class="font-heading text-2xl font-extrabold text-ink md:text-3xl"
                        >
                            {{ money(displayPrice.amount, currency) }}
                        </span>
                        <span
                            v-if="savedAmount"
                            class="font-mono text-ink-faint line-through"
                        >
                            {{
                                money(
                                    displayPrice.compare_amount ?? 0,
                                    currency,
                                )
                            }}
                        </span>
                        <span
                            v-if="savedAmount"
                            class="rounded-[4px] bg-card-green px-2 py-1 font-mono text-xs font-semibold text-paper"
                        >
                            {{
                                t('shop.price.save', {
                                    amount: money(savedAmount, currency),
                                })
                            }}
                        </span>
                        <span
                            v-if="taxLabel"
                            class="font-mono text-[11px] text-ink-mute"
                        >
                            {{ taxLabel }}
                        </span>
                    </template>
                    <span v-else class="font-heading text-xl text-ink-mute">
                        {{ t('shop.price.unavailable') }}
                    </span>
                </div>

                <p
                    v-if="product.summary"
                    class="mb-5 text-md leading-relaxed text-ink-soft"
                >
                    {{ product.summary }}
                </p>

                <span
                    class="mb-5 inline-flex items-center gap-2 font-mono text-xs tracking-[0.04em] text-ink-mute"
                >
                    <span
                        class="size-1.5 rounded-full"
                        :class="outOfStock ? 'bg-rose' : 'bg-emerald'"
                        aria-hidden="true"
                    />
                    {{
                        outOfStock
                            ? t('shop.product.out_of_stock')
                            : t('shop.product.in_stock')
                    }}
                </span>

                <div v-if="hasVariants && variantOptions" class="space-y-5">
                    <div
                        v-for="option in variantOptions.productOptions"
                        :key="option.id"
                    >
                        <p
                            class="mb-3 font-mono text-xs tracking-[0.08em] text-ink-mute uppercase"
                        >
                            {{ option.name }}
                        </p>

                        <ToggleGroup
                            type="single"
                            :model-value="
                                selectedOptions[option.id]?.toString() ?? ''
                            "
                            class="flex-wrap justify-start gap-2"
                            :aria-label="option.name"
                            @update:model-value="
                                (value) =>
                                    selectOption(option.id, String(value ?? ''))
                            "
                        >
                            <ToggleGroupItem
                                v-for="value in option.values"
                                :key="value.id"
                                :value="value.id.toString()"
                                :disabled="
                                    !(
                                        variantOptions.availabilityMatrix[
                                            option.id
                                        ]?.[value.id] ?? true
                                    )
                                "
                                :class="
                                    option.type === 'colorpicker'
                                        ? 'size-9 rounded-full border-2 border-rule-strong p-0 data-[state=on]:border-brand data-[state=on]:ring-2 data-[state=on]:ring-brand data-[state=on]:ring-offset-2'
                                        : 'h-auto rounded-sm border border-rule-strong px-4 py-2.5 text-sm font-semibold data-[state=on]:border-brand data-[state=on]:bg-primary data-[state=on]:text-paper'
                                "
                                :style="
                                    option.type === 'colorpicker' && value.key
                                        ? { backgroundColor: value.key }
                                        : undefined
                                "
                                :title="value.value"
                            >
                                <span
                                    v-if="option.type === 'colorpicker'"
                                    class="sr-only"
                                >
                                    {{ value.value }}
                                </span>
                                <template v-else>{{ value.value }}</template>
                            </ToggleGroupItem>
                        </ToggleGroup>
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap items-center gap-3">
                    <QtyStepper
                        v-model="quantity"
                        :disabled="outOfStock"
                        :max="20"
                    />

                    <Button
                        type="button"
                        size="lg"
                        class="flex-1"
                        :disabled="
                            adding ||
                            outOfStock ||
                            (hasVariants && !selectedVariantId)
                        "
                        @click="addToCart"
                    >
                        {{
                            outOfStock
                                ? t('shop.product.out_of_stock')
                                : adding
                                  ? t('shop.product.adding')
                                  : t('shop.product.add_to_cart')
                        }}
                    </Button>

                    <Button
                        type="button"
                        variant="outline"
                        size="icon-lg"
                        :aria-label="t('shop.product.add_to_wishlist')"
                        :title="t('shop.nav.wishlist_soon')"
                    >
                        <Heart class="size-5" aria-hidden="true" />
                    </Button>
                </div>

                <div
                    class="mt-10 grid gap-3 border-t border-rule pt-5 sm:grid-cols-2"
                >
                    <div
                        v-for="feature in features"
                        :key="feature.key"
                        class="flex items-center gap-3 text-sm text-ink-soft"
                    >
                        <span
                            class="grid size-8 shrink-0 place-items-center rounded-full bg-brand-soft text-brand"
                        >
                            <component
                                :is="feature.icon"
                                class="size-4"
                                aria-hidden="true"
                            />
                        </span>
                        {{ t(`shop.trust.${feature.key}.title`) }}
                    </div>
                </div>
            </div>
        </div>

        <Tabs default-value="specs" class="mt-14 md:mt-20">
            <TabsList class="w-full justify-start rounded-sm">
                <TabsTrigger value="specs">
                    {{ t('shop.product.tabs.specs') }}
                </TabsTrigger>
                <TabsTrigger value="description">
                    {{ t('shop.product.tabs.description') }}
                </TabsTrigger>
                <TabsTrigger value="reviews">
                    {{ t('shop.product.tabs.reviews') }}
                </TabsTrigger>
            </TabsList>

            <TabsContent value="specs" class="pt-7">
                <dl
                    class="overflow-hidden rounded-lg border border-rule bg-paper"
                >
                    <div
                        v-for="(spec, index) in specs"
                        :key="spec.label"
                        :class="[
                            'grid gap-1 px-5 py-3.5 sm:grid-cols-[220px_1fr]',
                            index % 2 === 1 && 'bg-muted',
                        ]"
                    >
                        <dt
                            class="font-mono text-xs tracking-[0.08em] text-ink-mute uppercase"
                        >
                            {{ spec.label }}
                        </dt>
                        <dd class="text-sm text-ink">{{ spec.value }}</dd>
                    </div>
                </dl>
            </TabsContent>

            <TabsContent value="description" class="pt-7">
                <div
                    v-if="product.description"
                    class="prose prose-sm max-w-none prose-zinc"
                    v-html="product.description"
                />
                <p v-else class="text-sm text-ink-mute">
                    {{ t('shop.product.no_description') }}
                </p>
            </TabsContent>

            <TabsContent value="reviews" class="pt-7">
                <div class="space-y-10">
                    <div class="grid gap-7 lg:grid-cols-[240px_1fr]">
                        <div
                            class="rounded-lg border border-rule bg-paper p-5 text-center"
                        >
                            <p
                                class="font-heading text-3xl font-extrabold text-ink"
                            >
                                {{ averageRating.toFixed(1) }}
                            </p>
                            <StarRating
                                class="mt-2 justify-center"
                                :rating="averageRating"
                                size="md"
                            />
                            <p class="mt-2 font-mono text-xs text-ink-mute">
                                {{
                                    t('shop.product.review_count', {
                                        count: reviews.length,
                                    })
                                }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <div
                                v-for="bucket in ratingDistribution"
                                :key="bucket.score"
                                class="flex items-center gap-3"
                            >
                                <span
                                    class="w-10 font-mono text-xs text-ink-mute"
                                >
                                    {{ bucket.score }}★
                                </span>
                                <Progress
                                    :model-value="bucket.percentage"
                                    class="h-1.5 flex-1"
                                />
                                <span
                                    class="w-10 text-right font-mono text-xs text-ink-mute"
                                >
                                    {{ bucket.count }}
                                </span>
                            </div>

                            <p
                                v-if="!reviews.length"
                                class="pt-4 text-sm text-ink-mute"
                            >
                                {{ t('shop.product.no_reviews') }}
                            </p>
                        </div>
                    </div>

                    <div class="border-t border-rule pt-7">
                        <p
                            v-if="!isAuthenticated"
                            class="text-sm text-ink-soft"
                        >
                            {{ t('shop.product.review_form.sign_in') }}
                            <Link
                                :href="login.url()"
                                class="font-semibold text-brand transition hover:text-brand-dark"
                            >
                                {{ t('shop.nav.sign_in') }}
                            </Link>
                        </p>

                        <p
                            v-else-if="!canReview"
                            class="text-sm text-ink-soft"
                        >
                            {{
                                t(
                                    'shop.product.review_form.already_submitted',
                                )
                            }}
                        </p>

                        <form
                            v-else
                            class="max-w-2xl space-y-5"
                            @submit.prevent="submitReview"
                        >
                            <h3 class="font-heading text-lg font-bold text-ink">
                                {{ t('shop.product.review_form.title') }}
                            </h3>

                            <div class="space-y-2">
                                <Label>{{
                                    t('shop.product.review_form.rating')
                                }}</Label>
                                <div
                                    class="flex items-center gap-1"
                                    role="radiogroup"
                                    :aria-label="
                                        t('shop.product.review_form.rating')
                                    "
                                >
                                    <button
                                        v-for="star in ratingStars"
                                        :key="star"
                                        type="button"
                                        class="rounded-sm p-0.5 text-amber transition hover:scale-110"
                                        :aria-label="
                                            t(
                                                'shop.product.review_form.rating_sr',
                                                { rating: star },
                                            )
                                        "
                                        :aria-checked="
                                            reviewForm.rating === star
                                        "
                                        role="radio"
                                        @click="setReviewRating(star)"
                                    >
                                        <Star
                                            :class="[
                                                'size-6',
                                                star <= reviewForm.rating
                                                    ? 'fill-current'
                                                    : 'text-rule-strong',
                                            ]"
                                            aria-hidden="true"
                                        />
                                    </button>
                                </div>
                                <InputError :message="reviewForm.errors.rating" />
                            </div>

                            <div class="space-y-2">
                                <Label for="review-title">{{
                                    t('shop.product.review_form.title_label')
                                }}</Label>
                                <Input
                                    id="review-title"
                                    v-model="reviewForm.title"
                                    type="text"
                                    autocomplete="off"
                                />
                                <InputError :message="reviewForm.errors.title" />
                            </div>

                            <div class="space-y-2">
                                <Label for="review-content">{{
                                    t('shop.product.review_form.content_label')
                                }}</Label>
                                <Textarea
                                    id="review-content"
                                    v-model="reviewForm.content"
                                    rows="5"
                                />
                                <InputError
                                    :message="reviewForm.errors.content"
                                />
                            </div>

                            <div class="flex items-center gap-2">
                                <Checkbox
                                    id="review-recommended"
                                    :checked="reviewForm.is_recommended"
                                    @update:checked="
                                        (value) =>
                                            (reviewForm.is_recommended =
                                                value === true)
                                    "
                                />
                                <Label
                                    for="review-recommended"
                                    class="cursor-pointer font-normal"
                                >
                                    {{
                                        t('shop.product.review_form.recommend')
                                    }}
                                </Label>
                            </div>

                            <InputError :message="reviewForm.errors.review" />

                            <Button
                                type="submit"
                                :disabled="reviewForm.processing"
                            >
                                {{ t('shop.product.review_form.submit') }}
                            </Button>
                        </form>
                    </div>

                    <div
                        v-if="reviews.length"
                        class="space-y-5 border-t border-rule pt-7"
                    >
                        <article
                            v-for="review in reviews"
                            :key="review.id"
                            class="rounded-lg border border-rule bg-paper p-5"
                        >
                            <div
                                class="flex flex-wrap items-start justify-between gap-3"
                            >
                                <div>
                                    <p class="font-semibold text-ink">
                                        {{ reviewAuthorName(review) }}
                                    </p>
                                    <p class="font-mono text-xs text-ink-mute">
                                        {{ formatReviewDate(review.created_at) }}
                                    </p>
                                </div>

                                <div class="flex flex-wrap items-center gap-2">
                                    <StarRating
                                        :rating="review.rating"
                                        size="md"
                                    />
                                    <span
                                        v-if="review.is_recommended"
                                        class="rounded-[4px] bg-brand-soft px-2 py-1 font-mono text-[11px] font-semibold text-brand"
                                    >
                                        {{
                                            t(
                                                'shop.product.review_form.recommended_badge',
                                            )
                                        }}
                                    </span>
                                </div>
                            </div>

                            <h4
                                v-if="review.title"
                                class="mt-4 font-heading text-md font-bold text-ink"
                            >
                                {{ review.title }}
                            </h4>

                            <p
                                v-if="review.content"
                                class="mt-2 text-sm leading-relaxed text-ink-soft"
                            >
                                {{ review.content }}
                            </p>
                        </article>
                    </div>
                </div>
            </TabsContent>
        </Tabs>

        <section
            v-if="product.related_products?.length"
            class="mt-14 border-t border-rule pt-14 md:mt-20 md:pt-20"
        >
            <SectionHead
                :title="t('shop.product.related')"
                :view-all-href="shop.index.url()"
                :view-all-label="t('shop.home.featured.view_all')"
            />

            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                <ProductCard
                    v-for="related in product.related_products"
                    :key="related.id"
                    :product="related"
                />
            </div>
        </section>
    </Container>
</template>
