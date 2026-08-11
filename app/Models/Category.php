<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\InteractsWithStorefrontMedia;
use App\Observers\CategoryObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Shopper\Models\Category as Model;

#[ObservedBy([CategoryObserver::class])]
final class Category extends Model
{
    use InteractsWithStorefrontMedia;
}
