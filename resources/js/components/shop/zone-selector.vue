<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { useShop } from '@/composables/useShop';
import { useTrans } from '@/composables/useTrans';
import type { CountryByZoneData } from '@/types/shop';

const { zone, availableZones, changeZone } = useShop();
const { t } = useTrans();

const open = ref<boolean>(false);

watch(
    [zone, availableZones],
    ([currentZone, zones]) => {
        if (currentZone || zones.length === 0) {
            return;
        }

        if (zones.length === 1) {
            changeZone(zones[0].countryCode);

            return;
        }

        open.value = true;
    },
    { immediate: true },
);

const grouped = computed<Record<string, CountryByZoneData[]>>(() => {
    return availableZones.value.reduce<Record<string, CountryByZoneData[]>>(
        (acc, country) => {
            const key = country.zoneName;
            (acc[key] ??= []).push(country);

            return acc;
        },
        {},
    );
});

const currentCountry = computed<CountryByZoneData | undefined>(() =>
    availableZones.value.find(
        (c) => c.countryCode === zone.value?.country_code,
    ),
);

function select(country: CountryByZoneData): void {
    open.value = false;
    changeZone(country.countryCode);
}
</script>

<template>
    <div>
        <div v-if="zone" class="flex flex-wrap items-center gap-x-3 gap-y-1">
            <span class="font-mono text-xs tracking-[0.04em] text-current/70">
                {{ t('shop.zone.shipping_to') }}
            </span>
            <button
                type="button"
                class="inline-flex items-center gap-2 text-sm font-semibold underline decoration-current/40 underline-offset-4 transition hover:decoration-current"
                @click="open = true"
            >
                <img
                    v-if="currentCountry"
                    :src="currentCountry.countryFlag"
                    alt=""
                    class="block h-auto w-5 shrink-0 rounded-xs"
                />
                {{ zone.country_name }}
            </button>
        </div>

        <Dialog v-model:open="open">
            <DialogContent class="rounded-xl border-rule sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle
                        class="font-heading text-lg font-bold text-ink"
                    >
                        {{ t('shop.zone.select_country') }}
                    </DialogTitle>
                    <DialogDescription class="text-sm text-ink-mute">
                        <template v-if="zone">
                            {{ t('shop.zone.currently_shipping') }}
                            <span class="font-semibold text-ink">
                                {{ zone.country_name }}
                            </span>
                            —
                        </template>
                        {{ t('shop.zone.change_notice') }}
                    </DialogDescription>
                </DialogHeader>

                <div class="max-h-96 divide-y divide-rule overflow-y-auto">
                    <div
                        v-for="(countries, zoneName) in grouped"
                        :key="zoneName"
                        class="py-4"
                    >
                        <h4
                            class="font-mono text-[11px] tracking-[0.08em] text-ink-faint uppercase"
                        >
                            {{ zoneName }}
                        </h4>
                        <ul role="listbox" class="mt-2 space-y-1">
                            <li
                                v-for="country in countries"
                                :key="country.countryId"
                            >
                                <button
                                    type="button"
                                    :class="[
                                        'flex w-full items-center gap-2 rounded-md px-3 py-2 text-sm transition',
                                        zone?.country_code ===
                                        country.countryCode
                                            ? 'bg-brand-soft font-semibold text-brand-deep'
                                            : 'text-ink-soft hover:bg-muted',
                                    ]"
                                    @click="select(country)"
                                >
                                    <img
                                        :src="country.countryFlag"
                                        alt=""
                                        class="block h-auto w-5 shrink-0 rounded-xs"
                                    />
                                    {{ country.countryName }}
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>
            </DialogContent>
        </Dialog>
    </div>
</template>
