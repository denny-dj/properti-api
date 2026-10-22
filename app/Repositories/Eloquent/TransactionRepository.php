<?php

namespace App\Repositories\Eloquent;

use App\Enums\TransactionStatus;
use App\Models\ArchitectBooking;
use App\Models\Property;
use App\Models\Transaction;
use App\Repositories\Contracts\TransactionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class TransactionRepository extends BaseRepository implements TransactionRepositoryInterface
{
    public function __construct(Transaction $model)
    {
        parent::__construct($model);
    }

    public function findByCode(string $code): ?Transaction
    {
        return $this->model->newQuery()->where('code', $code)->first();
    }

    public function findByCodeForUpdate(string $code): ?Transaction
    {
        return $this->model->newQuery()->where('code', $code)->lockForUpdate()->first();
    }

    public function lockProperty(int $propertyId): ?Property
    {
        return Property::query()->whereKey($propertyId)->lockForUpdate()->first();
    }

    public function lockBooking(int $bookingId): ?ArchitectBooking
    {
        return ArchitectBooking::query()->whereKey($bookingId)->lockForUpdate()->first();
    }

    public function activePendingForProperty(int $propertyId): ?Transaction
    {
        return $this->activePending()->where('property_id', $propertyId)->first();
    }

    public function activePendingForBooking(int $bookingId): ?Transaction
    {
        return $this->activePending()->where('architect_booking_id', $bookingId)->first();
    }

    public function hasPaidForBooking(int $bookingId): bool
    {
        return $this->model->newQuery()
            ->where('architect_booking_id', $bookingId)
            ->where('status', TransactionStatus::Paid->value)
            ->exists();
    }

    public function paginateForPayer(int $userId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->listQuery($filters)
            ->with(['property', 'architectBooking', 'payee'])
            ->where('user_id', $userId)
            ->paginate($perPage)->withQueryString();
    }

    public function paginateForPayee(int $userId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->listQuery($filters)
            ->with(['property', 'architectBooking', 'payer'])
            ->where('payee_id', $userId)
            ->paginate($perPage)->withQueryString();
    }

    public function paginateForAdmin(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->listQuery($filters)
            ->with(['property', 'architectBooking', 'payer', 'payee'])
            ->paginate($perPage)->withQueryString();
    }

    public function summary(array $filters = []): array
    {
        $query = $this->model->newQuery();
        $this->applyFilters($query, $filters);

        $rows = $query
            ->selectRaw('status, count(*) as total_count, coalesce(sum(amount), 0) as total_amount')
            ->groupBy('status')
            ->get();

        $byStatus = $rows->map(fn ($r) => [
            'status' => $r->status instanceof TransactionStatus ? $r->status->value : $r->status,
            'count' => (int) $r->total_count,
            'total_amount' => (float) $r->total_amount,
        ])->values()->all();

        $paid = collect($byStatus)->firstWhere('status', TransactionStatus::Paid->value);

        return [
            'paid_total_amount' => $paid['total_amount'] ?? 0.0,
            'paid_count' => $paid['count'] ?? 0,
            'by_status' => $byStatus,
        ];
    }

    public function expireStale(): int
    {
        return $this->model->newQuery()
            ->where('status', TransactionStatus::Pending->value)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->update(['status' => TransactionStatus::Expired->value]);
    }

    public function flagPaidForProperty(int $propertyId): int
    {
        return $this->model->newQuery()
            ->where('property_id', $propertyId)
            ->where('status', TransactionStatus::Paid->value)
            ->update(['needs_review' => true]);
    }

    // ------------------------------------------------------------------

    private function activePending(): Builder
    {
        return $this->model->newQuery()
            ->where('status', TransactionStatus::Pending->value)
            ->where(fn (Builder $q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()));
    }

    private function listQuery(array $filters): Builder
    {
        $query = $this->model->newQuery();
        $this->applyFilters($query, $filters);

        return $query->orderByDesc('id');
    }

    private function applyFilters(Builder $query, array $f): void
    {
        $query
            ->when($f['status'] ?? null, fn (Builder $q, $v) => $q->where('status', $v))
            ->when($f['type'] ?? null, fn (Builder $q, $v) => $q->where('type', $v))
            ->when(isset($f['needs_review']), fn (Builder $q) => $q->where(
                'needs_review',
                filter_var($f['needs_review'], FILTER_VALIDATE_BOOLEAN)
            ))
            ->when($f['from'] ?? null, fn (Builder $q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($f['to'] ?? null, fn (Builder $q, $d) => $q->whereDate('created_at', '<=', $d));
    }
}
