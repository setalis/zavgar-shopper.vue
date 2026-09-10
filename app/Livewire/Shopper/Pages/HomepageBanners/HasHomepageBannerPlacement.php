<?php

declare(strict_types=1);

namespace App\Livewire\Shopper\Pages\HomepageBanners;

use App\Enums\HomepageBannerPlacement;

trait HasHomepageBannerPlacement
{
    public HomepageBannerPlacement $placement = HomepageBannerPlacement::Bento;

    protected function syncPlacementFromRoute(): void
    {
        $fromRoute = HomepageBannerPlacement::fromRoute();

        if ($fromRoute instanceof HomepageBannerPlacement) {
            $this->placement = $fromRoute;
        }
    }

    public function isPromo(): bool
    {
        return $this->placement === HomepageBannerPlacement::Promo;
    }
}
