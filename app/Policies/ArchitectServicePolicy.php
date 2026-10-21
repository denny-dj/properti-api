<?php

namespace App\Policies;

use App\Models\ArchitectService;
use App\Models\User;

class ArchitectServicePolicy
{
    public function view(User $user, ArchitectService $service): bool
    {
        return $user->id === $service->architect_id;
    }

    public function update(User $user, ArchitectService $service): bool
    {
        return $user->id === $service->architect_id;
    }

    public function delete(User $user, ArchitectService $service): bool
    {
        return $user->id === $service->architect_id;
    }
}
