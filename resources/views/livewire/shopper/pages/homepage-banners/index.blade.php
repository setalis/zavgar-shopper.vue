<x-shopper::container class="py-5">
    <x-shopper::heading :title="__('backend.banners.menu')">
        <x-slot name="action">
            @can('add_homepage_banners')
                <x-filament::button
                    tag="a"
                    :href="route('shopper.banners.create')"
                    wire:navigate
                >
                    {{ __('shopper::forms.actions.add_label', ['label' => __('backend.banners.single')]) }}
                </x-filament::button>
            @endcan
        </x-slot>
    </x-shopper::heading>

    <div class="mt-10">
        {{ $this->table }}
    </div>
</x-shopper::container>
