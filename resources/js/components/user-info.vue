<script setup lang="ts">
import { computed } from 'vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { useInitials } from '@/composables/useInitials';
import type { User } from '@/types';

type Props = {
    user: User;
    showEmail?: boolean;
};

const props = withDefaults(defineProps<Props>(), {
    showEmail: false,
});

const { getInitials } = useInitials();

const avatarUrl = computed<string | null>(() =>
    typeof props.user.avatar === 'string' && props.user.avatar !== ''
        ? props.user.avatar
        : null,
);

const displayName = computed<string>(() => {
    if (
        typeof props.user.full_name === 'string' &&
        props.user.full_name !== ''
    ) {
        return props.user.full_name;
    }

    if (typeof props.user.name === 'string') {
        return props.user.name;
    }

    return '';
});
</script>

<template>
    <Avatar class="size-8 overflow-hidden rounded-lg">
        <AvatarImage v-if="avatarUrl" :src="avatarUrl" :alt="displayName" />
        <AvatarFallback class="rounded-lg text-ink">
            {{ getInitials(displayName) }}
        </AvatarFallback>
    </Avatar>

    <div class="grid flex-1 text-left text-sm/tight">
        <span class="truncate font-medium">{{ displayName }}</span>
        <span v-if="showEmail" class="truncate text-xs text-muted-foreground">{{
            user.email
        }}</span>
    </div>
</template>
