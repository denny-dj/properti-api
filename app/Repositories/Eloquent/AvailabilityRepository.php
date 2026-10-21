<?php

namespace App\Repositories\Eloquent;

use App\Models\ArchitectAvailability;
use App\Repositories\Contracts\AvailabilityRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class AvailabilityRepository extends BaseRepository implements AvailabilityRepositoryInterface
{
    public function __construct(ArchitectAvailability $model)
    {
        parent::__construct($model);
    }

    public function forArchitect(int $architectId): Collection
    {
        return $this->model->newQuery()
            ->where('architect_id', $architectId)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();
    }

    public function replaceForArchitect(int $architectId, array $rows): Collection
    {
        $this->model->newQuery()->where('architect_id', $architectId)->delete();

        foreach ($rows as $row) {
            $this->model->newQuery()->create([
                'architect_id' => $architectId,
                'day_of_week' => $row['day_of_week'],
                'start_time' => $row['start_time'],
                'end_time' => $row['end_time'],
            ]);
        }

        return $this->forArchitect($architectId);
    }
}
