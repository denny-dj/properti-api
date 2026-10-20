<?php

namespace App\Repositories\Eloquent;

use App\Models\Property;
use App\Repositories\Contracts\PropertyRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PropertyRepository extends BaseRepository implements PropertyRepositoryInterface
{
    public function __construct(Property $model)
    {
        parent::__construct($model);
    }

    public function findBySlug(string $slug): ?Property
    {
        return $this->model->newQuery()
            ->with(['partner.partnerProfile', 'images', 'facilities'])
            ->where('slug', $slug)
            ->first();
    }

    public function paginatePublished(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->newQuery()
            ->with(['coverImage', 'facilities'])
            ->published()
            ->latest('published_at')
            ->paginate($perPage);
    }
}
