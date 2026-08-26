<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight } from 'lucide-vue-next';
import { computed } from 'vue';
import { useTrans } from '@/composables/useTrans';
import { stripHtml } from '@/lib/format';
import * as shop from '@/routes/shop';
import type { Collection } from '@/types/shop';

const props = defineProps<{ collection: Collection }>();

const { t } = useTrans();

const description = computed<string>(() =>
    stripHtml(props.collection.description),
);
</script>

<template>
    <Link
        :href="shop.collection.url({ collection: collection.slug })"
        class="group relative block overflow-hidden rounded-xl bg-linear-to-br from-card-blue to-card-blue-2"
    >
        <div class="aspect-video sm:aspect-3/2">
            <img
                v-if="collection.thumbnail"
                :src="collection.thumbnail"
                :alt="collection.name"
                loading="lazy"
                class="size-full object-cover object-center opacity-90 transition duration-500 ease-brand group-hover:scale-105"
            />
            <div
                class="absolute inset-0 bg-linear-to-t from-ink/85 via-ink/40 to-transparent"
                aria-hidden="true"
            />
        </div>

        <div class="absolute inset-x-0 bottom-0 p-7">
            <h3 class="font-heading text-lg font-bold text-paper">
                {{ collection.name }}
            </h3>
            <p
                v-if="description"
                class="mt-1 line-clamp-2 text-sm text-paper/80"
            >
                {{ description }}
            </p>
            <span
                class="mt-3 inline-flex items-center gap-2 border-b-[1.5px] border-current pb-1 text-sm font-semibold text-paper transition-all group-hover:gap-3.5"
            >
                {{ t('shop.collection.shop_now') }}
                <ArrowRight class="size-4" aria-hidden="true" />
            </span>
        </div>
    </Link>
</template>
