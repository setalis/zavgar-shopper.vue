<?php

declare(strict_types=1);

namespace App\Sidebar;

use Shopper\Sidebar\AbstractAdminSidebar;
use Shopper\Sidebar\Contracts\Builder\Group;
use Shopper\Sidebar\Contracts\Builder\Item;
use Shopper\Sidebar\Contracts\Builder\Menu;

final class HomepageBannersSidebar extends AbstractAdminSidebar
{
    public function extendWith(Menu $menu): Menu
    {
        $menu->group(__('backend.banners.menu_group'), function (Group $group): void {
            $group->weight(2);
            $group->setAuthorized();
            $group->collapsible();

            $group->item(__('backend.banners.menu'), function (Item $item): void {
                $item->weight(1);
                $item->setAuthorized($this->canBrowseHomepageBanners());
                $item->useSpa();
                $item->route('shopper.banners.index');
                $item->setIcon('phosphor-squares-four');
            });
        });

        return $menu;
    }

    private function canBrowseHomepageBanners(): bool
    {
        if ($this->user === null || ! method_exists($this->user, 'getAllPermissions')) {
            return false;
        }

        return (bool) $this->user->getAllPermissions()->contains(
            fn (object $permission): bool => $permission->name === 'browse_homepage_banners',
        );
    }
}
