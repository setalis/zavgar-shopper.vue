<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight } from 'lucide-vue-next';
import Container from '@/components/shop/container.vue';
import { Button } from '@/components/ui/button';
import type { HomepageBanner, HomepageBannerTint } from '@/types/shop';

const props = defineProps<{
    banners: HomepageBanner[];
}>();

const sizeClasses: Record<HomepageBanner['size'], string> = {
    large: 'min-h-[420px] p-8 lg:col-span-4 lg:row-span-2 lg:min-h-[540px] lg:p-14',
    medium: 'min-h-[260px] p-8 lg:col-span-2',
    small: 'min-h-[240px] p-7 lg:col-span-2',
};

const accentClasses: Record<HomepageBanner['size'], string> = {
    large: '-right-6 -bottom-5 w-1/2 max-w-[440px] rounded-xl drop-shadow-2xl',
    medium: '-right-2.5 -bottom-2.5 w-[60%] max-w-[200px] rounded-lg drop-shadow-xl',
    small: 'right-0 bottom-0 w-[55%] max-w-[180px] rounded-lg drop-shadow-xl',
};

function hasBackgroundImage(banner: HomepageBanner): boolean {
    return banner.background_type === 'image' && Boolean(banner.background_image);
}

function surfaceClass(banner: HomepageBanner): string {
    if (banner.gradient && !hasBackgroundImage(banner)) {
        return '';
    }

    if (hasBackgroundImage(banner)) {
        return '';
    }

    return 'bg-ink';
}

function fillStyle(banner: HomepageBanner): Record<string, string> {
    if (!banner.gradient || hasBackgroundImage(banner)) {
        return {};
    }

    return {
        backgroundImage: `linear-gradient(to bottom right, ${banner.gradient.from}, ${banner.gradient.to})`,
    };
}

function overlayStyle(tint: HomepageBannerTint | null): Record<string, string> {
    if (!tint) {
        return {};
    }

    return {
        backgroundImage: `linear-gradient(to right, ${tint.from} 12%, color-mix(in oklch, ${tint.from} 80%, transparent) 45%, color-mix(in oklch, ${tint.from} 20%, transparent))`,
    };
}

function accentBackdropStyle(tint: HomepageBannerTint | null): Record<string, string> {
    if (!tint) {
        return {};
    }

    return {
        backgroundImage: `radial-gradient(circle at 50% 70%, ${tint.from}, ${tint.to})`,
    };
}
</script>

<template>
    <section v-if="props.banners.length" class="bg-paper pt-6 pb-14">
        <Container>
            <div class="grid gap-4 lg:grid-cols-6">
                <article
                    v-for="banner in props.banners"
                    :key="banner.id"
                    :class="[
                        'bento-surface relative flex flex-col justify-between overflow-hidden rounded-xl text-paper transition duration-300 ease-brand hover:-translate-y-1 hover:shadow-lg',
                        sizeClasses[banner.size],
                        surfaceClass(banner),
                    ]"
                    :style="fillStyle(banner)"
                >
                    <img
                        v-if="hasBackgroundImage(banner)"
                        :src="banner.background_image ?? undefined"
                        alt=""
                        class="pointer-events-none absolute inset-0 z-0 size-full object-cover object-[72%_center]"
                    />
                    <div
                        v-if="hasBackgroundImage(banner) && banner.gradient"
                        class="pointer-events-none absolute inset-0 z-1"
                        :style="overlayStyle(banner.gradient)"
                        aria-hidden="true"
                    />

                    <div
                        :class="[
                            'relative z-2',
                            banner.size === 'large' ? 'lg:max-w-[50%]' : '',
                        ]"
                    >
                        <span
                            v-if="banner.eyebrow"
                            class="inline-flex items-center gap-2 font-mono text-[11px] tracking-[0.14em] uppercase opacity-82"
                        >
                            {{ banner.eyebrow }}
                        </span>
                        <component
                            :is="banner.size === 'large' ? 'h2' : 'h3'"
                            :class="[
                                'my-3 leading-[1.04] font-extrabold tracking-[-0.025em]',
                                banner.size === 'large'
                                    ? 'max-w-[14ch] text-[clamp(1.75rem,1rem+2vw,2.75rem)]'
                                    : banner.size === 'medium'
                                      ? 'max-w-[16ch] text-xl'
                                      : 'max-w-[14ch] text-lg',
                            ]"
                        >
                            {{ banner.title }}
                        </component>
                        <p
                            v-if="
                                banner.size === 'large' && banner.description
                            "
                            class="mb-5 max-w-[32ch] text-sm leading-relaxed opacity-86"
                        >
                            {{ banner.description }}
                        </p>
                    </div>

                    <Button
                        v-if="
                            banner.size === 'large' &&
                            banner.href &&
                            banner.button_text
                        "
                        as-child
                        variant="paper"
                        class="relative z-2 self-start"
                    >
                        <Link :href="banner.href">
                            {{ banner.button_text }}
                            <ArrowRight class="size-4" aria-hidden="true" />
                        </Link>
                    </Button>
                    <Link
                        v-else-if="banner.href && banner.button_text"
                        :href="banner.href"
                        class="relative z-2 inline-flex items-center gap-2 self-start border-b-[1.5px] border-current pb-1 text-sm font-semibold transition-all hover:gap-3.5"
                    >
                        {{ banner.button_text }}
                        <ArrowRight class="size-4" aria-hidden="true" />
                    </Link>

                    <div
                        v-if="banner.accent_image"
                        :class="[
                            'pointer-events-none absolute z-3',
                            accentClasses[banner.size],
                        ]"
                    >
                        <div
                            v-if="banner.overlay_gradient"
                            class="absolute inset-0"
                            :style="accentBackdropStyle(banner.overlay_gradient)"
                            aria-hidden="true"
                        />
                        <img
                            :src="banner.accent_image"
                            alt=""
                            class="relative z-1 size-full object-contain"
                        />
                    </div>
                </article>
            </div>
        </Container>
    </section>
</template>
