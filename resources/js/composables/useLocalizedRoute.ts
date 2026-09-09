import { router, usePage } from '@inertiajs/vue3';
import { computed, watchEffect } from 'vue';
import { setUrlDefaults } from '@/wayfinder';

export type HreflangLink = {
    locale: string;
    url: string;
};

function collapseOptionalPrefix(url: string): string {
    if (/^https?:\/\//.test(url)) {
        const parsed = new URL(url);
        parsed.pathname = parsed.pathname.replace(/\/{2,}/g, '/') || '/';

        return parsed.toString();
    }

    const queryIndex = url.indexOf('?');
    const path = queryIndex === -1 ? url : url.slice(0, queryIndex);
    const query = queryIndex === -1 ? '' : url.slice(queryIndex);
    const collapsed = path.replace(/\/{2,}/g, '/') || '/';

    return collapsed + query;
}

export function useLocalizedRoute() {
    const page = usePage();
    const locale = computed(() => page.props.locale);
    const defaultLocale = computed(() => page.props.default_locale);

    watchEffect(() => {
        setUrlDefaults({
            locale: undefined,
        });
    });

    function localized(url: string): string {
        const collapsed = collapseOptionalPrefix(url);

        if (!locale.value || locale.value === defaultLocale.value) {
            return collapsed;
        }

        const prefix = `/${locale.value}`;

        if (collapsed === '/') {
            return prefix;
        }

        if (collapsed === prefix || collapsed.startsWith(`${prefix}/`)) {
            return collapsed;
        }

        return prefix + (collapsed.startsWith('/') ? collapsed : `/${collapsed}`);
    }

    function urlForLocale(targetLocale: string): string {
        const fromServer = page.props.locale_urls?.[targetLocale];

        if (fromServer) {
            return fromServer;
        }

        const { pathname, search } = window.location;
        const prefixes = Object.keys(page.props.locales).filter(
            (code) => code !== defaultLocale.value,
        );
        const segments = pathname.split('/').filter(Boolean);
        const hasPrefix = prefixes.includes(segments[0] ?? '');
        const stripped = hasPrefix
            ? `/${segments.slice(1).join('/')}`
            : pathname;
        const prefix =
            targetLocale === defaultLocale.value ? '' : `/${targetLocale}`;
        const nextPath =
            `${prefix}${stripped === '/' ? '' : stripped}` || '/';

        return nextPath + search;
    }

    function switchTo(targetLocale: string): void {
        if (targetLocale === locale.value) {
            return;
        }

        router.visit(urlForLocale(targetLocale), {
            preserveScroll: true,
            preserveState: false,
        });
    }

    function withoutLocalePrefix(path: string): string {
        const pathname = path.split('?')[0] ?? path;
        const prefixes = Object.keys(page.props.locales).filter(
            (code) => code !== defaultLocale.value,
        );
        const segments = pathname.split('/').filter(Boolean);

        if (prefixes.includes(segments[0] ?? '')) {
            return `/${segments.slice(1).join('/')}` || '/';
        }

        return pathname || '/';
    }

    return {
        locale,
        defaultLocale,
        localized,
        switchTo,
        urlForLocale,
        withoutLocalePrefix,
    };
}
