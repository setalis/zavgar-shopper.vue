<?php

declare(strict_types=1);

use App\Enums\HomepageBannerColor;
use App\Enums\HomepageBannerShade;
use App\Support\TailwindPalette;
use App\Support\TailwindTint;

test('it parses a tailwind color and shade token', function (): void {
    expect(TailwindTint::parse('sky-700'))
        ->toEqual(TailwindTint::of(HomepageBannerColor::Sky, HomepageBannerShade::Shade700));
});

test('it maps legacy banner colors to the 500 shade', function (string $legacy, TailwindTint $expected): void {
    expect(TailwindTint::parse($legacy))->toEqual($expected);
})->with([
    ['blue', TailwindTint::of(HomepageBannerColor::Blue)],
    ['purple', TailwindTint::of(HomepageBannerColor::Purple)],
    ['teal', TailwindTint::of(HomepageBannerColor::Teal)],
    ['orange', TailwindTint::of(HomepageBannerColor::Orange)],
    ['green', TailwindTint::of(HomepageBannerColor::Green)],
    ['black', TailwindTint::of(HomepageBannerColor::Zinc, HomepageBannerShade::Shade950)],
]);

test('it returns null for empty or invalid tints', function (mixed $value): void {
    expect(TailwindTint::parse($value))->toBeNull();
})->with([
    null,
    '',
    'not-a-color',
    'sky-999',
    500,
]);

test('it darkens a shade by two steps', function (): void {
    expect(TailwindTint::of(HomepageBannerColor::Red)->darker())
        ->toEqual(TailwindTint::of(HomepageBannerColor::Red, HomepageBannerShade::Shade700))
        ->and(TailwindTint::of(HomepageBannerColor::Red, HomepageBannerShade::Shade950)->darker())
        ->toEqual(TailwindTint::of(HomepageBannerColor::Red, HomepageBannerShade::Shade950));
});

test('it uses the documented tailwind oklch value', function (): void {
    expect(TailwindTint::of(HomepageBannerColor::Red)->oklch())
        ->toBe(TailwindPalette::COLORS['red'][500]);
});
