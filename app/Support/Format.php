<?php

namespace App\Support;

use App\Services\KprService;

/** Pemformatan angka untuk tampilan ringkas (kartu properti & portofolio). */
final class Format
{
    /** 850000000 -> "Rp 850 Jt"; 1250000000 -> "Rp 1,25 M"; di bawah 1 juta ditulis penuh. */
    public static function rupiahShort(float|int|string $value): string
    {
        $v = (float) $value;

        if ($v >= 1_000_000_000) {
            return 'Rp '.self::trimZeros(number_format($v / 1_000_000_000, 2, ',', '.')).' M';
        }

        if ($v >= 1_000_000) {
            return 'Rp '.self::trimZeros(number_format($v / 1_000_000, 1, ',', '.')).' Jt';
        }

        return 'Rp '.number_format($v, 0, ',', '.');
    }

    /**
     * Rentang estimasi RAB di sekitar angka estimasi, mis. "Rp 270 Jt – Rp 330 Jt".
     * Backend hanya menyimpan satu angka RAB, sehingga rentang = estimasi ± persen (config concierge.rab_range_percent).
     */
    public static function rabRange(float|int|string $rab, ?float $percent = null): string
    {
        $rab = (float) $rab;
        $percent ??= (float) config('concierge.rab_range_percent', 10);

        if ($percent <= 0) {
            return '≈ '.self::rupiahShort($rab);
        }

        return self::rupiahShort($rab * (1 - $percent / 100)).' – '.self::rupiahShort($rab * (1 + $percent / 100));
    }

    /** Cicilan KPR per bulan dengan asumsi bawaan simulasi (DP, bunga, dan tenor di KprService). */
    public static function kprMonthly(float|int|string $price): float
    {
        $principal = (float) $price * (1 - KprService::DEFAULT_DP_PERCENT / 100);
        $rate = KprService::DEFAULT_INTEREST_RATE / 100 / 12;
        $months = KprService::DEFAULT_TENOR_YEARS * 12;

        return $rate > 0 ? $principal * $rate / (1 - (1 + $rate) ** -$months) : $principal / $months;
    }

    public static function kprAssumption(): string
    {
        return 'DP '.KprService::DEFAULT_DP_PERCENT.'%, tenor '.KprService::DEFAULT_TENOR_YEARS.' th, bunga '
            .str_replace('.', ',', (string) KprService::DEFAULT_INTEREST_RATE).'%';
    }

    private static function trimZeros(string $number): string
    {
        return str_contains($number, ',') ? rtrim(rtrim($number, '0'), ',') : $number;
    }
}
