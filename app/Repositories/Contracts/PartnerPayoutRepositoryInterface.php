<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface PartnerPayoutRepositoryInterface extends BaseRepositoryInterface
{
    public function totalFor(int $partnerId): float;

    /** @return array<int, float> total pencairan per id mitra */
    public function totalsByPartner(): array;

    public function paginateHistory(?int $partnerId = null, int $perPage = 15): LengthAwarePaginator;

    public function recentFor(int $partnerId, int $limit = 5): Collection;
}
