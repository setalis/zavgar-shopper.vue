<?php

declare(strict_types=1);

use App\Livewire\Shopper\Pages\HomepageBanners\Edit as HomepageBannerEdit;
use App\Livewire\Shopper\Pages\HomepageBanners\Index as HomepageBannersIndex;
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
