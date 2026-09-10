<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useLocalizedRoute } from '@/composables/useLocalizedRoute';
import { useShop } from '@/composables/useShop';
import { useTrans } from '@/composables/useTrans';
import { update as updateLocale } from '@/routes/locale';

const page = usePage();
const { locale, locales } = useTrans();
const { currency } = useShop();
const { urlForLocale } = useLocalizedRoute();

const localeCodes = computed<string[]>(() => Object.keys(locales.value));

function hrefFor(code: string): string {
    return page.props.locale_urls?.[code] ?? urlForLocale(code);
}

function usesSessionSwitch(code: string): boolean {
    return hrefFor(code) === hrefFor(locale.value) && code !== locale.value;
}

function switchSessionLocale(code: string): void {
    router.patch(updateLocale.url(), { locale: code }, { preserveScroll: true });
}
</script>

<template>
    <span
        class="inline-flex items-center gap-1.5 font-mono text-xs"
        role="group"
        :aria-label="locales[locale]"
    >
        <template v-for="(code, index) in localeCodes" :key="code">
            <span v-if="index > 0" aria-hidden="true" class="opacity-40">
                /
            </span>
            <button
                v-if="usesSessionSwitch(code)"
                type="button"
                :class="[
                    'uppercase transition',
                    code === locale
                        ? 'opacity-100'
                        : 'opacity-60 hover:opacity-100',
                ]"
                @click="switchSessionLocale(code)"
            >
                {{ code }}
            </button>
            <Link
                v-else
                :href="hrefFor(code)"
                preserve-scroll
                :class="[
                    'uppercase transition',
                    code === locale
                        ? 'opacity-100'
                        : 'opacity-60 hover:opacity-100',
                ]"
                :aria-current="code === locale ? 'true' : undefined"
            >
                {{ code }}
            </Link>
        </template>
        <span aria-hidden="true" class="opacity-40">·</span>
        <span>{{ currency }}</span>
    </span>
</template>
