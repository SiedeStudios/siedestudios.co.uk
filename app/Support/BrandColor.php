<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * Picks a project's brand colour from its cover image and keeps any colour
 * readable as an accent on the dark theme (enough saturation, mid lightness).
 */
class BrandColor
{
    /** Clamp a colour into a range that works for buttons, links and glows on #121214. */
    public static function normalize(?string $hex): ?string
    {
        if (! is_string($hex) || ! preg_match('/^#?([0-9a-f]{6})$/i', $hex, $m)) {
            return null;
        }

        [$h, $s, $l] = self::toHsl(hexdec(substr($m[1], 0, 2)), hexdec(substr($m[1], 2, 2)), hexdec(substr($m[1], 4, 2)));

        return self::fromHsl($h, max($s, 0.55), min(max($l, 0.45), 0.62));
    }

    /** The most prominent vivid colour in an image, or null if it's mostly grey. */
    public static function fromImage(?string $path): ?string
    {
        $file = self::absolute($path);
        if (! $file || ! is_file($file)) {
            return null;
        }

        try {
            $src = @imagecreatefromstring((string) file_get_contents($file));
            if (! $src) {
                return null;
            }
            $img = imagescale($src, 64, -1, IMG_BILINEAR_FIXED);
            imagedestroy($src);

            // Weighted hue histogram: vivid, mid-bright pixels count most.
            $buckets = [];
            for ($y = 0, $hgt = imagesy($img); $y < $hgt; $y++) {
                for ($x = 0; $x < 64; $x++) {
                    $c = imagecolorat($img, $x, $y);
                    [$h, $s, $l] = self::toHsl(($c >> 16) & 0xFF, ($c >> 8) & 0xFF, $c & 0xFF);
                    if ($s < 0.35 || $l < 0.18 || $l > 0.85) {
                        continue;
                    }
                    $weight = $s * (1 - abs($l - 0.5) * 2);
                    $b = (int) floor($h * 18) % 18;
                    $buckets[$b]['w'] = ($buckets[$b]['w'] ?? 0) + $weight;
                    $buckets[$b]['x'] = ($buckets[$b]['x'] ?? 0) + cos($h * 2 * M_PI) * $weight;
                    $buckets[$b]['y'] = ($buckets[$b]['y'] ?? 0) + sin($h * 2 * M_PI) * $weight;
                    $buckets[$b]['s'] = ($buckets[$b]['s'] ?? 0) + $s * $weight;
                }
            }
            imagedestroy($img);

            if (! $buckets) {
                return null;
            }

            uasort($buckets, fn ($a, $b) => $b['w'] <=> $a['w']);
            $top = reset($buckets);
            // Too little colour overall (e.g. a grey dashboard screenshot): no tint.
            if ($top['w'] < 6) {
                return null;
            }
            $hue = fmod(atan2($top['y'], $top['x']) / (2 * M_PI) + 1, 1);

            return self::fromHsl($hue, min(0.85, max(0.6, $top['s'] / $top['w'])), 0.55);
        } catch (Throwable) {
            return null;
        }
    }

    private static function absolute(?string $path): ?string
    {
        if (blank($path) || Str::startsWith($path, ['http://', 'https://', '//'])) {
            return null;
        }

        return Str::startsWith($path, 'images/') ? public_path($path) : Storage::disk('public')->path($path);
    }

    /** @return array{0: float, 1: float, 2: float} hue 0-1, saturation 0-1, lightness 0-1 */
    private static function toHsl(int $r, int $g, int $b): array
    {
        $r /= 255;
        $g /= 255;
        $b /= 255;
        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $l = ($max + $min) / 2;
        if ($max === $min) {
            return [0.0, 0.0, $l];
        }
        $d = $max - $min;
        $s = $l > 0.5 ? $d / (2 - $max - $min) : $d / ($max + $min);
        $h = match ($max) {
            $r => ($g - $b) / $d + ($g < $b ? 6 : 0),
            $g => ($b - $r) / $d + 2,
            default => ($r - $g) / $d + 4,
        };

        return [$h / 6, $s, $l];
    }

    private static function fromHsl(float $h, float $s, float $l): string
    {
        $q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
        $p = 2 * $l - $q;
        $channel = function (float $t) use ($p, $q): int {
            $t = fmod($t + 1, 1);
            $v = match (true) {
                $t < 1 / 6 => $p + ($q - $p) * 6 * $t,
                $t < 1 / 2 => $q,
                $t < 2 / 3 => $p + ($q - $p) * (2 / 3 - $t) * 6,
                default => $p,
            };

            return (int) round($v * 255);
        };

        return sprintf('#%02X%02X%02X', $channel($h + 1 / 3), $channel($h), $channel($h - 1 / 3));
    }
}
