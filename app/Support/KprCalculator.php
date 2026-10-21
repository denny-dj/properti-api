<?php

namespace App\Support;

use InvalidArgumentException;

/**
 * Kalkulator KPR metode anuitas (cicilan tetap per bulan).
 * Kelas murni PHP tanpa dependensi framework, sehingga mudah diuji.
 *
 * Mendukung 1 fase (bunga tetap sepanjang tenor) atau 2 fase
 * (bunga tetap N bulan pertama, lalu bunga floating untuk sisa tenor).
 */
class KprCalculator
{
    public function simulate(
        float $propertyPrice,
        float $downPayment,
        float $annualRate,
        int $tenorMonths,
        ?int $fixedMonths = null,
        ?float $floatingRate = null,
        bool $withSchedule = false,
    ): array {
        if ($propertyPrice <= 0 || $tenorMonths < 1) {
            throw new InvalidArgumentException('Harga properti dan tenor harus lebih dari nol.');
        }

        if ($downPayment < 0 || $downPayment >= $propertyPrice) {
            throw new InvalidArgumentException('Uang muka harus lebih kecil dari harga properti.');
        }

        $loan = $propertyPrice - $downPayment;

        $phases = [['rate' => $annualRate, 'months' => $tenorMonths]];

        if ($fixedMonths !== null && $floatingRate !== null && $fixedMonths > 0 && $fixedMonths < $tenorMonths) {
            $phases = [
                ['rate' => $annualRate, 'months' => $fixedMonths],
                ['rate' => $floatingRate, 'months' => $tenorMonths - $fixedMonths],
            ];
        }

        $balance = $loan;
        $month = 0;
        $totalInterest = 0.0;
        $schedule = [];
        $summaries = [];

        foreach ($phases as $index => $phase) {
            $payment = $this->annuity($balance, $phase['rate'], $tenorMonths - $month);
            $monthlyRate = $phase['rate'] / 1200;
            $phaseInterest = 0.0;

            for ($i = 0; $i < $phase['months']; $i++) {
                $month++;
                $interest = $balance * $monthlyRate;
                $principal = $payment - $interest;
                $balance = max(0.0, $balance - $principal);
                $phaseInterest += $interest;

                if ($withSchedule) {
                    $schedule[] = [
                        'month' => $month,
                        'payment' => $this->rp($payment),
                        'principal' => $this->rp($principal),
                        'interest' => $this->rp($interest),
                        'balance' => $this->rp($balance),
                    ];
                }
            }

            $totalInterest += $phaseInterest;
            $summaries[] = [
                'phase' => $index + 1,
                'annual_rate' => $phase['rate'],
                'months' => $phase['months'],
                'monthly_payment' => $this->rp($payment),
                'total_interest' => $this->rp($phaseInterest),
            ];
        }

        $result = [
            'property_price' => $this->rp($propertyPrice),
            'down_payment' => $this->rp($downPayment),
            'down_payment_percent' => round($downPayment / $propertyPrice * 100, 2),
            'loan_amount' => $this->rp($loan),
            'tenor_months' => $tenorMonths,
            'tenor_years' => round($tenorMonths / 12, 2),
            'monthly_payment' => $summaries[0]['monthly_payment'],
            'phases' => $summaries,
            'total_interest' => $this->rp($totalInterest),
            'total_payment' => $this->rp($loan + $totalInterest),          // pokok + bunga
            'total_cost' => $this->rp($downPayment + $loan + $totalInterest), // termasuk uang muka
        ];

        if ($withSchedule) {
            $result['schedule'] = $schedule;
        }

        return $result;
    }

    /** Cicilan anuitas: P*r / (1 - (1+r)^-n). Bunga 0% = pokok dibagi rata. */
    private function annuity(float $principal, float $annualRate, int $months): float
    {
        $r = $annualRate / 1200;

        if ($r == 0.0) {
            return $principal / $months;
        }

        return $principal * $r / (1 - (1 + $r) ** (-$months));
    }

    private function rp(float $value): int
    {
        return (int) round($value);
    }
}
