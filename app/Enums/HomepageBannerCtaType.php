<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum HomepageBannerCtaType: string implements HasLabel
{
    case Url = 'url';
    case Category = 'category';
    case Product = 'product';
    case Collection = 'collection';
    case Brand = 'brand';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Url => __('backend.banners.cta_types.url'),
            self::Category => __('backend.banners.cta_types.category'),
            self::Product => __('backend.banners.cta_types.product'),
            self::Collection => __('backend.banners.cta_types.collection'),
            self::Brand => __('backend.banners.cta_types.brand'),
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $type): array => [$type->value => (string) $type->getLabel()])
            ->all();
    }
}
