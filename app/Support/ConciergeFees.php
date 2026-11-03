<?php

namespace App\Support;

use InvalidArgumentException;

/** Perhitungan komisi platform (rupiah, dibulatkan ke bilangan bulat). */
final class ConciergeFees
{
    public static function propertyPercent(): float
    {
        return (float) config('concierge.fees.property_percent', 2.0);
    }

    /** Management fee properti = persen x harga properti. */
    public static function propertyFee(float $price): float
    {
        return round($price * self::propertyPercent() / 100);
    }

    public static function resolveArchitectPercent(?float $requested): float
    {
        $min = (float) config('concierge.fees.architect_min_percent', 10.0);
        $max = (float) config('concierge.fees.architect_max_percent', 15.0);
        $percent = $requested ?? (float) config('concierge.fees.architect_default_percent', 12.5);

        if ($percent < $min || $percent > $max) {
            throw new InvalidArgumentException("Komisi arsitek harus antara {$min}% dan {$max}%.");
        }

        return $percent;
    }

    public static function architectFee(float $amount, float $percent): float
    {
        return round($amount * $percent / 100);
    }
}
