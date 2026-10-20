<?php

namespace App\Repositories\Contracts;

use App\Models\Property;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PropertyRepositoryInterface extends BaseRepositoryInterface
{
    public function findPublishedBySlug(string $slug): ?Property;

    public function incrementViews(Property $property): void;

    public function paginatePublished(array $filters = [], int $perPage = 12): LengthAwarePaginator;

    public function paginateByPartner(int $partnerId, array $filters = [], int $perPage = 12): LengthAwarePaginator;

    public function paginateForAdmin(array $filters = [], int $perPage = 12): LengthAwarePaginator;

    public function syncFacilities(Property $property, array $facilityIds): void;

    public function loadDetail(Property $property): Property;
}
