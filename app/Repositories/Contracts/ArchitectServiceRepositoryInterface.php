<?php

namespace App\Repositories\Contracts;

use App\Models\ArchitectService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ArchitectServiceRepositoryInterface extends BaseRepositoryInterface
{
    /** Hanya layanan aktif milik arsitek yang sudah diverifikasi. */
    public function paginatePublic(array $filters = [], int $perPage = 12): LengthAwarePaginator;

    public function findActiveBySlug(string $slug): ?ArchitectService;

    public function findActiveById(int $id): ?ArchitectService;

    public function paginateByArchitect(int $architectId, int $perPage = 12): LengthAwarePaginator;
}
