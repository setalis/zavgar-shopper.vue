// eslint-disable-next-line @typescript-eslint/triple-slash-reference
/// <reference path="./generated.d.ts" />

import type {
    Address,
    Brand as BaseBrand,
    Cart as BaseCart,
    CartLine as BaseCartLine,
    Category as BaseCategory,
    Channel,
    Collection as BaseCollection,
    Media,
    Order as BaseOrder,
    OrderItem as BaseOrderItem,
    Product as BaseProduct,
    ProductVariant as BaseProductVariant,
} from '@shopperlabs/shopper-types';

export { isNoDivisionCurrency } from '@/lib/format';
export { AddressType, ProductType } from '@shopperlabs/shopper-types';

export type {
    Address,
    Channel,
    Country,
    Media,
    OrderStatus,
    PaymentStatus,
    Price,
    ShippingStatus,
} from '@shopperlabs/shopper-types';

type AddressData = App.DTO.AddressData;
type CountryByZoneData = App.DTO.CountryByZoneData;

type WithStorefrontMedia = {
    thumbnail?: string | null;
    images?: Media[];
};

export type Brand = BaseBrand & WithStorefrontMedia;

export type Category = BaseCategory &
    WithStorefrontMedia & {
        products_count?: number;
    };

export type NavCategoryChild = Pick<BaseCategory, 'id' | 'name' | 'slug'>;

export type NavCategory = Pick<BaseCategory, 'id' | 'name' | 'slug'> & {
    thumbnail?: string | null;
    children?: NavCategoryChild[];
};

export type Collection = BaseCollection &
    WithStorefrontMedia & {
        products_count?: number;
    };

export type ProductVariant = BaseProductVariant & WithStorefrontMedia;

export type StorefrontPrice = {
    amount: number;
    compare_amount: number | null;
    from: boolean;
};

export type Product = Omit<BaseProduct, 'brand' | 'variants'> &
    WithStorefrontMedia & {
        brand?: Brand | null;
        variants?: ProductVariant[];
        related_products?: Product[];
        storefront_price?: StorefrontPrice | null;
        storefront_stock?: number;
        storefront_reviews_count?: number;
        storefront_average_rating?: number | null;
    };

export type ProductPrice = StorefrontPrice;

export type ProductAttribute = {
    id: number;
    name: string;
    slug: string;
    type: string;
    value: string;
};

export type VariantOptions = {
    productOptions: Array<{
        id: number;
        name: string;
        slug: string;
        type: string;
        values: Array<{ id: number; value: string; key?: string | null }>;
    }>;
    variantIndex: Record<string, number>;
    variantMap: Record<
        number,
        {
            id: number;
            values: number[];
            stock: number;
            allow_backorder: boolean;
        }
    >;
    availabilityMatrix: Record<number, Record<number, boolean>>;
    hasStructuredAttributes: boolean;
};

export type CartLine = BaseCartLine & {
    purchasable: Product | ProductVariant;
};

export type CartContext = {
    subtotal: number;
    discountTotal: number;
    taxTotal: number;
    total: number;
    taxInclusive: boolean;
    lineSubtotals: Record<number, number>;
};

export type Cart = BaseCart & {
    lines: CartLine[];
};

export type OrderItem = BaseOrderItem & {
    product: Product | ProductVariant | null;
};

export type Order = Omit<
    BaseOrder,
    'shippingAddress' | 'billingAddress' | 'items'
> & {
    items: OrderItem[];
    shipping_address: Address | null;
    billing_address: Address | null;
};

export type DeliveryOption = {
    service_code: string | number;
    service_name: string;
    carrier_code: string;
    carrier_name: string | null;
    carrier_logo: string | null;
    description: string | null;
    currency: string;
    amount: number;
    estimated_days: number | null;
};

export type ZoneSession = {
    country_code: string;
    country_name: string;
    currency_code: string;
    zone_id: number;
};

export type ShopSharedProps = {
    cart_count: number;
    wishlist_count: number;
    wishlist_ids: number[];
    zone: ZoneSession | null;
    currency: string;
    channels: Channel[];
    available_zones: CountryByZoneData[];
    tax_label: string;
    logo: string | null;
    nav_categories: NavCategory[];
    footer_categories: NavCategory[];
};

export type StripePayment = {
    client_secret: string;
    publishable_key: string;
};

export type { AddressData, CountryByZoneData };
