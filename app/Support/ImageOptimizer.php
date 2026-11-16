<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * Re-encodes uploaded PNG/JPEG screenshots as WebP, plus a smaller "-sm"
 * copy for cards and grids. Typically 60–85% smaller than the original PNG.
 * GIFs and SVGs are left alone; any failure keeps the original file.
 */
class ImageOptimizer
{
    public const MAX_WIDTH = 2400;

    public const SMALL_WIDTH = 960;

    public const QUALITY = 82;

    /** Optimise a file on the public disk (or a bundled "images/…" asset) and return its new path. */
    public static function optimize(?string $path): ?string
    {
        if (blank($path) || ! function_exists('imagewebp') || Str::startsWith($path, ['http://', 'https://', '//'])) {
            return $path;
        }

        $absolute = self::absolute($path);
        if (! is_file($absolute) || ! in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), ['png', 'jpg', 'jpeg', 'webp'], true)) {
            return $path;
        }

        try {
            $source = @imagecreatefromstring((string) file_get_contents($absolute));
            if (! $source) {
                return $path;
            }

            $target = preg_replace('/\.[a-z0-9]+$/i', '', $path).'.webp';
            self::write($source, self::absolute($target), self::MAX_WIDTH);
            self::write($source, self::absolute(self::smallPath($target)), self::SMALL_WIDTH);
            imagedestroy($source);

            if ($target !== $path) {
                @unlink($absolute);
            }

            return $target;
        } catch (Throwable $e) {
            Log::warning('Image optimisation failed', ['path' => $path, 'error' => $e->getMessage()]);

            return $path;
        }
    }

    public static function smallPath(string $path): string
    {
        return preg_replace('/\.webp$/i', '-sm.webp', $path);
    }

    private static function write(\GdImage $source, string $destination, int $maxWidth): void
    {
        $width = imagesx($source);
        $image = $width > $maxWidth ? imagescale($source, $maxWidth, -1, IMG_BICUBIC) : $source;
        imagepalettetotruecolor($image);
        imagealphablending($image, true);
        imagesavealpha($image, true);
        imagewebp($image, $destination, self::QUALITY);

        if ($image !== $source) {
            imagedestroy($image);
        }
    }

    private static function absolute(string $path): string
    {
        return Str::startsWith($path, 'images/') ? public_path($path) : Storage::disk('public')->path($path);
    }
}
