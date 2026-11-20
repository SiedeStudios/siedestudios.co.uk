<?php

namespace App\Support;

/**
 * Integer minor-unit arithmetic so totals never drift, without requiring bcmath.
 */
class Money
{
    public static function toMinor(string|int|float|null $amount): int
    {
        return (int) round(((float) $amount) * 100);
    }

    public static function fromMinor(int $minor): float
    {
        return $minor / 100;
    }

    public static function lineTotal(string|int|float $quantity, string|int|float $unitPrice): int
    {
        // quantity (2dp) × price (2dp) = 4dp; round back to pennies.
        return (int) round((self::toMinor($quantity) * self::toMinor($unitPrice)) / 100);
    }

    public static function equals(string|int|float|null $a, string|int|float|null $b): bool
    {
        return self::toMinor($a) === self::toMinor($b);
    }
}
