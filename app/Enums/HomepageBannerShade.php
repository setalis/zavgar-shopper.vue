<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum HomepageBannerShade: int implements HasLabel
{
    case Shade50 = 50;
    case Shade100 = 100;
    case Shade200 = 200;
    case Shade300 = 300;
    case Shade400 = 400;
    case Shade500 = 500;
    case Shade600 = 600;
    case Shade700 = 700;
    case Shade800 = 800;
    case Shade900 = 900;
    case Shade950 = 950;

    public function getLabel(): string|Htmlable|null
    {
        return (string) $this->value;
    }
}
