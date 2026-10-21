<?php

namespace App\Repositories\Contracts;

use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface BookingRepositoryInterface extends BaseRepositoryInterface
{
    public function paginateForClient(int $clientId, array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function paginateForArchitect(int $architectId, array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function paginateForAdmin(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /** Booking pending/confirmed milik arsitek yang beririsan dengan rentang waktu. */
    public function activeBetween(int $architectId, CarbonInterface $from, CarbonInterface $to): Collection;

    /** Kunci baris arsitek agar pemesanan slot diproses berurutan (cegah double booking). */
    public function lockArchitect(int $architectId): void;

    public function codeExists(string $code): bool;

    public function hasActiveForService(int $serviceId): bool;
}
