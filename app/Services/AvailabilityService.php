<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\AvailabilityRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class AvailabilityService
{
    public function __construct(private AvailabilityRepositoryInterface $availabilities)
    {
    }

    public function get(User $architect): Collection
    {
        return $this->availabilities->forArchitect($architect->id);
    }

    public function replace(User $architect, array $rows): Collection
    {
        return DB::transaction(fn () => $this->availabilities->replaceForArchitect($architect->id, $rows));
    }
}
