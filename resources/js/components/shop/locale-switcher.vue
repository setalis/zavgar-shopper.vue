<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useShop } from '@/composables/useShop';
import { useTrans } from '@/composables/useTrans';
import * as localeRoute from '@/routes/locale';

const { locale, locales } = useTrans();
const { currency } = useShop();

const localeCodes = computed<string[]>(() => Object.keys(locales.value));

function switchLocale(code: string): void {
    if (code === locale.value) {
        return;
    }

    router.patch(
        localeRoute.update.url(),
        { locale: code },
        { preserveScroll: true, preserveState: false },
    );
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
                type="button"
                :class="[
                    'uppercase transition',
                    code === locale
                        ? 'opacity-100'
                        : 'opacity-60 hover:opacity-100',
                ]"
                :aria-current="code === locale ? 'true' : undefined"
                @click="switchLocale(code)"
            >
                {{ code }}
            </button>
        </template>
        <span aria-hidden="true" class="opacity-40">·</span>
        <span>{{ currency }}</span>
    </span>
</template>
