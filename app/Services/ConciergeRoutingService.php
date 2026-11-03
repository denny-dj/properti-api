<?php

namespace App\Services;

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Enums\PaymentPreference;
use App\Enums\PortfolioStatus;
use App\Enums\PropertyStatus;
use App\Exceptions\ApiException;
use App\Models\ConciergeLead;
use App\Repositories\Contracts\ArchitectPortfolioRepositoryInterface;
use App\Repositories\Contracts\ConciergeLeadRepositoryInterface;
use App\Repositories\Contracts\PropertyRepositoryInterface;
use App\Support\PhoneNumber;
use Illuminate\Support\Str;

/** Garda depan: setiap minat dicatat sebagai prospek, lalu diarahkan ke WhatsApp admin resmi. */
class ConciergeRoutingService
{
    public function __construct(
        private ConciergeLeadRepositoryInterface $leads,
        private PropertyRepositoryInterface $properties,
        private ArchitectPortfolioRepositoryInterface $portfolios,
    ) {
    }

    /**
     * @param  array{source: string, reference_id?: ?int, customer_name?: ?string, customer_phone?: ?string, estimated_budget?: ?int, payment_preference?: ?string, note?: ?string}  $data
     * @return array{lead: ConciergeLead, wa_url: string, message: string, created: bool}
     */
    public function generateWhatsAppLink(array $data): array
    {
        $number = $this->adminNumber();
        $source = LeadSource::from($data['source']);
        $referenceId = isset($data['reference_id']) ? (int) $data['reference_id'] : null;
        $item = $this->resolveItem($source, $referenceId);
        $phone = PhoneNumber::normalize($data['customer_phone'] ?? null);

        $duplicate = $phone ? $this->leads->findRecentDuplicate($source->value, $referenceId, $phone) : null;

        if ($duplicate) {
            $lead = $duplicate;
            $created = false;
        } else {
            $lead = $this->createLead([
                'source' => $source->value,
                'reference_id' => $referenceId,
                'customer_name' => $this->clean($data['customer_name'] ?? null),
                'customer_phone' => $phone,
                'estimated_budget' => isset($data['estimated_budget']) ? (int) $data['estimated_budget'] : null,
                'payment_preference' => $data['payment_preference'] ?? null,
                'notes' => $this->composeNotes($item, $data['note'] ?? null),
            ]);
            $created = true;
        }

        $message = $this->buildMessage($lead, $item);

        return [
            'lead' => $lead,
            'wa_url' => 'https://wa.me/'.$number.'?text='.rawurlencode($message),
            'message' => $message,
            'created' => $created,
        ];
    }

    /**
     * Minat KPR dari kalkulator: dicatat sebagai prospek untuk diteruskan admin ke bank rekanan.
     *
     * @param  array{customer_name: string, customer_phone: string, loan_amount: int, monthly_income: int, tenor_years?: ?int, property_id?: ?int}  $data
     * @return array{lead: ConciergeLead, wa_url: string, message: string, created: bool}
     */
    public function recordKprInterest(array $data): array
    {
        $number = $this->adminNumber();
        $propertyId = isset($data['property_id']) ? (int) $data['property_id'] : null;
        $item = $this->resolveItem(LeadSource::KprCalculator, $propertyId);
        $phone = PhoneNumber::normalize($data['customer_phone']);

        $duplicate = $phone ? $this->leads->findRecentDuplicate(LeadSource::KprCalculator->value, $propertyId, $phone) : null;

        if ($duplicate) {
            $lead = $duplicate;
            $created = false;
        } else {
            $lines = array_filter([
                'Minat KPR (dari kalkulator)',
                $item ? "Item: {$item['kind']} {$item['code']} - {$item['title']}" : null,
                'Plafon diminta: '.self::rp($data['loan_amount']),
                'Penghasilan/bulan: '.self::rp($data['monthly_income']),
                isset($data['tenor_years']) ? 'Tenor: '.$data['tenor_years'].' tahun' : null,
                'Persetujuan data diteruskan ke bank rekanan: Ya ('.now()->format('d/m/Y H:i').')',
            ]);

            $lead = $this->createLead([
                'source' => LeadSource::KprCalculator->value,
                'reference_id' => $propertyId,
                'customer_name' => $this->clean($data['customer_name']),
                'customer_phone' => $phone,
                'estimated_budget' => (int) $data['loan_amount'],
                'payment_preference' => PaymentPreference::Kpr->value,
                'notes' => implode("\n", $lines),
            ]);
            $created = true;
        }

        $message = $this->buildMessage($lead, $item);

        return [
            'lead' => $lead,
            'wa_url' => 'https://wa.me/'.$number.'?text='.rawurlencode($message),
            'message' => $message,
            'created' => $created,
        ];
    }

