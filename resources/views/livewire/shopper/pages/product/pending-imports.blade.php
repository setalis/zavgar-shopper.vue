<x-shopper::container class="py-5">
    <x-shopper::heading :title="__('backend.product_imports.menu')">
        <x-slot name="action">
            <x-filament::button
                tag="a"
                :href="route('shopper.products.index')"
                color="gray"
            >
                {{ __('backend.product_imports.back_to_products') }}
            </x-filament::button>
        </x-slot>
    </x-shopper::heading>

    <p class="mt-2 max-w-3xl text-sm text-gray-500 dark:text-gray-400">
        {{ __('backend.product_imports.description') }}
    </p>

    <div class="mt-10">
        {{ $this->table }}
    </div>
</x-shopper::container>
