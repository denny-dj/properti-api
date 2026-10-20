<?php

namespace App\Repositories\Eloquent;

use App\Models\PartnerProfile;
use App\Repositories\Contracts\PartnerProfileRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class PartnerProfileRepository extends BaseRepository implements PartnerProfileRepositoryInterface
{
    public function __construct(PartnerProfile $model)
    {
        parent::__construct($model);
    }

    public function paginateFiltered(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->newQuery()
            ->with('user')
            ->when($filters['status'] ?? null, fn (Builder $q, $s) => $q->where('verification_status', $s))
            ->when($filters['partner_type'] ?? null, fn (Builder $q, $t) => $q->where('partner_type', $t))
            ->when($filters['q'] ?? null, function (Builder $q, string $term) {
                $like = '%'.$term.'%';
                $q->where(fn (Builder $w) => $w
                    ->where('business_name', 'like', $like)
                    ->orWhereHas('user', fn (Builder $u) => $u
                        ->where('name', 'like', $like)
                        ->orWhere('email', 'like', $like)));
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }
}
