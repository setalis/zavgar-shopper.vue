<x-shopper::container class="py-5">
    @if ($this->parentItem)
        <x-shopper::breadcrumb :back="$this->parentIndexUrl()">
            <x-untitledui-chevron-left class="size-4 shrink-0 text-gray-300 dark:text-gray-600" aria-hidden="true" />
            <x-shopper::breadcrumb.link
                :link="$this->parentIndexUrl()"
                :title="$this->parentItem->parent?->title ?? __('backend.menu.menu')"
            />
        </x-shopper::breadcrumb>
    @endif

    <x-shopper::heading
        :class="$this->parentItem ? 'mt-6' : null"
        :title="$this->parentItem?->title ?? __('backend.menu.menu')"
    >
        <x-slot name="action">
            @if ($this->canAddAtCurrentLevel())
                <x-filament::button
                    tag="a"
                    :href="$this->createUrl()"
                    wire:navigate
                >
                    {{ __('shopper::forms.actions.add_label', ['label' => __('backend.menu.single')]) }}
                </x-filament::button>
            @endif
        </x-slot>
    </x-shopper::heading>

    <div class="mt-10">
        {{ $this->table }}
    </div>
</x-shopper::container>
