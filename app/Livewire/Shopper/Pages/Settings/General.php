<?php

declare(strict_types=1);

namespace App\Livewire\Shopper\Pages\Settings;

use App\Support\SettingMediaPath;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Illuminate\Support\Arr;
use ReflectionProperty;
use Shopper\Livewire\Pages\Settings\General as BaseGeneral;

class General extends BaseGeneral
{
    public function mount(): void
    {
        parent::mount();

        $this->form->fill([
            ...($this->data ?? []),
            'logo' => SettingMediaPath::existingPath($this->data['logo'] ?? null),
            'cover' => SettingMediaPath::existingPath($this->data['cover'] ?? null),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        $schema = parent::form($schema);

        foreach (['logo', 'cover'] as $name) {
            $upload = $schema->getComponent(
                fn (mixed $component): bool => $component instanceof FileUpload
                    && $component->getName() === $name,
            );

            if ($upload instanceof FileUpload) {
                $this->configureSettingFileUpload($upload);
            }
        }

        return $schema;
    }

    public function store(): void
    {
        $this->authorize('access_setting');

        $state = $this->form->getState();
        $state['logo'] = SettingMediaPath::path($this->data['logo'] ?? $state['logo'] ?? null);
        $state['cover'] = SettingMediaPath::path($this->data['cover'] ?? $state['cover'] ?? null);

        $this->saveSettings($state);

        Notification::make()
            ->title(__('shopper::notifications.store_info'))
            ->success()
            ->send();
    }

    protected function configureSettingFileUpload(FileUpload $upload): void
    {
        if ($upload->getName() === 'logo') {
            $this->disableLogoAvatarCrop($upload);
        }

        $upload
            ->image()
            ->maxFiles(1)
            ->visibility('public')
            ->previewable()
            ->fetchFileInformation()
            ->dehydrateStateUsing(fn (FileUpload $component): ?string => SettingMediaPath::path($component->getRawState()))
            ->afterStateUpdated(function (FileUpload $component): void {
                $files = array_filter(
                    Arr::wrap($component->getRawState()),
                    fn (mixed $file): bool => filled($file),
                );

                if (count($files) <= 1) {
                    return;
                }

                $key = array_key_last($files);
                $component->rawState([$key => $files[$key]]);
            });
    }

    protected function disableLogoAvatarCrop(FileUpload $logo): void
    {
        $isAvatar = new ReflectionProperty(FileUpload::class, 'isAvatar');
        $isAvatar->setValue($logo, false);

        $logo
            ->imageAspectRatio(null)
            ->automaticallyCropImagesToAspectRatio(false)
            ->automaticallyResizeImagesMode(null)
            ->automaticallyResizeImagesToHeight(null)
            ->automaticallyResizeImagesToWidth(null)
            ->panelLayout('compact')
            ->loadingIndicatorPosition('right')
            ->removeUploadedFileButtonPosition('left')
            ->uploadButtonPosition('right')
            ->uploadProgressIndicatorPosition('right');
    }
}
