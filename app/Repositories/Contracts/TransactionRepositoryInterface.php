<?php

namespace App\Repositories\Contracts;

use App\Models\ArchitectBooking;
use App\Models\Property;
use App\Models\Transaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TransactionRepositoryInterface extends BaseRepositoryInterface
{
    public function findByCode(string $code): ?Transaction;

    /** Harus dipanggil di dalam DB::transaction (SELECT ... FOR UPDATE). */
    public function findByCodeForUpdate(string $code): ?Transaction;

    public function lockProperty(int $propertyId): ?Property;

    public function lockBooking(int $bookingId): ?ArchitectBooking;

    /** Transaksi pending yang belum kedaluwarsa (menahan properti/booking). */
    public function activePendingForProperty(int $propertyId): ?Transaction;

    public function activePendingForBooking(int $bookingId): ?Transaction;

    public function hasPaidForBooking(int $bookingId): bool;

    public function paginateForPayer(int $userId, array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function paginateForPayee(int $userId, array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function paginateForAdmin(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function summary(array $filters = []): array;

    /** Tandai pending yang lewat batas waktu sebagai expired. Mengembalikan jumlah baris. */
    public function expireStale(): int;

    public function flagPaidForProperty(int $propertyId): int;
}
