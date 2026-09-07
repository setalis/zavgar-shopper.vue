<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum MenuItemTargetType: string implements HasLabel
{
    case Url = 'url';
    case Brand = 'brand';
    case Category = 'category';
    case Collection = 'collection';
    case Product = 'product';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Url => __('backend.menu.target_types.url'),
            self::Brand => __('backend.menu.target_types.brand'),
            self::Category => __('backend.menu.target_types.category'),
            self::Collection => __('backend.menu.target_types.collection'),
            self::Product => __('backend.menu.target_types.product'),
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
