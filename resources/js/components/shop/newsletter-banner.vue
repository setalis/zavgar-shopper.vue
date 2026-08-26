<script setup lang="ts">
import { ref } from 'vue';
import Container from '@/components/shop/container.vue';
import { Button } from '@/components/ui/button';
import { useTrans } from '@/composables/useTrans';

const { t } = useTrans();

const email = ref<string>('');
const sent = ref<boolean>(false);

/**
 * The template ships the newsletter as a presentation-only block; there is no
 * subscriber endpoint yet, so the form only acknowledges the submission.
 */
function acknowledge(): void {
    if (email.value.trim().length === 0) {
        return;
    }

    sent.value = true;
}
</script>

<template>
    <Container>
        <div
            class="relative my-14 overflow-hidden rounded-2xl bg-linear-to-br from-brand via-brand-deep to-card-purple-2 px-10 py-14 text-paper"
        >
            <div
                class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_80%_20%,rgb(255_255_255/0.18)_0,transparent_40%),radial-gradient(circle_at_10%_80%,rgb(255_255_255/0.1)_0,transparent_50%)]"
                aria-hidden="true"
            />

            <div class="relative grid items-center gap-10 lg:grid-cols-2">
                <div>
                    <h2
                        class="max-w-[16ch] text-display leading-[1.05] tracking-[-0.025em] text-paper"
                    >
                        {{ t('shop.newsletter.title') }}
                        <strong class="font-extrabold text-amber">
                            {{ t('shop.newsletter.title_highlight') }}
                        </strong>
                    </h2>
                    <p class="mt-3 max-w-[38ch] text-md text-paper/86">
                        {{ t('shop.newsletter.description') }}
                    </p>
                </div>

                <form
                    class="flex items-center gap-2 rounded-full bg-paper/96 p-1.5"
                    @submit.prevent="acknowledge"
                >
                    <input
                        v-model="email"
                        type="email"
                        required
                        :placeholder="t('shop.newsletter.placeholder')"
                        :aria-label="t('shop.newsletter.placeholder')"
                        class="flex-1 bg-transparent px-4 py-3 text-base text-ink placeholder:text-ink-faint focus:outline-none"
                    />
                    <Button type="submit" variant="ink">
                        {{
                            sent
                                ? t('shop.newsletter.sent')
                                : t('shop.newsletter.submit')
                        }}
                    </Button>
                </form>
            </div>
        </div>
    </Container>
</template>
