<?php

namespace Tests\Unit;

use App\Support\Format;
use Tests\TestCase;

class FormatTest extends TestCase
{
    public function test_rupiah_short_uses_juta_and_miliar(): void
    {
        $this->assertSame('Rp 850 Jt', Format::rupiahShort(850_000_000));
        $this->assertSame('Rp 5,5 Jt', Format::rupiahShort(5_478_034));
        $this->assertSame('Rp 1,25 M', Format::rupiahShort(1_250_000_000));
        $this->assertSame('Rp 1 M', Format::rupiahShort(1_000_000_000));
        $this->assertSame('Rp 2 M', Format::rupiahShort(2_000_000_000));
        $this->assertSame('Rp 500.000', Format::rupiahShort(500_000));
        $this->assertSame('Rp 12 Jt', Format::rupiahShort('12000000'));
    }

    public function test_rab_range_is_the_estimate_plus_minus_a_percentage(): void
    {
        $this->assertSame('Rp 270 Jt – Rp 330 Jt', Format::rabRange(300_000_000, 10));
        $this->assertSame('Rp 540 Jt – Rp 660 Jt', Format::rabRange(600_000_000, 10));
        $this->assertSame('≈ Rp 300 Jt', Format::rabRange(300_000_000, 0));

        config(['concierge.rab_range_percent' => 20]);
        $this->assertSame('Rp 240 Jt – Rp 360 Jt', Format::rabRange(300_000_000));
    }

    public function test_kpr_estimate_uses_the_default_simulation_assumptions(): void
    {
        // 850 jt, DP 20%, bunga 7,5%, tenor 20 tahun -> cicilan sekitar Rp 5,48 jt
        $this->assertEqualsWithDelta(5_478_034, Format::kprMonthly(850_000_000), 5);
        $this->assertSame('DP 20%, tenor 20 th, bunga 7,5%', Format::kprAssumption());
    }
}
