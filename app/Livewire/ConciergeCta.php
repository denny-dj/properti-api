<?php

namespace App\Livewire;

use App\Enums\LeadSource;
use App\Enums\PaymentPreference;
use App\Exceptions\ApiException;
use App\Services\ConciergeRoutingService;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Tombol "Konsultasi via WhatsApp Admin": mencatat prospek lalu membuka WhatsApp admin resmi
 * dengan pesan berisi item dan kode prospek. Dipakai di detail properti dan detail portofolio.
 */
class ConciergeCta extends Component
{
    #[Locked]
    public string $source = '';

    #[Locked]
    public ?int $referenceId = null;

    public bool $open = false;

    public string $name = '';

    public string $phone = '';

    public string $budget = '';

    public string $preference = '';

    public function mount(string $source, ?int $referenceId = null): void
    {
        abort_unless(LeadSource::tryFrom($source), 404);

        $this->source = $source;
        $this->referenceId = $referenceId;

        if ($user = auth()->user()) {
            $this->name = (string) $user->name;
            $this->phone = (string) $user->phone;
        }
    }

    public function openWhatsApp()
    {
        $this->resetErrorBag();

        $key = 'concierge-cta:'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, 8)) {
            $this->addError('cta', 'Terlalu banyak permintaan. Coba lagi sebentar lagi.');

            return;
        }

        RateLimiter::hit($key, 60);

        $this->phone = preg_replace('/[\s\-().]/', '', $this->phone) ?? '';
        $this->budget = preg_replace('/\D/', '', $this->budget) ?? '';

        $this->validate([
            'name' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'regex:/^(\+62|62|0)8[0-9]{8,12}$/'],
            'budget' => ['nullable', 'integer', 'min:0', 'max:99999999999999'],
            'preference' => ['nullable', Rule::enum(PaymentPreference::class)],
        ], [
            'phone.regex' => 'Format nomor WhatsApp tidak valid (contoh: 081234567890).',
        ], [
            'name' => 'nama',
            'phone' => 'nomor WhatsApp',
            'budget' => 'anggaran',
            'preference' => 'rencana pembayaran',
        ]);

        try {
            $result = app(ConciergeRoutingService::class)->generateWhatsAppLink([
                'source' => $this->source,
                'reference_id' => $this->referenceId,
                'customer_name' => $this->name !== '' ? $this->name : null,
                'customer_phone' => $this->phone !== '' ? $this->phone : null,
                'estimated_budget' => $this->budget !== '' ? (int) $this->budget : null,
                'payment_preference' => $this->preference !== '' ? $this->preference : null,
            ]);
        } catch (ApiException $e) {
            $this->addError('cta', $e->getMessage());

            return;
        }

        return $this->redirect($result['wa_url']);
    }

    public function render()
    {
        return view('livewire.concierge-cta', ['preferences' => PaymentPreference::cases()]);
    }
}
