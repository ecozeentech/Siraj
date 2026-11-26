<?php

namespace Tests\Unit;

use App\Services\Worship\ZakatCalculator;
use Tests\TestCase;

class ZakatCalculatorTest extends TestCase
{
    public function test_zakat_is_two_and_a_half_percent_above_nisab_after_a_hawl(): void
    {
        $calc = new ZakatCalculator;
        $result = $calc->calculate(['cash' => 1_000_000, 'debts' => 100_000], 'gold', 1_000, 10, true);

        $this->assertSame(1_000_000.0, $result['total_assets']);
        $this->assertSame(900_000.0, $result['net_wealth']);
        $this->assertTrue($result['meets_nisab']);
        $this->assertSame(22_500.0, $result['zakat_due']);
    }

    public function test_nothing_is_due_without_a_full_year_or_below_nisab(): void
    {
        $calc = new ZakatCalculator;
        $below = $calc->calculate(['cash' => 1000], 'gold', 1_000, 10, true);
        $noYear = $calc->calculate(['cash' => 10_000_000], 'gold', 1_000, 10, false);

        $this->assertFalse($below['meets_nisab']);
        $this->assertSame(0.0, $below['zakat_due']);
        $this->assertTrue($noYear['meets_nisab']);
        $this->assertSame(0.0, $noYear['zakat_due']);
    }
}
