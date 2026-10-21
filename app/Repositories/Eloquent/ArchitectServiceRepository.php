<?php

namespace App\Repositories\Eloquent;

use App\Enums\VerificationStatus;
use App\Models\ArchitectService;
use App\Repositories\Contracts\ArchitectServiceRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ArchitectServiceRepository extends BaseRepository implements ArchitectServiceRepositoryInterface
{
    public function __construct(ArchitectService $model)
    {
        parent::__construct($model);
    }

    public function paginatePublic(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        $query = $this->publicQuery()
            ->when($filters['q'] ?? null, function (Builder $q, string $term) {
                $like = '%'.$term.'%';
                $q->where(fn (Builder $w) => $w->where('title', 'like', $like)->orWhere('description', 'like', $like));
            })
            ->when($filters['service_type'] ?? null, fn (Builder $q, $v) => $q->where('service_type', $v))
            ->when($filters['min_price'] ?? null, fn (Builder $q, $v) => $q->where('price', '>=', $v))
            ->when($filters['max_price'] ?? null, fn (Builder $q, $v) => $q->where('price', '<=', $v))
            ->when($filters['city'] ?? null, fn (Builder $q, $v) => $q->whereHas(
                'architect.partnerProfile',
                fn (Builder $p) => $p->where('city', 'like', '%'.$v.'%')
            ));

        match ($filters['sort'] ?? 'newest') {
            'price_asc' => $query->orderBy('price')->orderByDesc('id'),
            'price_desc' => $query->orderByDesc('price')->orderByDesc('id'),
            default => $query->orderByDesc('id'),
        };

        return $query->paginate($perPage)->withQueryString();
    }

    public function findActiveBySlug(string $slug): ?ArchitectService
    {
        return $this->publicQuery()->where('slug', $slug)->first();
    }

    public function findActiveById(int $id): ?ArchitectService
    {
        return $this->publicQuery()->whereKey($id)->first();
    }

    public function paginateByArchitect(int $architectId, int $perPage = 12): LengthAwarePaginator
    {
        return $this->model->newQuery()
            ->where('architect_id', $architectId)
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    private function publicQuery(): Builder
    {
        return $this->model->newQuery()
            ->with('architect.partnerProfile')
            ->where('is_active', true)
            ->whereHas('architect', fn (Builder $q) => $q
                ->where('status', 'active')
                ->whereHas('partnerProfile', fn (Builder $p) => $p
                    ->where('verification_status', VerificationStatus::Approved->value)));
    }
}
