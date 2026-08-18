<?php

declare(strict_types=1);

namespace App\Sidebar;

use Shopper\Sidebar\AbstractAdminSidebar;
use Shopper\Sidebar\Contracts\Builder\Group;
use Shopper\Sidebar\Contracts\Builder\Item;
use Shopper\Sidebar\Contracts\Builder\Menu;

final class PendingProductImportsSidebar extends AbstractAdminSidebar
{
    public function extendWith(Menu $menu): Menu
    {
        $menu->group(__('shopper::layout.sidebar.catalog'), function (Group $group): void {
            $group->item(__('backend.product_imports.menu'), function (Item $item): void {
                $item->weight(5);
                $item->setAuthorized($this->user->hasPermissionTo('browse_products'));
                $item->useSpa();
                $item->route('shopper.products.pending-imports');
                $item->setIcon('phosphor-tray-arrow-down');
            });
        });

        return $menu;
    }
}
