<?php

namespace App\Repositories\Contracts;

use App\Models\Property;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PropertyRepositoryInterface extends BaseRepositoryInterface
{
    public function findBySlug(string $slug): ?Property;

    /** Filter lengkap (lokasi, harga, fasilitas) ditambahkan di Fase 2. */
    public function paginatePublished(int $perPage = 15): LengthAwarePaginator;
}
