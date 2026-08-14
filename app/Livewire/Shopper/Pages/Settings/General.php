<?php

declare(strict_types=1);

namespace App\Livewire\Shopper\Pages\Settings;

use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Schema;
use ReflectionProperty;
use Shopper\Livewire\Pages\Settings\General as BaseGeneral;

class General extends BaseGeneral
{
    public function form(Schema $schema): Schema
    {
        $schema = parent::form($schema);

        $logo = $schema->getComponent(
            fn (mixed $component): bool => $component instanceof FileUpload
                && $component->getName() === 'logo',
        );

        if ($logo instanceof FileUpload) {
            $this->disableLogoAvatarCrop($logo);
        }

        return $schema;
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
