<?php

namespace App\Repositories\Contracts;

use App\Models\Transaction;

/** Kueri khusus transaksi concierge (invoice portofolio, saldo mitra, pendapatan). */
interface ConciergeTransactionRepositoryInterface
{
    /** Invoice pending (belum kedaluwarsa) milik pembeli untuk portofolio & jenis tertentu. */
    public function activePendingForPortfolio(int $buyerId, int $portfolioId, string $type): ?Transaction;

    /** Ada tagihan pending atau dana lunas yang belum dikonfirmasi pembeli. */
    public function hasOpenTransactions(int $portfolioId): bool;

    /** @return array{released: float, pending_release: float, transactions: int} */
    public function balanceFor(int $partnerId): array;

    /** @return array<int, array{type: string, transactions: int, gross: float, platform_fee: float, net_partner_amount: float}> */
    public function revenueByType(?string $from, ?string $to): array;

    /** @return array{pending_release: float, released: float} */
    public function escrowTotals(?string $from, ?string $to): array;
}
