<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useTrans } from '@/composables/useTrans';
import * as profile from '@/routes/profile';
import * as security from '@/routes/security';

type Item = { href: string; label: string };

const page = usePage();
const { t } = useTrans();

const items = computed<Item[]>(() => [
    { href: profile.edit.url(), label: t('settings.nav.profile') },
    { href: security.edit.url(), label: t('settings.nav.security') },
]);

function isActive(item: Item): boolean {
    const path = (page.url ?? '').split('?')[0];

    return path.startsWith(item.href);
}
</script>

<template>
    <section class="w-full">
        <div class="relative mb-6 w-full">
            <h1 class="font-heading text-2xl font-bold text-ink">
                {{ t('settings.layout.title') }}
            </h1>
            <p class="mt-1 mb-6 text-base text-ink-mute">
                {{ t('settings.layout.description') }}
            </p>
            <hr class="border-rule" />
        </div>

        <div class="flex items-start max-md:flex-col">
            <div class="me-10 w-full pb-4 md:w-[220px]">
                <nav
                    class="flex flex-col gap-1"
                    :aria-label="t('settings.layout.title')"
                >
                    <Link
                        v-for="item in items"
                        :key="item.href"
                        :href="item.href"
                        :class="[
                            'rounded-md px-3 py-2 text-sm transition',
                            isActive(item)
                                ? 'bg-brand-soft font-medium text-brand-deep'
                                : 'text-ink-mute hover:text-ink',
                        ]"
                    >
                        {{ item.label }}
                    </Link>
                </nav>
            </div>

            <hr class="border-rule md:hidden" />

            <div class="flex-1 self-stretch max-md:pt-6">
                <slot />
            </div>
        </div>
    </section>
</template>
