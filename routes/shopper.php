<?php

declare(strict_types=1);

use App\Livewire\Shopper\Pages\Product\PendingImports;
use Illuminate\Support\Facades\Route;

Route::get('/products/pending-imports', PendingImports::class)
    ->name('products.pending-imports');
