<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Media
{
    /**
     * Resolve a stored path to a public URL. Paths under "images/" are bundled
     * assets in /public; everything else lives on the public storage disk.
     */
    public static function url(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://', '//'])) {
            return $path;
        }

        if (Str::startsWith($path, 'images/')) {
            return asset($path);
        }

        return Storage::disk('public')->url($path);
    }

    /** URL of the smaller copy made by ImageOptimizer, when there is one. */
    public static function small(?string $path): ?string
    {
        if (blank($path) || ! str_ends_with($path, '.webp')) {
            return null;
        }

        $small = ImageOptimizer::smallPath($path);
        $exists = Str::startsWith($small, 'images/') ? is_file(public_path($small)) : Storage::disk('public')->exists($small);

        return $exists ? self::url($small) : null;
    }

    /** srcset for an optimised image ("small 960w, full 2400w"), or null. */
    public static function srcset(?string $path): ?string
    {
        $small = self::small($path);

        return $small ? $small.' '.ImageOptimizer::SMALL_WIDTH.'w, '.self::url($path).' '.ImageOptimizer::MAX_WIDTH.'w' : null;
    }

    public static function delete(?string $path): void
    {
        if (blank($path) || Str::startsWith($path, ['http://', 'https://', '//', 'images/'])) {
            return;
        }

        Storage::disk('public')->delete(array_filter([$path, str_ends_with($path, '.webp') ? ImageOptimizer::smallPath($path) : null]));
    }
}
