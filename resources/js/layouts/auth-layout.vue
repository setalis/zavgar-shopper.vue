<script setup lang="ts">
import { computed } from 'vue';
import AuthLayout from '@/layouts/auth/auth-simple-layout.vue';
import { useTrans } from '@/composables/useTrans';

const props = defineProps<{
    title?: string;
    description?: string;
}>();

const { t } = useTrans();

function translateIfKey(value: string): string {
    return value.includes('.') ? t(value) : value;
}

const resolvedTitle = computed(() => translateIfKey(props.title ?? ''));
const resolvedDescription = computed(() =>
    translateIfKey(props.description ?? ''),
);
</script>

<template>
    <AuthLayout
        :title="resolvedTitle"
        :description="resolvedDescription"
    >
        <slot />
    </AuthLayout>
</template>
