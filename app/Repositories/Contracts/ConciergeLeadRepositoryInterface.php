<?php

namespace App\Repositories\Contracts;

use App\Models\ConciergeLead;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ConciergeLeadRepositoryInterface extends BaseRepositoryInterface
{
    public function findByCode(string $code): ?ConciergeLead;

    public function codeExists(string $code): bool;

    /** Filter: status, source, q (kode/nama/telepon). */
    public function paginateFiltered(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /** Prospek yang sama (sumber + item + telepon) yang masih aktif dalam N jam terakhir. */
    public function findRecentDuplicate(string $source, ?int $referenceId, string $phone, int $hours = 24): ?ConciergeLead;
}
