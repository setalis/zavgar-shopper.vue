<script setup lang="ts">
import type { HreflangLink } from '@/composables/useLocalizedRoute';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    links: HreflangLink[];
}>();

const page = usePage();

const defaultUrl = computed(
    () =>
        props.links.find((link) => link.locale === page.props.default_locale)
            ?.url ?? null,
);
</script>

<template>
    <link
        v-for="link in links"
        :key="link.locale"
        :head-key="`hreflang-${link.locale}`"
        rel="alternate"
        :hreflang="link.locale"
        :href="link.url"
    />
    <link
        v-if="defaultUrl"
        head-key="hreflang-x-default"
        rel="alternate"
        hreflang="x-default"
        :href="defaultUrl"
    />
</template>
