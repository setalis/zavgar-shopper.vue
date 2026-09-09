<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

final class SettingMediaPath
{
    /**
     * @return list<string>
     */
    public static function relativePaths(mixed $value): array
    {
        $paths = [];

        foreach (Arr::wrap($value) as $item) {
            if (is_array($item)) {
                $paths = [...$paths, ...self::relativePaths($item)];

                continue;
            }

            if (! is_string($item) || blank($item)) {
                continue;
            }

            $paths[] = self::toRelativePath($item);
        }

        return array_values(array_filter($paths, fn (string $path): bool => $path !== ''));
    }

    public static function path(mixed $value): ?string
    {
        $paths = self::relativePaths($value);

        if ($paths === []) {
            return null;
        }

        return $paths[array_key_last($paths)];
    }

    public static function existingPath(mixed $value): ?string
    {
        $disk = (string) config('shopper.media.storage.disk_name');

        foreach (array_reverse(self::relativePaths($value)) as $path) {
            if (Storage::disk($disk)->exists($path)) {
                return $path;
            }
        }

        return null;
    }

    private static function toRelativePath(string $path): string
    {
        if (! str_starts_with($path, 'http://') && ! str_starts_with($path, 'https://')) {
            return ltrim($path, '/');
        }

        $disk = (string) config('shopper.media.storage.disk_name');
        $storageUrl = rtrim(Storage::disk($disk)->url(''), '/');

        if (str_starts_with($path, $storageUrl.'/')) {
            return ltrim(substr($path, strlen($storageUrl)), '/');
        }

        $urlPath = parse_url($path, PHP_URL_PATH);

        if (! is_string($urlPath) || $urlPath === '') {
            return $path;
        }

        if (preg_match('#/storage/(.+)$#', $urlPath, $matches) === 1) {
            return $matches[1];
        }

        return $path;
    }
}
