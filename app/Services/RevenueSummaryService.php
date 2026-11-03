<?php

namespace App\Services;

use App\Repositories\Contracts\ConciergeTransactionRepositoryInterface;

class RevenueSummaryService
{
    private const LABELS = [
        'property_booking_fee' => 'Booking fee properti (management fee 2% dari harga)',
        'architect_blueprint' => 'Arsitek: paket desain siap bangun (komisi 10-15%)',
        'architect_custom' => 'Arsitek: tanda jadi desain custom (komisi 10-15%)',
        'architect_booking' => 'Konsultasi arsitek (model lama, tanpa komisi tercatat)',
    ];

    public function __construct(private ConciergeTransactionRepositoryInterface $transactions)
    {
    }

    public function summary(?string $from, ?string $to): array
    {
        $rows = collect($this->transactions->revenueByType($from, $to))
            ->map(fn (array $r) => ['label' => self::LABELS[$r['type']] ?? $r['type']] + $r)
            ->values();

        return [
            'period' => ['from' => $from, 'to' => $to],
            'totals' => [
                'transactions' => (int) $rows->sum('transactions'),
                'gross' => (float) $rows->sum('gross'),
                'platform_fee' => (float) $rows->sum('platform_fee'),
                'net_partner_amount' => (float) $rows->sum('net_partner_amount'),
            ],
            'by_type' => $rows->all(),
            'architect_escrow' => $this->transactions->escrowTotals($from, $to),
            'notes' => [
                'Hanya transaksi berstatus lunas (paid) dalam periode berdasarkan tanggal pembayaran.',
                'platform_fee properti = 2% dari HARGA properti (hak komisi), dapat melebihi booking fee; selisihnya ditagih saat akad.',
                'Transaksi sebelum fitur concierge tidak memiliki platform_fee tercatat (bernilai 0).',
            ],
        ];
    }
}
