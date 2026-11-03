<?php

namespace App\Repositories\Eloquent;

use App\Enums\LeadStatus;
use App\Models\ConciergeLead;
use App\Repositories\Contracts\ConciergeLeadRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ConciergeLeadRepository extends BaseRepository implements ConciergeLeadRepositoryInterface
{
    public function __construct(ConciergeLead $model)
    {
        parent::__construct($model);
    }

    public function findByCode(string $code): ?ConciergeLead
    {
        return $this->model->newQuery()->where('lead_code', $code)->first();
    }

    public function codeExists(string $code): bool
    {
        return $this->model->newQuery()->where('lead_code', $code)->exists();
    }

    public function paginateFiltered(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->newQuery()
            ->with('handler')
            ->when($filters['status'] ?? null, fn (Builder $q, $v) => $q->where('status', $v))
            ->when($filters['source'] ?? null, fn (Builder $q, $v) => $q->where('source', $v))
            ->when($filters['q'] ?? null, function (Builder $q, string $term) {
                $like = '%'.$term.'%';
                $q->where(fn (Builder $w) => $w
                    ->where('lead_code', 'like', $like)
                    ->orWhere('customer_name', 'like', $like)
                    ->orWhere('customer_phone', 'like', $like));
            })
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findRecentDuplicate(string $source, ?int $referenceId, string $phone, int $hours = 24): ?ConciergeLead
    {
        return $this->model->newQuery()
            ->where('source', $source)
            ->where('customer_phone', $phone)
            ->when(
                $referenceId !== null,
                fn (Builder $q) => $q->where('reference_id', $referenceId),
                fn (Builder $q) => $q->whereNull('reference_id'),
            )
            ->whereIn('status', [LeadStatus::New->value, LeadStatus::Contacted->value])
            ->where('created_at', '>=', now()->subHours($hours))
            ->latest('id')
            ->first();
    }
}
