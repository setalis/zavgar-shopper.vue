<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum ContactTopic: string implements HasLabel
{
    case Order = 'order';
    case Product = 'product';
    case Returns = 'returns';
    case Trade = 'trade';
    case Other = 'other';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Order => __('backend.contact.topics.order'),
            self::Product => __('backend.contact.topics.product'),
            self::Returns => __('backend.contact.topics.returns'),
            self::Trade => __('backend.contact.topics.trade'),
            self::Other => __('backend.contact.topics.other'),
        };
    }
}
