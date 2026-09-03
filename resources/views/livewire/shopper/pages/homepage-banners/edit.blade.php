<x-shopper::container class="py-5">
    <x-shopper::breadcrumb :back="route('shopper.banners.index')">
        <x-untitledui-chevron-left class="size-4 shrink-0 text-gray-300 dark:text-gray-600" aria-hidden="true" />
        <x-shopper::breadcrumb.link
            :link="route('shopper.banners.index')"
            :title="__('backend.banners.menu')"
        />
    </x-shopper::breadcrumb>

    <x-shopper::heading
        class="mt-6"
        :title="$banner->exists ? $banner->title : __('backend.banners.create')"
    />

    <form wire:submit="store" class="mt-8 border-t border-gray-200 pt-10 dark:border-white/20">
        <div class="space-y-10">
            {{ $this->form }}

            <div class="border-t border-gray-200 py-8 dark:border-white/10">
                <div class="flex justify-end">
                    <x-filament::button type="submit" wire.loading.attr="disabled">
                        <x-shopper::loader wire:loading wire:target="store" class="text-white" />
                        {{ $banner->exists ? __('shopper::forms.actions.update') : __('shopper::forms.actions.add_label', ['label' => __('backend.banners.single')]) }}
                    </x-filament::button>
                </div>
            </div>
        </div>
    </form>
</x-shopper::container>
