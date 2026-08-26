<?php

declare(strict_types=1);

namespace App\Concerns;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

trait InteractsWithStorefrontMedia
{
    public function initializeInteractsWithStorefrontMedia(): void
    {
        $this->append(['thumbnail', 'images']);
    }

    protected function thumbnail(): Attribute
    {
        return Attribute::get(function (): ?string {
            $media = $this->getFirstMedia(
                (string) config('shopper.media.storage.thumbnail_collection', 'thumbnail'),
            );

            return $media?->getUrl();
        });
    }

    /** @return Attribute<array<int, array{id: int|string, url: string, name: ?string, extension: ?string}>, never> */
    protected function images(): Attribute
    {
        return Attribute::get(function (): array {
            $thumbnailCollection = (string) config('shopper.media.storage.thumbnail_collection', 'thumbnail');
            $uploadsCollection = (string) config('shopper.media.storage.collection_name', 'uploads');
            $thumbnail = $this->getFirstMedia($thumbnailCollection);

            return $this->getMedia($uploadsCollection)
                ->reject(fn (Media $media): bool => $thumbnail !== null && (
                    $media->file_name === $thumbnail->file_name
                    || $media->name === $thumbnail->name
                    || $media->getUrl() === $thumbnail->getUrl()
                ))
                ->map(fn (Media $media): array => [
                    'id' => $media->id,
                    'url' => $media->getUrl(),
                    'name' => $media->name,
                    'extension' => $media->extension,
                ])
                ->values()
                ->all();
        });
    }
}
