<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\HomepageBannerColor;
use App\Enums\HomepageBannerShade;
use JsonSerializable;

final readonly class TailwindTint implements JsonSerializable
{
    /**
     * @var array<string, string>
     */
    private const array LEGACY = [
        'blue' => 'blue-500',
        'purple' => 'purple-500',
        'teal' => 'teal-500',
        'orange' => 'orange-500',
        'green' => 'green-500',
        'black' => 'zinc-950',
    ];

    public function __construct(
        public HomepageBannerColor $color,
        public HomepageBannerShade $shade,
    ) {}

    public static function of(HomepageBannerColor $color, HomepageBannerShade $shade = HomepageBannerShade::Shade500): self
    {
        return new self($color, $shade);
    }

    public static function parse(mixed $value): ?self
    {
        if ($value instanceof self) {
            return $value;
        }

        if (! filled($value) || ! is_string($value)) {
            return null;
        }

        $value = self::LEGACY[$value] ?? $value;

        if (! str_contains($value, '-')) {
            return null;
        }

        $color = HomepageBannerColor::tryFrom(str($value)->beforeLast('-')->toString());
        $shade = HomepageBannerShade::tryFrom((int) str($value)->afterLast('-')->toString());

        if ($color === null || $shade === null) {
            return null;
        }

        return new self($color, $shade);
    }

    public function value(): string
    {
        return $this->color->value.'-'.$this->shade->value;
    }

    public function oklch(): string
    {
        return TailwindPalette::COLORS[$this->color->value][$this->shade->value];
    }

    public function darker(): self
    {
        $shades = HomepageBannerShade::cases();
        $index = array_search($this->shade, $shades, true);

        if (! is_int($index)) {
            return $this;
        }

        return new self($this->color, $shades[min($index + 2, count($shades) - 1)]);
    }

    public function jsonSerialize(): string
    {
        return $this->value();
    }
}
