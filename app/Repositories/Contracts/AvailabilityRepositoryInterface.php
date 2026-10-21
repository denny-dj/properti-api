<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface AvailabilityRepositoryInterface extends BaseRepositoryInterface
{
    public function forArchitect(int $architectId): Collection;

    /** Ganti seluruh jadwal mingguan arsitek (hapus lalu isi ulang). */
    public function replaceForArchitect(int $architectId, array $rows): Collection;
}
