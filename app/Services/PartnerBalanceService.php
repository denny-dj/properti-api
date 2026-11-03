<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\ConciergeTransactionRepositoryInterface;

/** Saldo mitra dihitung dari transaksi (tanpa tabel ledger terpisah). */
class PartnerBalanceService
{
    public function __construct(private ConciergeTransactionRepositoryInterface $transactions)
    {
    }

    public function summary(User $partner): array
    {
        $balance = $this->transactions->balanceFor($partner->id);

        return $balance + [
            'note' => 'released = hak bersih mitra yang sudah dikonfirmasi pembeli (siap dicairkan oleh admin). '
                .'pending_release = sudah dibayar tetapi menunggu berkas/konfirmasi pembeli. Pencairan tercatat terpisah oleh admin.',
        ];
    }
}
