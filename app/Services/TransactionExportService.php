<?php

namespace App\Services;

use App\Enums\PropertyStatus;
use App\Enums\TransactionStatus;
use App\Models\Transaction;
use Generator;
use Illuminate\Database\Eloquent\Builder;

/**
 * Ekspor transaksi ke CSV untuk kertas kerja keuangan.
 *  - properti: kolom A-J sama dengan sheet PROPERTY_TRANSACTIONS (tempel A:J); kolom sesudahnya hanya rujukan.
 *  - desain  : paket desain siap bangun & tanda jadi custom, lengkap dengan komisi dan serah terima.
 */
class TransactionExportService
{
    public const KINDS = ['properti', 'desain'];

    private const PROPERTY_TYPES = ['property_booking_fee'];

    private const DESIGN_TYPES = ['architect_blueprint', 'architect_custom'];

    public function headers(string $kind): array
    {
        if ($kind === 'desain') {
            return [
                'Tanggal Bayar', 'Order ID', 'Nama Pembeli', 'Nama Mitra', 'Item', 'Jenis', 'Nilai Transaksi (Rp)',
                'Komisi Platform (Rp)', 'Hak Bersih Mitra (Rp)', 'Status Serah Terima', 'Tanggal Konfirmasi Pembeli',
                'Metode Bayar', 'Kode Prospek',
            ];
        }

        return [
            'Tanggal Bayar', 'Order ID', 'Nama Buyer', 'Nama Partner', 'Properti', 'Harga Properti (Rp)',
            'Status Penjualan', 'Metode Bayar', 'Booking Fee Masuk (Rp)', 'Pembayaran Lain via Platform (Rp)',
            // rujukan di luar kolom A-J sheet:
            'Komisi Platform Tercatat (Rp)', 'Hak Mitra Tercatat (Rp)', 'Status Transaksi', 'Kode Prospek',
        ];
    }

    public function filename(string $kind, ?string $from, ?string $to): string
    {
        return sprintf('transaksi-%s-%s_%s.csv', $kind, $from ?: 'awal', $to ?: now()->toDateString());
    }

    /** @return Generator<int, array<int, string|int>> */
    public function rows(string $kind, ?string $status, ?string $from, ?string $to): Generator
    {
        $isDesign = $kind === 'desain';
        $latestPaid = $isDesign ? [] : $this->latestPaidByProperty();

        foreach ($this->query($isDesign, $status, $from, $to)->lazyById(500) as $t) {
            yield array_map([$this, 'safe'], $isDesign ? $this->designRow($t) : $this->propertyRow($t, $latestPaid));
        }
    }

    // ------------------------------------------------------------------

    private function query(bool $isDesign, ?string $status, ?string $from, ?string $to): Builder
    {
        return Transaction::query()
            ->whereIn('type', $isDesign ? self::DESIGN_TYPES : self::PROPERTY_TYPES)
            ->whereIn('status', $status ? [$status] : [TransactionStatus::Paid->value, TransactionStatus::Refunded->value])
            ->when($from, fn (Builder $q, string $d) => $q->whereRaw('DATE(COALESCE(paid_at, created_at)) >= ?', [$d]))
            ->when($to, fn (Builder $q, string $d) => $q->whereRaw('DATE(COALESCE(paid_at, created_at)) <= ?', [$d]))
            ->with(array_merge(
                ['payer', 'payee.partnerProfile', 'lead'],
                $isDesign ? ['portfolio'] : ['property' => fn ($q) => $q->withTrashed()],
            ));
    }

    /** @return array<int, int> id properti => id transaksi lunas terbaru */
    private function latestPaidByProperty(): array
    {
        return Transaction::query()
            ->where('type', 'property_booking_fee')
            ->where('status', TransactionStatus::Paid->value)
            ->whereNotNull('property_id')
            ->selectRaw('property_id, MAX(id) as last_id')
            ->groupBy('property_id')
            ->pluck('last_id', 'property_id')
            ->map(fn ($id) => (int) $id)
            ->mapWithKeys(fn ($id, $propertyId) => [(int) $propertyId => $id])
            ->all();
    }

    private function propertyRow(Transaction $t, array $latestPaid): array
    {
        $property = $t->property;

        return [
            $this->date($t),
            $t->code,
            (string) $t->payer?->name,
            $this->partnerName($t),
            $property?->title ?? (string) $t->description,
            $property ? $this->rupiah($property->price) : '',
            $this->salesStatus($t, $property, $latestPaid),
            $this->method($t->payment_type),
            $this->rupiah($t->amount),
            0,
            $this->rupiah($t->platform_fee),
            $this->rupiah($t->net_partner_amount),
            $t->status->value,
            (string) $t->lead?->lead_code,
        ];
    }

    private function designRow(Transaction $t): array
    {
        if ($t->status === TransactionStatus::Refunded) {
            $handover = 'Dikembalikan';
        } elseif ($t->client_confirmed_at) {
            $handover = 'Dikonfirmasi';
        } elseif ($t->deliverable_link) {
            $handover = 'Menunggu konfirmasi pembeli';
        } else {
            $handover = 'Menunggu berkas';
        }

        return [
            $this->date($t),
            $t->code,
            (string) $t->payer?->name,
            $this->partnerName($t),
            $t->portfolio?->title ?? (string) $t->description,
            $t->type->value === 'architect_blueprint' ? 'Desain siap bangun' : 'Custom/survei',
            $this->rupiah($t->amount),
            $this->rupiah($t->platform_fee),
            $this->rupiah($t->net_partner_amount),
            $handover,
            $t->client_confirmed_at?->format('Y-m-d') ?? '',
            $this->method($t->payment_type),
            (string) $t->lead?->lead_code,
        ];
    }

    /**
     * Nilai dropdown sheet: Booking Aktif | Terjual | Batal.
     * "Perlu dicek" bila properti sudah tidak ada. Transaksi lunas yang sudah tergantikan transaksi lain
     * pada properti yang sama dianggap Batal (booking-nya sudah dilepas).
     */
    private function salesStatus(Transaction $t, $property, array $latestPaid): string
    {
        if ($t->status === TransactionStatus::Refunded) {
            return 'Batal';
        }

        if (! $property) {
            return 'Perlu dicek';
        }

        if (($latestPaid[(int) $t->property_id] ?? null) !== (int) $t->id) {
            return 'Batal';
        }

        return match ($property->status) {
            PropertyStatus::Sold => 'Terjual',
            PropertyStatus::Reserved => 'Booking Aktif',
            default => 'Batal',
        };
    }

    /** Sesuai daftar metode di sheet ASUMSI; kosong bila tidak dikenali (isi manual). */
    private function method(?string $paymentType): string
    {
        return match ($paymentType) {
            'bank_transfer', 'echannel' => 'VA',
            'credit_card' => 'Kartu Kredit',
            'gopay', 'shopeepay' => 'E-Wallet',
            'qris' => 'QRIS',
            default => '',
        };
    }

    private function date(Transaction $t): string
    {
        return ($t->paid_at ?? $t->created_at)->format('Y-m-d');
    }

    private function partnerName(Transaction $t): string
    {
        return (string) ($t->payee?->partnerProfile?->business_name ?? $t->payee?->name);
    }

    private function rupiah(mixed $value): int
    {
        return (int) round((float) $value);
    }

    /** Mencegah CSV injection: sel teks yang diawali = + - @ (atau tab/CR) diberi apostrof. */
    private function safe(mixed $value): mixed
    {
        if (! is_string($value) || $value === '') {
            return $value;
        }

        return in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true) ? "'".$value : $value;
    }
}
