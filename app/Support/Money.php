<?php

namespace App\Support;

final class Money
{
    private const SYMBOLS = [
        'NGN' => '₦', 'USD' => '$', 'GBP' => '£', 'EUR' => '€', 'CAD' => 'CA$', 'AUD' => 'A$', 'ZAR' => 'R',
        'KES' => 'KSh ', 'GHS' => 'GH₵', 'INR' => '₹', 'PKR' => 'Rs ', 'BDT' => '৳', 'IDR' => 'Rp ', 'MYR' => 'RM ',
        'SGD' => 'S$', 'TRY' => '₺', 'CNY' => 'CN¥', 'JPY' => '¥', 'BRL' => 'R$', 'SAR' => 'SAR ', 'AED' => 'AED ',
        'EGP' => 'E£', 'CHF' => 'CHF ',
    ];

    public static function format(int $kobo, string $currency = 'NGN'): string
    {
        $symbol = self::SYMBOLS[$currency] ?? $currency.' ';
        $major = $kobo / 100;

        return $symbol.number_format($major, $kobo % 100 === 0 ? 0 : 2);
    }

    /** Local-currency equivalent of an NGN kobo amount. Null when no verified rate is available. */
    public static function local(int $kobo): ?string
    {
        if (app()->runningInConsole() && ! app()->runningUnitTests()) {
            return null;
        }
        $currency = session('currency', 'NGN');
        if ($currency === 'NGN') {
            return null;
        }
        $amount = app(\App\Services\Finance\DisplayCurrency::class)->convertFromKobo($kobo, $currency);
        if ($amount === null) {
            return null;
        }
        $symbol = self::SYMBOLS[$currency] ?? $currency.' ';
        $decimals = in_array($currency, ['JPY', 'KRW'], true) ? 0 : 2;

        return $symbol.number_format($amount, $decimals);
    }

    public static function toKobo(float|int|string $amount): int
    {
        return (int) round(((float) $amount) * 100);
    }
}
