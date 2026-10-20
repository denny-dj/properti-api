<?php

namespace App\Repositories\Eloquent;

use App\Models\Facility;
use App\Repositories\Contracts\FacilityRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class FacilityRepository extends BaseRepository implements FacilityRepositoryInterface
{
    public function __construct(Facility $model)
    {
        parent::__construct($model);
    }

    public function all(array $relations = []): Collection
    {
        return $this->model->newQuery()->with($relations)->orderBy('name')->get();
    }
}
