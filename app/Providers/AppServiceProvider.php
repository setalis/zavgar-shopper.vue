<?php

declare(strict_types=1);

namespace App\Providers;

use App\Listeners\MergeGuestWishlist;
use App\Livewire\Shopper\Components\Products\Form\Variants as ProductVariantsForm;
use App\Livewire\Shopper\Pages\Order\Detail as OrderDetail;
use App\Livewire\Shopper\Pages\Product\Index as ProductIndex;
use App\Livewire\Shopper\Pages\Settings\General as GeneralSettings;
use App\Livewire\Shopper\SlideOvers\AttributeForm;
use App\Sidebar\PendingProductImportsSidebar;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Livewire\Livewire;
use Shopper\Sidebar\SidebarBuilder;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Config must be overridden in register() so Shopper routes pick up
        // app Livewire page classes when package routes are loaded during boot.
        $this->app->booting(function (): void {
            config([
                'shopper.components.order.pages.order-detail' => OrderDetail::class,
                'shopper.components.product.pages.product-index' => ProductIndex::class,
                'shopper.components.product.components' => array_merge(
                    config('shopper.components.product.components', []),
                    ['products.form.variants' => ProductVariantsForm::class],
                ),
                'shopper.components.setting.pages.general' => GeneralSettings::class,
            ]);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerShopperLivewireAliases();
        $this->registerShopperSidebar();
        $this->configureDefaults();
        $this->app['events']->listen(Login::class, MergeGuestWishlist::class);
    }

    protected function registerShopperLivewireAliases(): void
    {
        Livewire::component('shopper-slide-overs.attribute-form', AttributeForm::class);
        Livewire::component('shopper-general', GeneralSettings::class);
    }

    protected function registerShopperSidebar(): void
    {
        $this->app['events']->listen(SidebarBuilder::class, PendingProductImportsSidebar::class);
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(
            fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
