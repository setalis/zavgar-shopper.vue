export function withPriceParams<T extends Record<string, unknown>>(
    params: T,
    priceMin: number | null | undefined,
    priceMax: number | null | undefined,
): T {
    const query = { ...params } as T & {
        price_min?: number;
        price_max?: number;
    };

    delete query.price_min;
    delete query.price_max;

    if (priceMin != null) {
        query.price_min = priceMin;
    }

    if (priceMax != null) {
        query.price_max = priceMax;
    }

    return query;
}
