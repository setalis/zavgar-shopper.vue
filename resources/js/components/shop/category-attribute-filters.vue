<script setup lang="ts">
import { computed, useAttrs } from 'vue';
import { Checkbox } from '@/components/ui/checkbox';
import { useTrans } from '@/composables/useTrans';
import { cn } from '@/lib/utils';
import type { AttributeFilter } from '@/types/shop';

defineOptions({ inheritAttrs: false });

const props = defineProps<{
    attributes: AttributeFilter[];
    selected: Record<string, string[]>;
}>();

const emit = defineEmits<{
    toggle: [slug: string, key: string];
    clear: [];
}>();

const { t } = useTrans();

const attrs = useAttrs();
const rootClass = computed<string>(() =>
    cn(
        'self-start rounded-lg border border-rule bg-paper p-5 lg:sticky lg:max-h-[calc(100vh-11rem)] lg:overflow-y-auto',
        attrs.class as string,
    ),
);

const hasSelection = computed<boolean>(() =>
    Object.values(props.selected).some((keys) => keys.length > 0),
);

function isSelected(slug: string, key: string): boolean {
    return (props.selected[slug] ?? []).includes(key);
}
</script>

<template>
    <aside
        v-bind="{ ...$attrs, class: undefined }"
        :class="rootClass"
        :style="{ top: 'calc(var(--header-h) + var(--nav-h) + 1rem)' }"
        :aria-label="t('shop.filters.title')"
    >
        <div class="mb-5 flex items-center justify-between gap-3">
            <h2
                class="font-heading text-sm font-bold tracking-[0.06em] text-ink uppercase"
            >
                {{ t('shop.filters.title') }}
            </h2>
            <button
                v-if="hasSelection"
                type="button"
                class="font-mono text-[11px] tracking-[0.04em] text-ink-mute uppercase transition hover:text-brand"
                @click="emit('clear')"
            >
                {{ t('shop.filters.clear') }}
            </button>
        </div>

        <div class="divide-y divide-rule">
            <div
                v-for="attribute in attributes"
                :key="attribute.id"
                class="py-5 first:pt-0 last:pb-0"
            >
                <h3
                    class="mb-3 font-heading text-sm font-bold tracking-[0.06em] text-ink uppercase"
                >
                    {{
                        attribute.slug === 'brand'
                            ? t('shop.filters.brand')
                            : attribute.name
                    }}
                </h3>

                <div class="space-y-0.5">
                    <label
                        v-for="value in attribute.values"
                        :key="value.key"
                        class="flex cursor-pointer items-center gap-2 py-1.5 text-sm text-ink-soft transition hover:text-brand"
                    >
                        <Checkbox
                            :model-value="isSelected(attribute.slug, value.key)"
                            @update:model-value="
                                emit('toggle', attribute.slug, value.key)
                            "
                        />
                        <span
                            v-if="attribute.type === 'colorpicker'"
                            class="size-3.5 shrink-0 rounded-full border border-rule"
                            :style="{ backgroundColor: value.key }"
                            aria-hidden="true"
                        />
                        <span class="flex-1">{{ value.label }}</span>
                    </label>
                </div>
            </div>
        </div>
    </aside>
</template>
