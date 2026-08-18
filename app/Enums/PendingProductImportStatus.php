<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum PendingProductImportStatus: string implements HasColor, HasIcon, HasLabel
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Pending => __('backend.product_imports.status.pending'),
            self::Approved => __('backend.product_imports.status.approved'),
            self::Rejected => __('backend.product_imports.status.rejected'),
        };
    }

    /**
     * @return string|array<string>|null
     */
    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Approved => 'success',
            self::Rejected => 'danger',
        };
    }

    public function getIcon(): string|\BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::Pending => 'phosphor-clock',
            self::Approved => 'phosphor-check-circle',
            self::Rejected => 'phosphor-x-circle',
        };
    }
}
