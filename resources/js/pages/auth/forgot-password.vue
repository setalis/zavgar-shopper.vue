<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/input-error.vue';
import TextLink from '@/components/text-link.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { useTrans } from '@/composables/useTrans';
import { login } from '@/routes';
import { email } from '@/routes/password';

const { t } = useTrans();

defineOptions({
    layout: {
        title: 'auth.forgot_password.layout.title',
        description: 'auth.forgot_password.layout.description',
    },
});

defineProps<{
    status?: string;
}>();
</script>

<template>
    <Head :title="t('auth.forgot_password.title')" />

    <div
        v-if="status"
        class="mb-4 text-center text-sm font-medium text-green-600"
    >
        {{ status }}
    </div>

    <div class="space-y-6">
        <Form v-bind="email.form()" v-slot="{ errors, processing }">
            <div class="grid gap-2">
                <Label for="email">{{ t('auth.forgot_password.email') }}</Label>
                <Input
                    id="email"
                    type="email"
                    name="email"
                    autocomplete="off"
                    autofocus
                    :placeholder="t('auth.forgot_password.email_placeholder')"
                />
                <InputError :message="errors.email" />
            </div>

            <div class="my-6 flex items-center justify-start">
                <Button
                    class="w-full"
                    :disabled="processing"
                    data-test="email-password-reset-link-button"
                >
                    <Spinner v-if="processing" />
                    {{ t('auth.forgot_password.submit') }}
                </Button>
            </div>
        </Form>

        <div class="space-x-1 text-center text-sm text-muted-foreground">
            <span>{{ t('auth.forgot_password.return_to') }}</span>
            <TextLink :href="login()">{{
                t('auth.forgot_password.log_in')
            }}</TextLink>
        </div>
    </div>
</template>
