<?php

namespace App\Services;

use App\Enums\PropertyStatus;
use App\Exceptions\ApiException;
use App\Models\Property;
use App\Models\User;
use App\Repositories\Contracts\PropertyImageRepositoryInterface;
use App\Repositories\Contracts\PropertyRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class PropertyService
{
    public function __construct(
        private PropertyRepositoryInterface $properties,
        private PropertyImageRepositoryInterface $images,
    ) {
    }

    // ---------- Publik ----------

    public function listPublished(array $filters): LengthAwarePaginator
    {
        return $this->properties->paginatePublished($filters, $this->perPage($filters));
    }

    public function showPublished(string $slug): Property
    {
        $property = $this->properties->findPublishedBySlug($slug)
            ?? throw new ApiException('Properti tidak ditemukan.', 404);

        $this->properties->incrementViews($property);

        return $property;
    }

    // ---------- Partner ----------

    public function listForPartner(User $partner, array $filters): LengthAwarePaginator
    {
        return $this->properties->paginateByPartner($partner->id, $filters, $this->perPage($filters));
    }

    public function loadDetail(Property $property): Property
    {
        return $this->properties->loadDetail($property);
    }

    public function create(User $partner, array $data): Property
    {
        return DB::transaction(function () use ($partner, $data) {
            $facilityIds = Arr::pull($data, 'facility_ids', []);

            $data['partner_id'] = $partner->id;
            $data['status'] = PropertyStatus::Draft;

            /** @var Property $property */
            $property = $this->properties->create($data);
            $this->properties->syncFacilities($property, $facilityIds ?? []);

            return $this->properties->loadDetail($property);
        });
    }

    public function update(Property $property, array $data): Property
    {
        return DB::transaction(function () use ($property, $data) {
            $facilityIds = Arr::pull($data, 'facility_ids');

            // Mengubah listing yang sudah tayang -> wajib diverifikasi ulang admin.
            if ($property->status === PropertyStatus::Published) {
                $data['status'] = PropertyStatus::PendingReview;
                $data['published_at'] = null;
                $data['verified_by'] = null;
                $data['verified_at'] = null;
            }

            /** @var Property $property */
            $property = $this->properties->update($property, $data);

            if ($facilityIds !== null) {
                $this->properties->syncFacilities($property, $facilityIds);
            }

            return $this->properties->loadDetail($property);
        });
    }

    public function delete(Property $property): void
    {
        $this->properties->delete($property); // soft delete
    }

    /** Ajukan draft / properti yang ditolak untuk diverifikasi admin. */
    public function submit(Property $property): Property
    {
        if (! in_array($property->status, [PropertyStatus::Draft, PropertyStatus::Rejected], true)) {
            throw new ApiException('Hanya properti berstatus draft atau ditolak yang dapat diajukan.', 422);
        }

        if ($this->images->countForProperty($property->id) < 1) {
            throw new ApiException('Unggah minimal 1 foto sebelum mengajukan properti.', 422);
        }

        /** @var Property $property */
        $property = $this->properties->update($property, [
            'status' => PropertyStatus::PendingReview,
            'rejection_reason' => null,
        ]);

        return $this->properties->loadDetail($property);
    }

    // ---------- Admin ----------

    public function listForAdmin(array $filters): LengthAwarePaginator
    {
        return $this->properties->paginateForAdmin($filters, $this->perPage($filters));
    }

    public function approve(Property $property, User $admin): Property
    {
        $this->assertPending($property);

        /** @var Property $property */
        $property = $this->properties->update($property, [
            'status' => PropertyStatus::Published,
            'verified_by' => $admin->id,
            'verified_at' => now(),
            'published_at' => now(),
            'rejection_reason' => null,
        ]);

        return $this->properties->loadDetail($property);
    }

    public function reject(Property $property, User $admin, string $reason): Property
    {
        $this->assertPending($property);

        /** @var Property $property */
        $property = $this->properties->update($property, [
            'status' => PropertyStatus::Rejected,
            'verified_by' => $admin->id,
            'verified_at' => now(),
            'rejection_reason' => $reason,
        ]);

        return $this->properties->loadDetail($property);
    }

    // ---------- Helper ----------

    private function assertPending(Property $property): void
    {
        if ($property->status !== PropertyStatus::PendingReview) {
            throw new ApiException('Hanya properti berstatus pending_review yang dapat diverifikasi.', 422);
        }
    }

    private function perPage(array $filters): int
    {
        return (int) ($filters['per_page'] ?? 12);
    }
}
