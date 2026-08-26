<?php

declare(strict_types=1);

use App\Models\Brand;
use App\Models\Category;
use App\Models\Channel;
use App\Models\Collection;
use App\Models\Product;
use App\Models\ProductVariant;
use Shopper\Core\Models\Address;
use Shopper\Core\Models\Inventory;
use Shopper\Core\Models\Order;
use Shopper\Core\Models\Supplier;
use Shopper\Core\Models\TaxRate;
use Shopper\Core\Models\TaxZone;

return [

    /*
    |--------------------------------------------------------------------------
    | Address Model
    |--------------------------------------------------------------------------
    |
    | Eloquent model should be used to retrieve user shipping / billing address.
    | If you want to use a custom model, your model needs to extends the
    | \Shopper\Core\Models\Address Model.
    |
    */

    'address' => Address::class,

    /*
    |--------------------------------------------------------------------------
    | Brand Model
    |--------------------------------------------------------------------------
    |
    | Eloquent model should be used to interacts with your brands.
    | If you want to use a custom model, your model needs to extends the
    | \Shopper\Models\Brand Model.
    |
    */

    'brand' => Brand::class,

    /*
    |--------------------------------------------------------------------------
    | Category Model
    |--------------------------------------------------------------------------
    |
    | Eloquent model should be used to interacts with your categories.
    | If you want to use a custom model, your model needs to extends the
    | \Shopper\Models\Category Model.
    |
    */

    'category' => Category::class,

    /*
    |--------------------------------------------------------------------------
    | Collection Model
    |--------------------------------------------------------------------------
    |
    | Eloquent model should be used to interacts with your collections.
    | If you want to use a custom model, your model needs to extends the
    | \Shopper\Models\Collection Model.
    |
    */

    'collection' => Collection::class,

    /*
    |--------------------------------------------------------------------------
    | Product Model
    |--------------------------------------------------------------------------
    |
    | Eloquent model should be used to interacts with your products.
    | If you want to use a custom model, your model needs to extends the
    | \Shopper\Models\Product Model.
    |
    */

    'product' => Product::class,

    /*
    |--------------------------------------------------------------------------
    | Product Variant Model
    |--------------------------------------------------------------------------
    |
    | Eloquent model should be used to interacts with your product variants.
    | If you want to use a custom model, your model needs to extends the
    | \Shopper\Models\ProductVariant Model.
    |
    */

    'variant' => ProductVariant::class,

    /*
    |--------------------------------------------------------------------------
    | Channel Model
    |--------------------------------------------------------------------------
    |
    | Eloquent model should be used to interacts with your channels.
    | If you want to use a custom model, your model needs to extends the
    | \Shopper\Core\Models\Channel Model.
    |
    */

    'channel' => Channel::class,

    /*
    |--------------------------------------------------------------------------
    | Inventory Model
    |--------------------------------------------------------------------------
    |
    | Eloquent model should be used to interact with inventories.
    | If you want to use a custom model, your model needs to extends the
    | \Shopper\Core\Models\Inventory Model.
    |
    */

    'inventory' => Inventory::class,

    /*
    |--------------------------------------------------------------------------
    | Order Model
    |--------------------------------------------------------------------------
    |
    | Eloquent model should be used to interact with your orders.
    | If you want to use a custom model, your model needs to extends the
    | \Shopper\Core\Models\Order Model.
    |
    */

    'order' => Order::class,

    /*
    |--------------------------------------------------------------------------
    | Supplier Model
    |--------------------------------------------------------------------------
    |
    | Eloquent model should be used to interact with your suppliers.
    | If you want to use a custom model, your model needs to extends the
    | \Shopper\Core\Models\Supplier Model.
    |
    */

    'supplier' => Supplier::class,

    /*
    |--------------------------------------------------------------------------
    | Tax Zone Model
    |--------------------------------------------------------------------------
    |
    | Eloquent model should be used to interact with your tax zones.
    | If you want to use a custom model, your model needs to extends the
    | \Shopper\Core\Models\TaxZone Model.
    |
    */

    'tax_zone' => TaxZone::class,

    /*
    |--------------------------------------------------------------------------
    | Tax Rate Model
    |--------------------------------------------------------------------------
    |
    | Eloquent model should be used to interact with your tax rates.
    | If you want to use a custom model, your model needs to extends the
    | \Shopper\Core\Models\TaxRate Model.
    |
    */

    'tax_rate' => TaxRate::class,

];
