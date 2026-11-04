<?php

namespace App\Repositories\Contracts;

use App\Models\Transaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/** Kueri khusus transaksi concierge (invoice portofolio, saldo mitra, pendapatan). */
interface ConciergeTransactionRepositoryInterface
{
    /** Invoice pending (belum kedaluwarsa) milik pembeli untuk portofolio & jenis tertentu. */
    public function activePendingForPortfolio(int $buyerId, int $portfolioId, string $type): ?Transaction;

    /** Ada tagihan pending atau dana lunas yang belum dikonfirmasi pembeli. */
    public function hasOpenTransactions(int $portfolioId): bool;

    /**
     * Pesanan desain (blueprint & custom) milik mitra.
     * Tahap: unpaid | awaiting_link | awaiting_confirmation | released | null (semua).
     */
    public function paginateDesignOrders(int $partnerId, ?string $stage = null, int $perPage = 10): LengthAwarePaginator;

    /** Pesanan lunas yang tautan berkasnya belum dikirim mitra. */
    public function countAwaitingDeliverable(int $partnerId): int;

    /** @return array<int, array{released: float, pending_release: float}> saldo bruto per id mitra */
    public function balancesByPartner(): array;

    /** @return array{released: float, pending_release: float, transactions: int} */
    public function balanceFor(int $partnerId): array;

    /** @return array<int, array{type: string, transactions: int, gross: float, platform_fee: float, net_partner_amount: float}> */
    public function revenueByType(?string $from, ?string $to): array;

    /** @return array{pending_release: float, released: float} */
    public function escrowTotals(?string $from, ?string $to): array;
}
