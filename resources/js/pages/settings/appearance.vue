<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppearanceTabs from '@/components/appearance-tabs.vue';
import Heading from '@/components/heading.vue';
import { useTrans } from '@/composables/useTrans';
import { edit } from '@/routes/appearance';

const { t } = useTrans();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'settings.appearance.breadcrumb',
                href: edit(),
            },
        ],
    },
});
</script>

<template>
    <Head :title="t('settings.appearance.title')" />

    <h1 class="sr-only">{{ t('settings.appearance.title') }}</h1>

    <div class="space-y-6">
        <Heading
            variant="small"
            :title="t('settings.appearance.heading')"
            :description="t('settings.appearance.description')"
        />
        <AppearanceTabs />
    </div>
</template>
