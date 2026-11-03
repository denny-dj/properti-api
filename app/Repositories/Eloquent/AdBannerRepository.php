<?php

namespace App\Repositories\Eloquent;

use App\Models\AdBanner;
use App\Repositories\Contracts\AdBannerRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class AdBannerRepository extends BaseRepository implements AdBannerRepositoryInterface
{
    public function __construct(AdBanner $model)
    {
        parent::__construct($model);
    }

    public function live(?string $placement = null): Collection
    {
        return $this->model->newQuery()
            ->live()
            ->when($placement, fn (Builder $q, string $p) => $q->where('placement', $p))
            ->orderBy('placement')
            ->orderByDesc('id')
            ->get();
    }

    public function findLive(int $id): ?AdBanner
    {
        return $this->model->newQuery()->live()->whereKey($id)->first();
    }

    public function incrementImpressions(array $ids): void
    {
        if ($ids !== []) {
            // Query builder dasar: atomik dan tidak mengubah updated_at.
            DB::table($this->model->getTable())->whereIn('id', $ids)->increment('impression_count');
        }
    }

    public function incrementClicks(int $id): void
    {
        DB::table($this->model->getTable())->where('id', $id)->increment('click_count');
    }
}
