<?php

namespace Tests\Unit;

use App\Support\KprCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class KprCalculatorTest extends TestCase
{
    public function test_standard_annuity_matches_reference_value(): void
    {
        // Pinjaman 500 jt, bunga 10%/thn, 20 thn -> cicilan referensi PMT = 4.825.108
        $r = (new KprCalculator())->simulate(625_000_000, 125_000_000, 10.0, 240);

        $this->assertSame(500_000_000, $r['loan_amount']);
        $this->assertSame(4_825_108, $r['monthly_payment']);
        $this->assertSame(20.0, $r['down_payment_percent']);
        $this->assertCount(1, $r['phases']);
        $this->assertArrayNotHasKey('schedule', $r);
    }

    public function test_zero_interest_splits_principal_evenly(): void
    {
        $r = (new KprCalculator())->simulate(150_000_000, 30_000_000, 0.0, 120);

        $this->assertSame(1_000_000, $r['monthly_payment']);
        $this->assertSame(0, $r['total_interest']);
        $this->assertSame(120_000_000, $r['total_payment']);
    }

    public function test_schedule_ends_with_zero_balance(): void
    {
        $r = (new KprCalculator())->simulate(625_000_000, 125_000_000, 10.0, 240, withSchedule: true);

        $this->assertCount(240, $r['schedule']);
        $this->assertSame(0, $r['schedule'][239]['balance']);
        $this->assertSame(1, $r['schedule'][0]['month']);
    }

    public function test_fixed_then_floating_has_two_phases_and_higher_second_payment(): void
    {
        $r = (new KprCalculator())->simulate(
            propertyPrice: 1_000_000_000,
            downPayment: 200_000_000,
            annualRate: 7.0,
            tenorMonths: 180,
            fixedMonths: 36,
            floatingRate: 11.0,
            withSchedule: true,
        );

        $this->assertCount(2, $r['phases']);
        $this->assertSame(36 + 144, $r['phases'][0]['months'] + $r['phases'][1]['months']);
        $this->assertGreaterThan($r['phases'][0]['monthly_payment'], $r['phases'][1]['monthly_payment']);
        $this->assertSame(0, $r['schedule'][179]['balance']);
        $this->assertSame($r['phases'][0]['monthly_payment'], $r['monthly_payment']);
    }

    public function test_down_payment_must_be_smaller_than_price(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new KprCalculator())->simulate(500_000_000, 500_000_000, 8.0, 120);
    }
}
