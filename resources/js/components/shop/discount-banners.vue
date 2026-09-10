<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight } from 'lucide-vue-next';
import Container from '@/components/shop/container.vue';
import type { HomepageBanner, HomepageBannerTint } from '@/types/shop';

const props = defineProps<{
    banners: HomepageBanner[];
}>();

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

function accentBackdropStyle(
    tint: HomepageBannerTint | null,
): Record<string, string> {
    if (!tint) {
        return {};
    }

    return {
        backgroundImage: `radial-gradient(circle at 50% 70%, ${tint.from}, ${tint.to})`,
    };
}
</script>

<template>
    <Container v-if="props.banners.length">
        <div class="grid gap-4 md:grid-cols-2">
            <article
                v-for="banner in props.banners"
                :key="banner.id"
                :class="[
                    'relative flex min-h-[260px] flex-col justify-center overflow-hidden rounded-xl p-10 text-paper',
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

                <p
                    v-if="banner.eyebrow"
                    class="relative z-2 mb-5 font-mono text-xs tracking-[0.08em] opacity-70"
                >
                    {{ banner.eyebrow }}
                </p>
                <h3
                    class="relative z-2 mb-2 max-w-[18ch] text-xl leading-[1.1] md:text-2xl"
                >
                    {{ banner.title }}
                    <span
                        v-if="banner.highlight"
                        class="block text-3xl font-extrabold text-amber"
                    >
                        {{ banner.highlight }}
                    </span>
                </h3>
                <Link
                    v-if="banner.href && banner.button_text"
                    :href="banner.href"
                    class="relative z-2 inline-flex items-center gap-2 self-start border-b-[1.5px] border-current pb-1 text-sm font-semibold transition-all hover:gap-3.5"
                >
                    {{ banner.button_text }}
                    <ArrowRight class="size-4" aria-hidden="true" />
                </Link>

                <div
                    v-if="banner.accent_image"
                    class="pointer-events-none absolute -right-2.5 -bottom-2.5 z-3 w-1/2 max-w-[240px] rounded-lg drop-shadow-xl"
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
</template>
