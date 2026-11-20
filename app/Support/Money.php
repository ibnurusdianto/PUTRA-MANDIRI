<?php

namespace App\Support;

/**
 * Helper aritmetika uang berbasis sen (integer) agar perhitungan transaksi
 * tidak memakai floating point.
 */
final class Money
{
    public static function toCents(int|float|string|null $amount): int
    {
        if ($amount === null || $amount === '') {
            return 0;
        }

        return (int) round((float) $amount * 100);
    }

    public static function fromCents(int $cents): string
    {
        return number_format($cents / 100, 2, '.', '');
    }

    public static function percentOf(int $baseCents, int|float|string $percent): int
    {
        return (int) round($baseCents * (float) $percent / 100);
    }

    public static function format(int|float|string|null $amount): string
    {
        return self::formatCents(self::toCents($amount));
    }

    public static function formatCents(int $cents): string
    {
        $sign = $cents < 0 ? '-' : '';
        $absolute = abs($cents);
        $whole = intdiv($absolute, 100);
        $fraction = $absolute % 100;

        $formatted = $sign.'Rp '.number_format($whole, 0, ',', '.');

        if ($fraction !== 0) {
            $formatted .= ','.str_pad((string) $fraction, 2, '0', STR_PAD_LEFT);
        }

        return $formatted;
    }
}
