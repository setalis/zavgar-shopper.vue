<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum HomepageBannerPlacement: string implements HasLabel
{
    case Bento = 'bento';
    case Promo = 'promo';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Bento => __('backend.banners.menu'),
            self::Promo => __('backend.banners.promo_menu'),
        };
    }

    public function indexRouteName(): string
    {
        return match ($this) {
            self::Bento => 'shopper.banners.index',
            self::Promo => 'shopper.promo-banners.index',
        };
    }

    public function createRouteName(): string
    {
        return match ($this) {
            self::Bento => 'shopper.banners.create',
            self::Promo => 'shopper.promo-banners.create',
        };
    }

    public function editRouteName(): string
    {
        return match ($this) {
            self::Bento => 'shopper.banners.edit',
            self::Promo => 'shopper.promo-banners.edit',
        };
    }

    public function routeIsPattern(): string
    {
        return match ($this) {
            self::Bento => 'shopper.banners.*',
            self::Promo => 'shopper.promo-banners.*',
        };
    }

    public static function fromRoute(): ?self
    {
        return match (true) {
            request()->routeIs(self::Promo->routeIsPattern()) => self::Promo,
            request()->routeIs(self::Bento->routeIsPattern()) => self::Bento,
            default => null,
        };
    }
}