    // ------------------------------------------------------------------

    private function adminNumber(): string
    {
        $digits = preg_replace('/\D+/', '', (string) config('concierge.admin_wa_number')) ?? '';

        if (! preg_match('/^62\d{8,13}$/', $digits)) {
            throw new ApiException('Nomor WhatsApp admin belum dikonfigurasi.', 503);
        }

        return $digits;
    }

    /** @return array{kind: string, code: string, title: string, price_label: string}|null */
    private function resolveItem(LeadSource $source, ?int $id): ?array
    {
        return match ($source) {
            LeadSource::PropertyDetail => $this->propertyItem($id ?? throw new ApiException('reference_id properti wajib diisi.', 422)),
            LeadSource::ArchitectPortfolio => $this->portfolioItem($id ?? throw new ApiException('reference_id portofolio wajib diisi.', 422)),
            LeadSource::KprCalculator => $id !== null ? $this->propertyItem($id) : null,
        };
    }

    private function propertyItem(int $id): array
    {
        $property = $this->properties->find($id);

        if (! $property || $property->status !== PropertyStatus::Published || ! $property->partner?->isActive()) {
            throw new ApiException('Properti tidak ditemukan.', 404);
        }

        return [
            'kind' => 'Properti',
            'code' => 'PROP-'.$property->id,
            'title' => $property->title,
            'price_label' => 'Harga: '.self::rp($property->price),
        ];
    }

    private function portfolioItem(int $id): array
    {
        $portfolio = $this->portfolios->find($id);

        if (! $portfolio || $portfolio->status !== PortfolioStatus::Published || ! $portfolio->architect?->isActive()) {
            throw new ApiException('Portofolio tidak ditemukan.', 404);
        }

        $label = 'Estimasi RAB: '.self::rp($portfolio->estimated_rab).' (±'.self::rp($portfolio->cost_per_sqm).'/m²)';

        if ($portfolio->is_ready_to_build && $portfolio->blueprint_price) {
            $label .= "\nPaket desain siap bangun: ".self::rp($portfolio->blueprint_price);
        }

        return ['kind' => 'Portofolio arsitek', 'code' => 'ARS-'.$portfolio->id, 'title' => $portfolio->title, 'price_label' => $label];
    }

    private function composeNotes(?array $item, ?string $note): ?string
    {
        $lines = array_filter([
            $item ? "Item: {$item['kind']} {$item['code']} - {$item['title']}" : null,
            $item['price_label'] ?? null,
            $this->clean($note),
        ]);

        return $lines === [] ? null : implode("\n", $lines);
    }

    private function buildMessage(ConciergeLead $lead, ?array $item): string
    {
        $lines = ['Halo Admin '.config('app.name').', saya ingin berkonsultasi:'];

        if ($item) {
            $lines[] = "{$item['kind']}: {$item['title']} ({$item['code']})";
            $lines[] = $item['price_label'];
        } else {
            $lines[] = 'Topik: Simulasi KPR';
        }

        if ($lead->customer_name) {
            $lines[] = 'Nama: '.$lead->customer_name;
        }

        if ($lead->estimated_budget) {
            $lines[] = 'Anggaran/plafon: '.self::rp($lead->estimated_budget);
        }

        if ($lead->payment_preference) {
            $lines[] = 'Rencana pembayaran: '.$lead->payment_preference->label();
        }

        $lines[] = 'Kode prospek: '.$lead->lead_code;
        $lines[] = 'Mohon dibantu informasi dan jadwal konsultasi/survei.';

        return implode("\n", $lines);
    }

    private function createLead(array $attributes): ConciergeLead
    {
        /** @var ConciergeLead $lead */
        $lead = $this->leads->create($attributes + [
            'lead_code' => $this->generateCode(),
            'status' => LeadStatus::New->value,
        ]);

        return $lead;
    }

    private function generateCode(): string
    {
        do {
            $code = config('concierge.lead_prefix', 'LEAD').'-'.now()->format('Y').'-'.strtoupper(Str::random(4));
        } while ($this->leads->codeExists($code));

        return $code;
    }

    private function clean(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private static function rp(int|float|string $value): string
    {
        return 'Rp '.number_format((float) $value, 0, ',', '.');
    }
}
