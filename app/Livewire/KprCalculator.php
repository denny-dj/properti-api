<?php

namespace App\Livewire;

use App\Exceptions\ApiException;
use App\Services\KprService;
use Livewire\Component;

/** Kalkulator KPR interaktif (hasil dihitung ulang setiap input berubah). */
class KprCalculator extends Component
{
    public string $price = '';

    public string $dpPercent = '20';

    public string $interest = '7.5';

    public string $tenorYears = '20';

    public bool $useFixed = false;

    public string $fixedYears = '3';

    public string $floatingRate = '11';

    public bool $lockPrice = false;

    public function mount(?float $price = null): void
    {
        if ($price !== null && $price > 0) {
            $this->price = (string) (int) round($price);
            $this->lockPrice = true;
        }
    }

    /** @return array{0: ?array, 1: ?string} [hasil, pesan error] */
    private function simulate(): array
    {
        $price = (float) preg_replace('/\D/', '', $this->price);
        $dp = $this->num($this->dpPercent);
        $rate = $this->num($this->interest);
        $tenor = $this->num($this->tenorYears);

        if ($price <= 0) {
            return [null, null]; // belum diisi
        }

        if ($dp === null || $dp < 0 || $dp > 90) {
            return [null, 'Uang muka harus antara 0% dan 90%.'];
        }

        if ($rate === null || $rate < 0 || $rate > 30) {
            return [null, 'Suku bunga harus antara 0% dan 30% per tahun.'];
        }

        if ($tenor === null || $tenor < 1 || $tenor > 30 || floor($tenor) != $tenor) {
            return [null, 'Tenor harus bilangan bulat 1 sampai 30 tahun.'];
        }

        $data = [
            'property_price' => $price,
            'dp_percent' => $dp,
            'interest_rate' => $rate,
            'tenor_years' => (int) $tenor,
        ];

        if ($this->useFixed) {
            $fixed = $this->num($this->fixedYears);
            $floating = $this->num($this->floatingRate);

            if ($fixed === null || floor($fixed) != $fixed || $fixed < 1 || $fixed >= $tenor) {
                return [null, 'Masa bunga tetap harus bilangan bulat dan lebih kecil dari tenor.'];
            }

            if ($floating === null || $floating < 0 || $floating > 30) {
                return [null, 'Bunga floating harus antara 0% dan 30% per tahun.'];
            }

            $data['fixed_years'] = (int) $fixed;
            $data['floating_rate'] = $floating;
        }

        try {
            return [app(KprService::class)->simulate($data), null];
        } catch (ApiException $e) {
            return [null, $e->getMessage()];
        }
    }

    private function num(string $value): ?float
    {
        $value = str_replace(',', '.', trim($value));

        return is_numeric($value) ? (float) $value : null;
    }

    public function render()
    {
        [$result, $error] = $this->simulate();

        return view('livewire.kpr-calculator', compact('result', 'error'));
    }
}
