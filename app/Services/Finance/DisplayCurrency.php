<?php

namespace App\Services\Finance;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Shows a visitor's local currency using their IP country and a cached mid-market rate.
 * Wallet balances stay in naira kobo. If the rate service is unavailable, the amount stays in NGN.
 */
class DisplayCurrency
{
    /** @var array<string, string> */
    private const COUNTRY = [
        'NG' => 'NGN', 'US' => 'USD', 'GB' => 'GBP', 'IE' => 'EUR', 'FR' => 'EUR', 'DE' => 'EUR', 'ES' => 'EUR', 'IT' => 'EUR', 'PT' => 'EUR', 'NL' => 'EUR', 'BE' => 'EUR', 'AT' => 'EUR', 'FI' => 'EUR',
        'CA' => 'CAD', 'AU' => 'AUD', 'NZ' => 'NZD', 'ZA' => 'ZAR', 'KE' => 'KES', 'GH' => 'GHS', 'EG' => 'EGP', 'MA' => 'MAD', 'SA' => 'SAR', 'AE' => 'AED', 'QA' => 'QAR', 'KW' => 'KWD',
        'IN' => 'INR', 'PK' => 'PKR', 'BD' => 'BDT', 'ID' => 'IDR', 'MY' => 'MYR', 'SG' => 'SGD', 'TR' => 'TRY', 'CN' => 'CNY', 'JP' => 'JPY', 'KR' => 'KRW', 'BR' => 'BRL', 'MX' => 'MXN',
        'RU' => 'RUB', 'CH' => 'CHF', 'SE' => 'SEK', 'NO' => 'NOK', 'DK' => 'DKK', 'PL' => 'PLN',
    ];

    public function detect(Request $request): string
    {
        $ip = $request->ip();
        if (! $ip || $this->isLocal($ip)) {
            return 'NGN';
        }

        $country = Cache::remember('geo-country:'.$ip, now()->addDay(), function () use ($ip) {
            try {
                $res = Http::timeout(4)->acceptJson()->get('https://ipwho.is/'.$ip);
                $code = strtoupper((string) $res->json('country_code'));

                return strlen($code) === 2 ? $code : 'NG';
            } catch (Throwable) {
                return 'NG';
            }
        });

        return self::COUNTRY[$country] ?? 'NGN';
    }

    /** Naira per 1 unit of the target currency is inverted: this returns target units per 1 NGN. */
    public function rate(string $currency): ?float
    {
        $currency = strtoupper($currency);
        if ($currency === 'NGN') {
            return 1.0;
        }
        $rates = Cache::remember('fx:ngn', now()->addHours(12), function () {
            try {
                $res = Http::timeout(6)->acceptJson()->get('https://open.er-api.com/v6/latest/NGN');
                $rates = $res->json('rates');

                return is_array($rates) && $res->json('result') === 'success' ? $rates : [];
            } catch (Throwable) {
                return [];
            }
        });
        $rate = $rates[$currency] ?? null;

        return is_numeric($rate) && (float) $rate > 0 ? (float) $rate : null;
    }

    public function convertFromKobo(int $kobo, string $currency): ?float
    {
        $rate = $this->rate($currency);

        return $rate === null ? null : ($kobo / 100) * $rate;
    }

    private function isLocal(string $ip): bool
    {
        return ! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
    }
}
