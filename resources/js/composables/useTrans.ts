import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

type Replacements = Record<string, string | number>;

function applyReplacements(
  text: string,
  replacements?: Replacements,
): string {
  if (!replacements) {
    return text;
  }

  return Object.entries(replacements).reduce(
    (result, [key, value]) =>
      result.replaceAll(`:${key}`, String(value)),
    text,
  );
}

export function useTrans() {
  const page = usePage<{
    locale: string;
    locales: Record<string, string>;
    translations: Record<string, string>;
  }>();

  const locale = computed(() => page.props.locale);
  const locales = computed(() => page.props.locales);
  const translations = computed(() => page.props.translations);

  function t(key: string, replacements?: Replacements): string {
    const text = translations.value[key] ?? key;

    return applyReplacements(text, replacements);
  }

  return {
    locale,
    locales,
    t,
  };
}
