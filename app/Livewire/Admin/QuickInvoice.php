<?php

namespace App\Livewire\Admin;

use App\Enums\LeadSource;
use App\Exceptions\ApiException;
use App\Models\ArchitectPortfolio;
use App\Models\Property;
use App\Models\User;
use App\Repositories\Contracts\ConciergeLeadRepositoryInterface;
use App\Services\AdminInvoiceService;
use App\Services\PaymentService;
use App\Support\ConciergeFees;
use App\Support\PhoneNumber;
use Livewire\Component;

/** Cockpit invoice cepat: buat tautan Midtrans Snap untuk dikirim ke pembeli lewat WhatsApp. */
class QuickInvoice extends Component
{
    public string $type = 'property_booking_fee';

    public string $leadCode = '';

    public string $buyerEmail = '';

    public string $itemId = '';

    public string $amount = '';

    public string $commission = '';

    public string $expiresHours = '24';

    public ?array $result = null;

    public ?string $leadInfo = null;

    public function mount(?string $lead = null): void
    {
        if ($lead === null || trim($lead) === '') {
            return;
        }

        $model = app(ConciergeLeadRepositoryInterface::class)->findByCode(trim($lead));

        if (! $model) {
            $this->addError('form', 'Kode prospek tidak ditemukan.');

            return;
        }

        $this->leadCode = $model->lead_code;
        $this->leadInfo = trim(($model->customer_name ?: 'Tanpa nama').' · '.($model->customer_phone ?: 'tanpa nomor'));

        if ($model->reference_id !== null) {
            $this->itemId = (string) $model->reference_id;

            if ($model->source === LeadSource::ArchitectPortfolio) {
                $portfolio = ArchitectPortfolio::find($model->reference_id);
                $this->type = $portfolio && $portfolio->is_ready_to_build ? 'architect_blueprint' : 'architect_custom';
            } else {
                $this->type = 'property_booking_fee';
            }
        }

        // Cocokkan nomor prospek dengan akun pembeli yang sudah terdaftar.
        if ($model->customer_phone) {
            $buyer = User::query()
                ->whereIn('phone', $this->phoneVariants($model->customer_phone))
                ->first();

            if ($buyer) {
                $this->buyerEmail = $buyer->email;
            }
        }
    }

    /** @return array<int, string> */
    private function phoneVariants(string $normalized): array
    {
        $local = '0'.substr($normalized, 2);

        return [$normalized, '+'.$normalized, $local];
    }

    public function updatedType(): void
    {
        $this->itemId = '';
        $this->amount = '';
        $this->result = null;
        $this->resetErrorBag();
    }

    public function createAnother(): void
    {
        $this->reset(['itemId', 'amount', 'commission', 'result', 'buyerEmail', 'leadCode', 'leadInfo']);
        $this->expiresHours = '24';
        $this->resetErrorBag();
    }

