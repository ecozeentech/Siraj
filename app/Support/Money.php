<?php

namespace App\Support;

final class Money
{
    private const SYMBOLS = ['NGN' => '₦', 'USD' => '$', 'GBP' => '£', 'EUR' => '€'];

    public static function format(int $kobo, string $currency = 'NGN'): string
    {
        $symbol = self::SYMBOLS[$currency] ?? $currency.' ';
        $major = $kobo / 100;

        return $symbol.number_format($major, $kobo % 100 === 0 ? 0 : 2);
    }

    public static function toKobo(float|int|string $amount): int
    {
        return (int) round(((float) $amount) * 100);
    }
}
