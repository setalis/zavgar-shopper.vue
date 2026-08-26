<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight } from 'lucide-vue-next';
import Container from '@/components/shop/container.vue';
import { Button } from '@/components/ui/button';
import { useTrans } from '@/composables/useTrans';
import * as shop from '@/routes/shop';

const { t } = useTrans();

/**
 * Sprylo's bento hero ships fixed editorial copy and photography; the shop has
 * no campaign entity to drive it, so the cards stay presentational and link
 * into the catalogue.
 */
const smallCards = [
    {
        key: 'orange',
        surface: 'bg-linear-to-br from-card-orange to-card-orange-2',
        image: 'https://images.unsplash.com/photo-1592899677977-9c10ca588bbd?w=420&q=80&auto=format&fit=crop',
        imageClass: 'w-[55%] max-w-[180px] right-0 bottom-0',
    },
    {
        key: 'green',
        surface: 'bg-linear-to-br from-card-green to-card-green-2',
        image: 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=420&q=80&auto=format&fit=crop',
        imageClass: 'w-[56%] max-w-[200px] right-0 bottom-0',
    },
    {
        key: 'black',
        surface: 'bg-linear-to-br from-card-black to-card-black-2',
        image: 'https://images.unsplash.com/photo-1546868871-7041f2a55e12?w=420&q=80&auto=format&fit=crop',
        imageClass: 'w-1/2 max-w-[180px] right-0 bottom-0',
    },
] as const;
</script>

