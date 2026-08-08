<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useTrans } from '@/composables/useTrans';
import * as localeRoute from '@/routes/locale';

const { locale, locales } = useTrans();

const localeCodes = computed(() => Object.keys(locales.value));

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
    <div
        class="flex items-center rounded-md border border-zinc-200 p-0.5 dark:border-zinc-700"
        role="group"
        :aria-label="locales[locale]"
    >
        <button
            v-for="code in localeCodes"
            :key="code"
            type="button"
            :class="[
                'rounded px-2 py-1 text-xs font-medium uppercase transition',
                code === locale
                    ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900'
                    : 'text-zinc-500 hover:text-zinc-900 dark:hover:text-white',
            ]"
            @click="switchLocale(code)"
        >
            {{ code }}
        </button>
    </div>
</template>
