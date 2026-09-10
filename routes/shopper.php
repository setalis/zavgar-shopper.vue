<?php

declare(strict_types=1);

use App\Livewire\Shopper\Pages\HomepageBanners\Edit as HomepageBannerEdit;
use App\Livewire\Shopper\Pages\HomepageBanners\Index as HomepageBannersIndex;
use App\Livewire\Shopper\Pages\MenuItems\Edit as MenuItemEdit;
use App\Livewire\Shopper\Pages\MenuItems\Index as MenuItemsIndex;
use App\Livewire\Shopper\Pages\Product\PendingImports;
use Illuminate\Support\Facades\Route;

Route::get('/products/pending-imports', PendingImports::class)
    ->name('products.pending-imports');

Route::get('/banners', HomepageBannersIndex::class)
    ->name('banners.index');
Route::get('/banners/create', HomepageBannerEdit::class)
    ->name('banners.create');
Route::get('/banners/{banner}/edit', HomepageBannerEdit::class)
    ->name('banners.edit');

Route::get('/promo-banners', HomepageBannersIndex::class)
    ->name('promo-banners.index');
Route::get('/promo-banners/create', HomepageBannerEdit::class)
    ->name('promo-banners.create');
Route::get('/promo-banners/{banner}/edit', HomepageBannerEdit::class)
    ->name('promo-banners.edit');

Route::get('/menu', MenuItemsIndex::class)
    ->name('menu.index');
Route::get('/menu/create', MenuItemEdit::class)
    ->name('menu.create');
Route::get('/menu/{menuItem}/edit', MenuItemEdit::class)
    ->name('menu.edit');
