<?php

namespace App\Repositories\Eloquent;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Repositories\Contracts\ConciergeTransactionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ConciergeTransactionRepository implements ConciergeTransactionRepositoryInterface
{
    /** Jenis transaksi dengan serah terima berkas (dana dilepas setelah konfirmasi pembeli). */
    public const DELIVERABLE_TYPES = ['architect_blueprint', 'architect_custom'];

    public function activePendingForPortfolio(int $buyerId, int $portfolioId, string $type): ?Transaction
    {
        return Transaction::query()
            ->where('user_id', $buyerId)
            ->where('portfolio_id', $portfolioId)
            ->where('type', $type)
            ->where('status', TransactionStatus::Pending->value)
            ->where(fn (Builder $q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->latest('id')
            ->first();
    }

    public function paginateDesignOrders(int $partnerId, ?string $stage = null, int $perPage = 10): LengthAwarePaginator
    {
        $query = Transaction::query()
            ->with(['payer', 'portfolio'])
            ->where('payee_id', $partnerId)
            ->whereIn('type', self::DELIVERABLE_TYPES);

        match ($stage) {
            'unpaid' => $query->where('status', TransactionStatus::Pending->value)
                ->where(fn (Builder $q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now())),
            'awaiting_link' => $query->where('status', TransactionStatus::Paid->value)->whereNull('deliverable_link'),
            'awaiting_confirmation' => $query->where('status', TransactionStatus::Paid->value)
                ->whereNotNull('deliverable_link')->whereNull('client_confirmed_at'),
            'released' => $query->where('status', TransactionStatus::Paid->value)->whereNotNull('client_confirmed_at'),
            default => null,
        };

        return $query->latest('id')->paginate($perPage)->withQueryString();
    }

    public function countAwaitingDeliverable(int $partnerId): int
    {
        return Transaction::query()
            ->where('payee_id', $partnerId)
            ->whereIn('type', self::DELIVERABLE_TYPES)
            ->where('status', TransactionStatus::Paid->value)
            ->whereNull('deliverable_link')
            ->count();
    }

    public function hasOpenTransactions(int $portfolioId): bool
    {
        return Transaction::query()
            ->where('portfolio_id', $portfolioId)
            ->where(function (Builder $q) {
                $q->where(fn (Builder $p) => $p
                    ->where('status', TransactionStatus::Pending->value)
                    ->where(fn (Builder $e) => $e->whereNull('expires_at')->orWhere('expires_at', '>', now())))
                    ->orWhere(fn (Builder $p) => $p
                        ->where('status', TransactionStatus::Paid->value)
                        ->whereNull('client_confirmed_at'));
            })
            ->exists();
    }

    public function balanceFor(int $partnerId): array
    {
        $row = Transaction::query()
            ->where('payee_id', $partnerId)
            ->where('status', TransactionStatus::Paid->value)
            ->whereIn('type', self::DELIVERABLE_TYPES)
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN client_confirmed_at IS NOT NULL THEN net_partner_amount ELSE 0 END), 0) as released, '
                .'COALESCE(SUM(CASE WHEN client_confirmed_at IS NULL THEN net_partner_amount ELSE 0 END), 0) as pending_release, '
                .'COUNT(*) as trx_count'
            )
            ->first();

        return [
            'released' => (float) ($row->released ?? 0),
            'pending_release' => (float) ($row->pending_release ?? 0),
            'transactions' => (int) ($row->trx_count ?? 0),
        ];
    }

    public function balancesByPartner(): array
    {
        return Transaction::query()
            ->where('status', TransactionStatus::Paid->value)
            ->whereIn('type', self::DELIVERABLE_TYPES)
            ->whereNotNull('payee_id')
            ->selectRaw(
                'payee_id, '
                .'COALESCE(SUM(CASE WHEN client_confirmed_at IS NOT NULL THEN net_partner_amount ELSE 0 END), 0) as released, '
                .'COALESCE(SUM(CASE WHEN client_confirmed_at IS NULL THEN net_partner_amount ELSE 0 END), 0) as pending_release'
            )
            ->groupBy('payee_id')
            ->get()
            ->mapWithKeys(fn ($row) => [(int) $row->payee_id => [
                'released' => (float) $row->released,
                'pending_release' => (float) $row->pending_release,
            ]])
            ->all();
    }

    public function revenueByType(?string $from, ?string $to): array
    {
        return $this->paidInPeriod($from, $to)
            ->selectRaw(
                'type, COUNT(*) as trx_count, COALESCE(SUM(amount), 0) as gross, '
                .'COALESCE(SUM(platform_fee), 0) as fee, COALESCE(SUM(net_partner_amount), 0) as net'
            )
            ->groupBy('type')
            ->get()
            ->map(fn ($r) => [
                'type' => $r->type instanceof TransactionType ? $r->type->value : (string) $r->type,
                'transactions' => (int) $r->trx_count,
                'gross' => (float) $r->gross,
                'platform_fee' => (float) $r->fee,
                'net_partner_amount' => (float) $r->net,
            ])
            ->all();
    }

    public function escrowTotals(?string $from, ?string $to): array
    {
        $row = $this->paidInPeriod($from, $to)
            ->whereIn('type', self::DELIVERABLE_TYPES)
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN client_confirmed_at IS NULL THEN net_partner_amount ELSE 0 END), 0) as pending_release, '
                .'COALESCE(SUM(CASE WHEN client_confirmed_at IS NOT NULL THEN net_partner_amount ELSE 0 END), 0) as released'
            )
            ->first();

        return [
            'pending_release' => (float) ($row->pending_release ?? 0),
            'released' => (float) ($row->released ?? 0),
        ];
    }

    private function paidInPeriod(?string $from, ?string $to): Builder
    {
        return Transaction::query()
            ->where('status', TransactionStatus::Paid->value)
            ->when($from, fn (Builder $q, string $d) => $q->whereRaw('DATE(COALESCE(paid_at, created_at)) >= ?', [$d]))
            ->when($to, fn (Builder $q, string $d) => $q->whereRaw('DATE(COALESCE(paid_at, created_at)) <= ?', [$d]));
    }
}
