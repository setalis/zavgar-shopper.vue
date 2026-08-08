import { isNoDivisionCurrency } from '@shopperlabs/shopper-types';

export { isNoDivisionCurrency };

const localeMap: Record<string, string> = {
  uk: 'uk-UA',
  en: 'en-US',
};

export function formatLocale(appLocale?: string): string {
  return localeMap[appLocale ?? 'uk'] ?? 'uk-UA';
}

export function formatMoney(
  amount: number,
  currency: string,
  locale = formatLocale(),
): string {
  const value = isNoDivisionCurrency(currency) ? amount : amount / 100;

  return new Intl.NumberFormat(locale, {
    style: 'currency',
    currency,
  }).format(value);
}

export function formatPercentage(
  value: number,
  locale = formatLocale(),
): string {
  return new Intl.NumberFormat(locale, {
    style: 'percent',
    maximumFractionDigits: 0,
  }).format(value / 100);
}

export function stripHtml(html: string | null | undefined): string {
  if (!html) return '';
  return html
    .replace(/<[^>]*>/g, '')
    .replace(/\s+/g, ' ')
    .trim();
}
