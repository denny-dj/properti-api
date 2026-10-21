<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Models\ArchitectService;
use App\Models\User;
use App\Repositories\Contracts\ArchitectServiceRepositoryInterface;
use App\Repositories\Contracts\BookingRepositoryInterface;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ArchitectCatalogService
{
    public function __construct(
        private ArchitectServiceRepositoryInterface $services,
        private BookingRepositoryInterface $bookings,
        private SlotService $slots,
    ) {
    }

    // ---------- Publik ----------

    public function listPublic(array $filters): LengthAwarePaginator
    {
        return $this->services->paginatePublic($filters, (int) ($filters['per_page'] ?? 12));
    }

    public function showPublic(string $slug): ArchitectService
    {
        return $this->services->findActiveBySlug($slug)
            ?? throw new ApiException('Layanan arsitek tidak ditemukan.', 404);
    }

    public function slots(string $slug, string $date): array
    {
        $service = $this->showPublic($slug);
        $tz = config('app.timezone');
        $day = CarbonImmutable::createFromFormat('Y-m-d', $date, $tz)->startOfDay();

        return [
            'date' => $day->toDateString(),
            'timezone' => $tz,
            'duration_minutes' => $service->duration_minutes,
            'slots' => $this->slots->availableSlots($service, $day)
                ->map(fn (array $s) => [
                    'start' => $s['start']->format('Y-m-d H:i'),
                    'end' => $s['end']->format('Y-m-d H:i'),
                ])
                ->values()
                ->all(),
        ];
    }

    // ---------- Arsitek ----------

    public function listForArchitect(User $architect, array $filters): LengthAwarePaginator
    {
        return $this->services->paginateByArchitect($architect->id, (int) ($filters['per_page'] ?? 12));
    }

    public function create(User $architect, array $data): ArchitectService
    {
        $data['architect_id'] = $architect->id;
        $data['is_active'] = $data['is_active'] ?? true;

        /** @var ArchitectService $service */
        $service = $this->services->create($data);

        return $service;
    }

    public function update(ArchitectService $service, array $data): ArchitectService
    {
        /** @var ArchitectService $service */
        $service = $this->services->update($service, $data);

        return $service;
    }

    public function delete(ArchitectService $service): void
    {
        if ($this->bookings->hasActiveForService($service->id)) {
            throw new ApiException('Layanan masih memiliki pesanan aktif. Nonaktifkan layanan, jangan dihapus.', 422);
        }

        $this->services->delete($service); // soft delete
    }
}
