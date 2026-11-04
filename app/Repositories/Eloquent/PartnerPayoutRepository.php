<?php

namespace App\Repositories\Eloquent;

use App\Models\PartnerPayout;
use App\Repositories\Contracts\PartnerPayoutRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class PartnerPayoutRepository extends BaseRepository implements PartnerPayoutRepositoryInterface
{
    public function __construct(PartnerPayout $model)
    {
        parent::__construct($model);
    }

    public function totalFor(int $partnerId): float
    {
        return (float) $this->model->newQuery()->where('partner_id', $partnerId)->sum('amount');
    }

    public function totalsByPartner(): array
    {
        return $this->model->newQuery()
            ->selectRaw('partner_id, SUM(amount) as total')
            ->groupBy('partner_id')
            ->get()
            ->mapWithKeys(fn ($row) => [(int) $row->partner_id => (float) $row->total])
            ->all();
    }

    public function paginateHistory(?int $partnerId = null, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->newQuery()
            ->with(['partner.partnerProfile', 'recorder'])
            ->when($partnerId, fn (Builder $q, int $id) => $q->where('partner_id', $id))
            ->latest('paid_at')
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function recentFor(int $partnerId, int $limit = 5): Collection
    {
        return $this->model->newQuery()
            ->where('partner_id', $partnerId)
            ->latest('paid_at')
            ->latest('id')
            ->limit($limit)
            ->get();
    }
}
