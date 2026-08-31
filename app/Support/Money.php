<?php

namespace App\Support;

/**
 * All monetary amounts are stored and calculated in integer cents to avoid
 * floating point rounding issues, and only formatted to a currency string
 * at the very edge (Blade/Inertia props, emails).
 */
class Money
{
    public static function toCents(float|string $amount): int
    {
        return (int) round(((float) $amount) * 100);
    }

    public static function toDecimal(int $cents): float
    {
        return round($cents / 100, 2);
    }

    public static function format(int $cents, ?string $currency = null): string
    {
        $currency ??= Settings::currency();
        $decimals = $cents % 100 === 0 ? 0 : 2;
        $formatted = number_format(static::toDecimal($cents), $decimals);

        return match ($currency) {
            'MXN' => '$'.$formatted.' MXN',
            'USD' => '$'.$formatted.' USD',
            default => $formatted.' '.$currency,
        };
    }
}
