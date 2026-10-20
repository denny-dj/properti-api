<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserService
{
    public function __construct(private UserRepositoryInterface $users)
    {
    }

    public function list(array $filters): LengthAwarePaginator
    {
        return $this->users->paginateFiltered($filters, (int) ($filters['per_page'] ?? 15));
    }

    public function updateStatus(User $target, User $actor, string $status): User
    {
        if ($target->is($actor)) {
            throw new ApiException('Anda tidak dapat mengubah status akun sendiri.', 422);
        }

        if ($target->isAdmin()) {
            throw new ApiException('Status akun administrator tidak dapat diubah.', 403);
        }

        /** @var User $target */
        $target = $this->users->update($target, ['status' => $status]);

        if ($status === 'suspended') {
            $target->tokens()->delete(); // paksa logout di semua perangkat
        }

        return $target->load(['roles', 'partnerProfile']);
    }
}
