<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum HomepageBannerBackgroundType: string implements HasLabel
{
    case Gradient = 'gradient';
    case Image = 'image';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Gradient => __('backend.banners.background_types.gradient'),
            self::Image => __('backend.banners.background_types.image'),
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
