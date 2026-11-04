<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\ConciergeTransactionRepositoryInterface;
use App\Repositories\Contracts\PartnerPayoutRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/** Saldo mitra dihitung dari transaksi dan pencairan yang dicatat admin. */
class PartnerBalanceService
{
    public function __construct(
        private ConciergeTransactionRepositoryInterface $transactions,
        private PartnerPayoutRepositoryInterface $payouts,
    ) {
    }

    public function orders(User $partner, ?string $stage = null, int $perPage = 10): LengthAwarePaginator
    {
        return $this->transactions->paginateDesignOrders($partner->id, $stage, $perPage);
    }

    public function awaitingCount(User $partner): int
    {
        return $this->transactions->countAwaitingDeliverable($partner->id);
    }

    public function recentPayouts(User $partner, int $limit = 5): Collection
    {
        return $this->payouts->recentFor($partner->id, $limit);
    }

    public function summary(User $partner): array
    {
        $balance = $this->transactions->balanceFor($partner->id);
        $paidOut = $this->payouts->totalFor($partner->id);

        return $balance + [
            'paid_out' => $paidOut,
            'available' => max(0.0, $balance['released'] - $paidOut),
            'note' => 'released = hak bersih yang sudah dikonfirmasi pembeli. paid_out = pencairan yang sudah ditransfer dan dicatat admin. '
                .'available = released dikurangi paid_out (siap dicairkan). pending_release = sudah dibayar, menunggu berkas/konfirmasi pembeli.',
        ];
    }
}
