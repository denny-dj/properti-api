<?php

namespace App\Services;

use App\Enums\VerificationStatus;
use App\Exceptions\ApiException;
use App\Models\PartnerProfile;
use App\Models\User;
use App\Repositories\Contracts\PartnerProfileRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PartnerService
{
    public function __construct(private PartnerProfileRepositoryInterface $profiles)
    {
    }

    public function list(array $filters): LengthAwarePaginator
    {
        return $this->profiles->paginateFiltered($filters, (int) ($filters['per_page'] ?? 15));
    }

    public function approve(PartnerProfile $profile, User $admin): PartnerProfile
    {
        if ($profile->verification_status === VerificationStatus::Approved) {
            throw new ApiException('Partner ini sudah terverifikasi.', 422);
        }

        /** @var PartnerProfile $profile */
        $profile = $this->profiles->update($profile, [
            'verification_status' => VerificationStatus::Approved,
            'verified_by' => $admin->id,
            'verified_at' => now(),
            'rejection_reason' => null,
        ]);

        return $profile->load('user');
    }

    public function reject(PartnerProfile $profile, User $admin, string $reason): PartnerProfile
    {
        /** @var PartnerProfile $profile */
        $profile = $this->profiles->update($profile, [
            'verification_status' => VerificationStatus::Rejected,
            'verified_by' => $admin->id,
            'verified_at' => now(),
            'rejection_reason' => $reason,
        ]);

        return $profile->load('user');
    }
}
