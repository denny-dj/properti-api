<?php

namespace App\Repositories\Contracts;

use App\Models\PropertyImage;

interface PropertyImageRepositoryInterface extends BaseRepositoryInterface
{
    public function countForProperty(int $propertyId): int;

    public function maxSortOrder(int $propertyId): int;

    public function hasCover(int $propertyId): bool;

    public function resetCover(int $propertyId): void;

    public function firstForProperty(int $propertyId): ?PropertyImage;
}
