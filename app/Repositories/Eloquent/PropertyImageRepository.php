<?php

namespace App\Repositories\Eloquent;

use App\Models\PropertyImage;
use App\Repositories\Contracts\PropertyImageRepositoryInterface;

class PropertyImageRepository extends BaseRepository implements PropertyImageRepositoryInterface
{
    public function __construct(PropertyImage $model)
    {
        parent::__construct($model);
    }

    public function countForProperty(int $propertyId): int
    {
        return $this->model->newQuery()->where('property_id', $propertyId)->count();
    }

    public function maxSortOrder(int $propertyId): int
    {
        return (int) $this->model->newQuery()->where('property_id', $propertyId)->max('sort_order');
    }

    public function hasCover(int $propertyId): bool
    {
        return $this->model->newQuery()->where('property_id', $propertyId)->where('is_cover', true)->exists();
    }

    public function resetCover(int $propertyId): void
    {
        $this->model->newQuery()->where('property_id', $propertyId)->update(['is_cover' => false]);
    }

    public function firstForProperty(int $propertyId): ?PropertyImage
    {
        return $this->model->newQuery()->where('property_id', $propertyId)->orderBy('sort_order')->first();
    }
}