    public function submit(): void
    {
        $this->resetErrorBag();
        $this->result = null;
        $this->amount = preg_replace('/\D/', '', $this->amount) ?? '';
        $this->itemId = preg_replace('/\D/', '', $this->itemId) ?? '';
        $this->commission = str_replace(',', '.', trim($this->commission));

        $isProperty = $this->type === 'property_booking_fee';
        $min = (float) config('concierge.fees.architect_min_percent', 10);
        $max = (float) config('concierge.fees.architect_max_percent', 15);

        $this->validate([
            'type' => ['required', 'in:property_booking_fee,architect_blueprint,architect_custom'],
            'buyerEmail' => ['required', 'email'],
            'itemId' => ['required', 'integer', $isProperty ? 'exists:properties,id' : 'exists:architect_portfolios,id'],
            'amount' => [$this->type === 'architect_custom' ? 'required' : 'nullable', 'integer', 'min:10000', 'max:99999999999'],
            'commission' => ['nullable', 'numeric', "min:{$min}", "max:{$max}"],
            'leadCode' => ['nullable', 'string', 'max:24', 'exists:concierge_leads,lead_code'],
            'expiresHours' => ['nullable', 'integer', 'min:1', 'max:72'],
        ], [
            'itemId.exists' => $isProperty ? 'Properti dengan ID tersebut tidak ditemukan.' : 'Portofolio dengan ID tersebut tidak ditemukan.',
            'commission.min' => "Komisi arsitek harus antara {$min}% dan {$max}%.",
            'commission.max' => "Komisi arsitek harus antara {$min}% dan {$max}%.",
            'amount.required' => 'Nominal tanda jadi wajib diisi untuk jalur custom.',
        ], [
            'buyerEmail' => 'email pembeli',
            'itemId' => $isProperty ? 'ID properti' : 'ID portofolio',
            'amount' => 'nominal',
            'commission' => 'komisi',
            'leadCode' => 'kode prospek',
            'expiresHours' => 'masa berlaku',
        ]);

        $data = [
            'type' => $this->type,
            'buyer_email' => trim($this->buyerEmail),
            $isProperty ? 'property_id' : 'portfolio_id' => (int) $this->itemId,
            'lead_code' => $this->leadCode !== '' ? $this->leadCode : null,
            'amount' => $this->amount !== '' ? (int) $this->amount : null,
            'commission_percent' => $this->commission !== '' ? (float) $this->commission : null,
            'expires_in_hours' => $this->expiresHours !== '' ? (int) $this->expiresHours : null,
        ];

        try {
            $out = app(AdminInvoiceService::class)->createQuickBooking(auth()->user(), array_filter($data, fn ($v) => $v !== null));
        } catch (ApiException $e) {
            $this->addError('form', $e->getMessage());

            return;
        }

        $tx = $out['transaction']->load(['payer', 'payee.partnerProfile']);
        $buyerPhone = PhoneNumber::normalize($tx->payer?->phone);

        $this->result = [
            'code' => $tx->code,
            'description' => $tx->description,
            'amount' => (float) $tx->amount,
            'platform_fee' => (float) $tx->platform_fee,
            'net_partner_amount' => (float) $tx->net_partner_amount,
            'commission_percent' => $out['commission_percent'],
            'fee_basis' => $out['fee_basis'],
            'payment_url' => $tx->redirect_url,
            'expires_at' => $tx->expires_at?->format('d/m/Y H:i'),
            'buyer_name' => $tx->payer?->name,
            'partner_name' => $tx->payee?->partnerProfile?->business_name ?? $tx->payee?->name,
            'wa_message' => $out['wa_message'],
            'wa_url' => 'https://wa.me/'.($buyerPhone ?? '').'?text='.rawurlencode($out['wa_message']),
            'reused' => $out['reused'],
        ];
    }

    /** Pratinjau item + hitungan komisi sebelum invoice dibuat. */
    private function preview(): ?array
    {
        $id = (int) preg_replace('/\D/', '', $this->itemId);

        if ($id < 1) {
            return null;
        }

        if ($this->type === 'property_booking_fee') {
            $property = Property::with('partner.partnerProfile')->find($id);

            if (! $property) {
                return null;
            }

            $amount = $this->amount !== '' ? (float) preg_replace('/\D/', '', $this->amount) : app(PaymentService::class)->bookingFeeFor($property);
            $fee = ConciergeFees::propertyFee((float) $property->price);

            return [
                'title' => $property->title,
                'owner' => $property->partner?->partnerProfile?->business_name ?? $property->partner?->name,
                'status' => $property->status->value,
                'lines' => [
                    ['Harga properti', (float) $property->price],
                    ['Booking fee yang ditagih', $amount],
                    ['Management fee platform ('.ConciergeFees::propertyPercent().'% dari harga)', $fee],
                    ['Hak bersih mitra dari booking fee', max(0.0, $amount - $fee)],
                ],
                'warning' => $fee > $amount ? 'Management fee lebih besar dari booking fee. Selisihnya ditagih saat akad.' : null,
            ];
        }

        $portfolio = ArchitectPortfolio::with('architect.partnerProfile')->find($id);

        if (! $portfolio) {
            return null;
        }

        $percent = $this->commission !== '' && is_numeric(str_replace(',', '.', $this->commission))
            ? (float) str_replace(',', '.', $this->commission)
            : (float) config('concierge.fees.architect_default_percent', 12.5);

        $amount = $this->type === 'architect_blueprint'
            ? (float) ($portfolio->blueprint_price ?? 0)
            : (float) preg_replace('/\D/', '', $this->amount);

        $fee = ConciergeFees::architectFee($amount, $percent);

        return [
            'title' => $portfolio->title,
            'owner' => $portfolio->architect?->partnerProfile?->business_name ?? $portfolio->architect?->name,
            'status' => $portfolio->status->value,
            'lines' => [
                ['Estimasi RAB (informasi)', (float) $portfolio->estimated_rab],
                [$this->type === 'architect_blueprint' ? 'Harga paket desain' : 'Nominal tanda jadi', $amount],
                ["Komisi platform ({$percent}%)", $fee],
                ['Hak bersih arsitek', $amount - $fee],
            ],
            'warning' => $this->type === 'architect_blueprint' && ! $portfolio->is_ready_to_build
                ? 'Portofolio ini tidak menyediakan paket siap bangun. Gunakan jalur custom.' : null,
        ];
    }

    public function render()
    {
        return view('livewire.admin.quick-invoice', ['preview' => $this->preview()]);
    }
}
