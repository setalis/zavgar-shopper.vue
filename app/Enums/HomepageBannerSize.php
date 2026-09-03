<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum HomepageBannerSize: string implements HasColor, HasLabel
{
    case Large = 'large';
    case Medium = 'medium';
    case Small = 'small';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Large => __('backend.banners.sizes.large'),
            self::Medium => __('backend.banners.sizes.medium'),
            self::Small => __('backend.banners.sizes.small'),
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $size): array => [$size->value => (string) $size->getLabel()])
            ->all();
    }

    /**
     * @return string|array<string>|null
     */
    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Large => 'info',
            self::Medium => 'warning',
            self::Small => 'gray',
        };
    }
}