<template>
    <section class="bg-paper pt-10 pb-14">
        <Container>
            <div
                class="grid gap-4 lg:grid-cols-[2.2fr_1fr] lg:grid-rows-[1fr_1fr_auto]"
            >
                <article
                    class="bento-surface relative flex min-h-[420px] flex-col justify-between rounded-xl bg-linear-to-br from-card-blue to-card-blue-2 p-8 text-paper transition duration-300 ease-brand hover:-translate-y-1 hover:shadow-lg lg:col-start-1 lg:row-span-2 lg:min-h-[540px] lg:p-14"
                >
                    <div class="relative z-2 lg:max-w-[50%]">
                        <span
                            class="inline-flex items-center gap-2 font-mono text-[11px] tracking-[0.14em] uppercase opacity-82"
                        >
                            {{ t('shop.hero.lead.eyebrow') }}
                        </span>
                        <h2
                            class="my-4 max-w-[14ch] text-[clamp(1.75rem,1rem+2vw,2.75rem)] leading-[1.04] font-extrabold tracking-[-0.025em]"
                        >
                            {{ t('shop.hero.lead.title') }}
                        </h2>
                        <p
                            class="mb-5 max-w-[32ch] text-sm leading-relaxed opacity-86"
                        >
                            {{ t('shop.hero.lead.description') }}
                        </p>
                        <Button as-child variant="paper">
                            <Link :href="shop.index.url()">
                                {{ t('shop.hero.lead.cta') }}
                                <ArrowRight class="size-4" aria-hidden="true" />
                            </Link>
                        </Button>

                        <div
                            class="mt-4 inline-flex gap-1.5"
                            aria-hidden="true"
                        >
                            <span class="h-1 w-7 rounded-full bg-paper" />
                            <span
                                class="h-1 w-[22px] rounded-full bg-paper/30"
                            />
                            <span
                                class="h-1 w-[22px] rounded-full bg-paper/30"
                            />
                        </div>
                    </div>

                    <img
                        src="https://images.unsplash.com/photo-1610945415295-d9bbf067e59c?w=900&q=80&auto=format&fit=crop"
                        alt=""
                        class="pointer-events-none absolute -right-8 -bottom-5 w-1/2 max-w-[440px] rounded-xl drop-shadow-2xl"
                    />
                </article>

                <article
                    class="bento-surface relative flex min-h-[260px] flex-col justify-between rounded-xl bg-linear-to-br from-card-purple to-card-purple-2 p-8 text-paper transition duration-300 ease-brand hover:-translate-y-1 hover:shadow-lg lg:col-start-2 lg:row-start-1"
                >
                    <span
                        class="relative z-2 inline-flex items-center gap-2 font-mono text-[11px] tracking-[0.14em] uppercase opacity-82"
                    >
                        {{ t('shop.hero.sidekick.eyebrow') }}
                    </span>
                    <h3 class="relative z-2 my-3 max-w-[16ch] text-xl">
                        {{ t('shop.hero.sidekick.title') }}
                    </h3>
                    <Link
                        :href="shop.categories.url()"
                        class="relative z-2 inline-flex items-center gap-2 self-start border-b-[1.5px] border-current pb-1 text-sm font-semibold transition-all hover:gap-3.5"
                    >
                        {{ t('shop.hero.shop_now') }}
                        <ArrowRight class="size-4" aria-hidden="true" />
                    </Link>

                    <img
                        src="https://images.unsplash.com/photo-1585123334904-845d60e97b29?w=420&q=80&auto=format&fit=crop"
                        alt=""
                        class="pointer-events-none absolute -right-2.5 -bottom-2.5 w-[60%] max-w-[200px] rounded-lg drop-shadow-xl"
                    />
                </article>

                <article
                    class="bento-surface relative flex min-h-[260px] flex-col justify-between rounded-xl bg-linear-to-br from-card-teal to-card-teal-2 p-8 text-paper transition duration-300 ease-brand hover:-translate-y-1 hover:shadow-lg lg:col-start-2 lg:row-start-2"
                >
                    <span
                        class="relative z-2 inline-flex items-center gap-2 font-mono text-[11px] tracking-[0.14em] uppercase opacity-82"
                    >
                        {{ t('shop.hero.support.eyebrow') }}
                    </span>
                    <h3 class="relative z-2 my-3 max-w-[16ch] text-xl">
                        {{ t('shop.hero.support.title') }}
                    </h3>
                    <Link
                        :href="shop.contact.url()"
                        class="relative z-2 inline-flex items-center gap-2 self-start border-b-[1.5px] border-current pb-1 text-sm font-semibold transition-all hover:gap-3.5"
                    >
                        {{ t('shop.hero.support.cta') }}
                        <ArrowRight class="size-4" aria-hidden="true" />
                    </Link>

                    <img
                        src="https://images.unsplash.com/photo-1484704849700-f032a568e944?w=420&q=80&auto=format&fit=crop"
                        alt=""
                        class="pointer-events-none absolute -right-2.5 -bottom-2.5 w-[62%] max-w-[220px] rounded-lg drop-shadow-xl"
                    />
                </article>

                <div
                    class="grid gap-4 sm:grid-cols-3 lg:col-span-2 lg:row-start-3"
                >
                    <article
                        v-for="card in smallCards"
                        :key="card.key"
                        :class="[
                            'bento-surface relative flex min-h-[240px] flex-col justify-between overflow-hidden rounded-xl p-7 text-paper transition duration-300 ease-brand hover:-translate-y-1 hover:shadow-lg',
                            card.surface,
                        ]"
                    >
                        <span
                            class="relative z-2 inline-flex items-center gap-2 font-mono text-[11px] tracking-[0.14em] uppercase opacity-82"
                        >
                            {{ t(`shop.hero.${card.key}.eyebrow`) }}
                        </span>
                        <h3 class="relative z-2 my-3 max-w-[14ch] text-lg">
                            {{ t(`shop.hero.${card.key}.title`) }}
                        </h3>
                        <Link
                            :href="shop.index.url()"
                            class="relative z-2 inline-flex items-center gap-2 self-start border-b-[1.5px] border-current pb-1 text-sm font-semibold transition-all hover:gap-3.5"
                        >
                            {{ t('shop.hero.shop_now') }}
                            <ArrowRight class="size-4" aria-hidden="true" />
                        </Link>

                        <img
                            :src="card.image"
                            alt=""
                            :class="[
                                'pointer-events-none absolute rounded-lg drop-shadow-xl',
                                card.imageClass,
                            ]"
                        />
                    </article>
                </div>
            </div>
        </Container>
    </section>
</template>
