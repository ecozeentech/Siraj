<?php

namespace App\Services\Worship;

/**
 * Zakat al-mal on wealth held for a full lunar year (hawl). Rate 2.5% of net zakatable wealth when it reaches the nisab.
 * Nisab is 85 g of gold or 595 g of silver; the user supplies today's price per gram (we do not fabricate market prices).
 */
class ZakatCalculator
{
    public const GOLD_NISAB_GRAMS = 85;

    public const SILVER_NISAB_GRAMS = 595;

    public const RATE = 0.025;

    public const ASSETS = ['cash' => 'Cash in hand', 'bank' => 'Bank balances', 'investments' => 'Shares, funds & investments', 'business' => 'Business inventory & goods for trade', 'receivables' => 'Money owed to you (expected to be repaid)', 'other_assets' => 'Other zakatable assets'];

    public const LIABILITIES = ['debts' => 'Debts due now', 'bills' => 'Bills & dues payable'];

    /** @return array{total_assets: float, total_liabilities: float, net_wealth: float, nisab_value: float, zakat_due: float, meets_nisab: bool} */
    public function calculate(array $in, string $basis, float $goldPrice, float $silverPrice, bool $hawl): array
    {
        $n = fn ($k) => max(0.0, (float) ($in[$k] ?? 0));
        $assets = collect(array_keys(self::ASSETS))->sum(fn ($k) => $n($k)) + $n('gold_grams') * $goldPrice + $n('silver_grams') * $silverPrice;
        $liabilities = collect(array_keys(self::LIABILITIES))->sum(fn ($k) => $n($k));
        $net = max(0.0, $assets - $liabilities);
        $nisab = $basis === 'gold' ? self::GOLD_NISAB_GRAMS * $goldPrice : self::SILVER_NISAB_GRAMS * $silverPrice;
        $meets = $nisab > 0 && $net >= $nisab;
        $due = ($meets && $hawl) ? round($net * self::RATE, 2) : 0.0;

        return [
            'total_assets' => round($assets, 2), 'total_liabilities' => round($liabilities, 2), 'net_wealth' => round($net, 2),
            'nisab_value' => round($nisab, 2), 'zakat_due' => $due, 'meets_nisab' => $meets,
        ];
    }
}
