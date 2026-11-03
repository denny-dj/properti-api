<?php

namespace App\Livewire;

use App\Exceptions\ApiException;
use App\Services\ConciergeRoutingService;
use App\Services\KprService;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Locked;
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

    // ---- Ajukan KPR via bank rekanan (prospek untuk admin) ----
    #[Locked]
    public ?int $propertyId = null;

    public bool $showInterest = false;

    public string $leadName = '';

    public string $leadPhone = '';

    public string $monthlyIncome = '';

    public bool $consent = false;

    /** @var array{code: string, wa_url: string}|null */
    public ?array $kprLead = null;

    public function mount(?float $price = null, ?int $propertyId = null): void
    {
        $this->propertyId = $propertyId;

        if ($user = auth()->user()) {
            $this->leadName = (string) $user->name;
            $this->leadPhone = (string) $user->phone;
        }

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

    /** Mencatat minat KPR sebagai prospek; admin meneruskannya ke bank rekanan. */
    public function submitInterest(): void
    {
        [$result, $error] = $this->simulate();

        if (! $result || $error) {
            $this->addError('leadName', 'Lengkapi simulasi KPR yang valid terlebih dahulu.');

            return;
        }

        if ($result['loan_amount'] < 10_000_000) {
            $this->addError('leadName', 'Plafon pinjaman terlalu kecil untuk diajukan (minimal Rp 10.000.000).');

            return;
        }

        $key = 'kpr-interest:'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('leadName', 'Terlalu banyak permintaan. Coba lagi sebentar lagi.');

            return;
        }

        RateLimiter::hit($key, 60);

        $this->leadPhone = preg_replace('/[\s\-().]/', '', $this->leadPhone) ?? '';
        $this->monthlyIncome = preg_replace('/\D/', '', $this->monthlyIncome) ?? '';

        $this->validate([
            'leadName' => ['required', 'string', 'max:100'],
            'leadPhone' => ['required', 'regex:/^(\+62|62|0)8[0-9]{8,12}$/'],
            'monthlyIncome' => ['required', 'integer', 'min:1000000', 'max:99999999999'],
            'consent' => ['accepted'],
        ], [
            'leadPhone.regex' => 'Format nomor WhatsApp tidak valid (contoh: 081234567890).',
            'consent.accepted' => 'Persetujuan meneruskan data ke bank rekanan wajib diberikan.',
            'monthlyIncome.min' => 'Penghasilan per bulan minimal Rp 1.000.000.',
        ], [
            'leadName' => 'nama',
            'leadPhone' => 'nomor WhatsApp',
            'monthlyIncome' => 'penghasilan per bulan',
        ]);

        try {
            $out = app(ConciergeRoutingService::class)->recordKprInterest([
                'customer_name' => $this->leadName,
                'customer_phone' => $this->leadPhone,
                'loan_amount' => (int) round($result['loan_amount']),
                'monthly_income' => (int) $this->monthlyIncome,
                'tenor_years' => (int) $this->tenorYears,
                'property_id' => $this->propertyId,
            ]);
        } catch (ApiException $e) {
            $this->addError('leadName', $e->getMessage());

            return;
        }

        $this->kprLead = ['code' => $out['lead']->lead_code, 'wa_url' => $out['wa_url']];
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
