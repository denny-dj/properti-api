<?php

namespace App\Repositories\Eloquent;

use App\Enums\BookingStatus;
use App\Models\ArchitectBooking;
use App\Repositories\Contracts\BookingRepositoryInterface;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class BookingRepository extends BaseRepository implements BookingRepositoryInterface
{
    private const RELATIONS = ['service', 'client', 'architect.partnerProfile'];

    public function __construct(ArchitectBooking $model)
    {
        parent::__construct($model);
    }

    public function paginateForClient(int $clientId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->listQuery($filters)->where('client_id', $clientId)->paginate($perPage)->withQueryString();
    }

    public function paginateForArchitect(int $architectId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->listQuery($filters)->where('architect_id', $architectId)->paginate($perPage)->withQueryString();
    }

    public function paginateForAdmin(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->listQuery($filters)->paginate($perPage)->withQueryString();
    }

    public function activeBetween(int $architectId, CarbonInterface $from, CarbonInterface $to): Collection
    {
        return $this->model->newQuery()
            ->where('architect_id', $architectId)
            ->whereIn('status', BookingStatus::blocking())
            ->where('scheduled_start', '<', $to)
            ->where('scheduled_end', '>', $from)
            ->get();
    }

    public function lockArchitect(int $architectId): void
    {
        DB::table('users')->where('id', $architectId)->lockForUpdate()->first();
    }

    public function codeExists(string $code): bool
    {
        return $this->model->newQuery()->where('code', $code)->exists();
    }

    public function hasActiveForService(int $serviceId): bool
    {
        return $this->model->newQuery()
            ->where('architect_service_id', $serviceId)
            ->whereIn('status', BookingStatus::blocking())
            ->exists();
    }

    private function listQuery(array $filters): Builder
    {
        return $this->model->newQuery()
            ->with(self::RELATIONS)
            ->when($filters['status'] ?? null, fn (Builder $q, $s) => $q->where('status', $s))
            ->when($filters['from'] ?? null, fn (Builder $q, $d) => $q->whereDate('scheduled_start', '>=', $d))
            ->when($filters['to'] ?? null, fn (Builder $q, $d) => $q->whereDate('scheduled_start', '<=', $d))
            ->orderByDesc('scheduled_start');
    }
}
