<script setup lang="ts">
import { computed, ref, useAttrs } from 'vue';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import { Slider } from '@/components/ui/slider';
import { useTrans } from '@/composables/useTrans';
import { cn } from '@/lib/utils';
import type { Category } from '@/types/shop';

defineOptions({ inheritAttrs: false });

export type FilterCategory = Pick<Category, 'id' | 'name' | 'slug'> & {
    products_count?: number;
    children?: FilterCategory[];
};

const props = defineProps<{
    categories: FilterCategory[];
    activeCategory: number | null;
}>();

const emit = defineEmits<{ selectCategory: [value: number | null] }>();

const { t } = useTrans();

const attrs = useAttrs();
const rootClass = computed<string>(() =>
    cn(
        'self-start rounded-lg border border-rule bg-paper p-5 lg:sticky lg:max-h-[calc(100vh-11rem)] lg:overflow-y-auto',
        attrs.class as string,
    ),
);

/**
 * Price, rating and availability come from the Sprylo template but the
 * catalogue endpoint only understands search / category / sort, so these
 * groups stay presentational until the backend gains the matching filters.
 */
const priceRange = ref<number[]>([20, 70]);
const rating = ref<string>('any');
const availability = ref<string>('any');

const ratingOptions = ['4', '3', 'any'] as const;
const availabilityOptions = ['in_stock', 'preorder', 'any'] as const;

function toggleCategory(id: number): void {
    emit('selectCategory', props.activeCategory === id ? null : id);
}
</script>

<template>
    <aside
        v-bind="{ ...$attrs, class: undefined }"
        :class="rootClass"
        :style="{ top: 'calc(var(--header-h) + var(--nav-h) + 1rem)' }"
        :aria-label="t('shop.filters.title')"
    >
        <div class="border-b border-rule pb-5">
            <h3
                class="mb-3 font-heading text-sm font-bold tracking-[0.06em] text-ink uppercase"
            >
                {{ t('shop.filters.category') }}
            </h3>

            <div class="space-y-0.5">
                <button
                    type="button"
                    :class="[
                        'flex w-full items-center gap-2 py-1.5 text-sm transition hover:text-brand',
                        activeCategory === null
                            ? 'font-semibold text-brand'
                            : 'text-ink-soft',
                    ]"
                    @click="emit('selectCategory', null)"
                >
                    {{ t('shop.filters.all_categories') }}
                </button>

                <div v-for="category in categories" :key="category.id">
                    <label
                        class="flex cursor-pointer items-center gap-2 py-1.5 text-sm text-ink-soft transition hover:text-brand"
                    >
                        <Checkbox
                            :model-value="activeCategory === category.id"
                            @update:model-value="toggleCategory(category.id)"
                        />
                        <span class="flex-1">{{ category.name }}</span>
                        <span
                            v-if="category.products_count !== undefined"
                            class="font-mono text-[11px] text-ink-faint"
                        >
                            {{ category.products_count }}
                        </span>
                    </label>

                    <div
                        v-if="category.children?.length"
                        class="ml-6 border-l border-rule pl-3"
                    >
                        <label
                            v-for="child in category.children"
                            :key="child.id"
                            class="flex cursor-pointer items-center gap-2 py-1.5 text-sm text-ink-mute transition hover:text-brand"
                        >
                            <Checkbox
                                :model-value="activeCategory === child.id"
                                @update:model-value="toggleCategory(child.id)"
                            />
                            <span class="flex-1">{{ child.name }}</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="border-b border-rule py-5">
            <h3
                class="mb-3 font-heading text-sm font-bold tracking-[0.06em] text-ink uppercase"
            >
                {{ t('shop.filters.price_range') }}
            </h3>
            <Slider v-model="priceRange" :min="0" :max="100" :step="1" />
            <p class="mt-3 font-mono text-[11px] text-ink-mute">
                {{ priceRange[0] }}% — {{ priceRange[1] }}%
            </p>
        </div>

        <div class="border-b border-rule py-5">
            <h3
                class="mb-3 font-heading text-sm font-bold tracking-[0.06em] text-ink uppercase"
            >
                {{ t('shop.filters.rating') }}
            </h3>
            <RadioGroup v-model="rating" class="gap-0.5">
                <div
                    v-for="option in ratingOptions"
                    :key="option"
                    class="flex items-center gap-2 py-1.5"
                >
                    <RadioGroupItem :id="`rating-${option}`" :value="option" />
                    <Label
                        :for="`rating-${option}`"
                        class="cursor-pointer text-sm font-normal text-ink-soft"
                    >
                        {{ t(`shop.filters.rating_options.${option}`) }}
                    </Label>
                </div>
            </RadioGroup>
        </div>

        <div class="pt-5">
            <h3
                class="mb-3 font-heading text-sm font-bold tracking-[0.06em] text-ink uppercase"
            >
                {{ t('shop.filters.availability') }}
            </h3>
            <RadioGroup v-model="availability" class="gap-0.5">
                <div
                    v-for="option in availabilityOptions"
                    :key="option"
                    class="flex items-center gap-2 py-1.5"
                >
                    <RadioGroupItem
                        :id="`availability-${option}`"
                        :value="option"
                    />
                    <Label
                        :for="`availability-${option}`"
                        class="cursor-pointer text-sm font-normal text-ink-soft"
                    >
                        {{ t(`shop.filters.availability_options.${option}`) }}
                    </Label>
                </div>
            </RadioGroup>
        </div>
    </aside>
</template>
