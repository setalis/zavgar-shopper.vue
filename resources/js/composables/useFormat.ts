import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { formatLocale, formatMoney, formatPercentage } from '@/lib/format';

export function useFormat() {
    const page = usePage<{ locale: string }>();
    const locale = computed(() => formatLocale(page.props.locale));

    function money(amount: number, currency: string): string {
        return formatMoney(amount, currency, locale.value);
    }

    function percentage(value: number): string {
        return formatPercentage(value, locale.value);
    }

    return {
        locale,
        money,
        percentage,
    };
}
