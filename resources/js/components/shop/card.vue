<script setup lang="ts">
import { cn } from '@/lib/utils';

const props = defineProps<{
    class?: string;
}>();
</script>

<template>
    <div :class="cn('rounded-lg border border-rule bg-paper p-5', props.class)">
        <slot />
    </div>
</template>
