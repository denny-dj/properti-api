<?php

namespace App\Policies;

use App\Enums\PropertyStatus;
use App\Models\Property;
use App\Models\User;

class PropertyPolicy
{
    /** Pemilik atau admin boleh melihat (termasuk draft/pending). */
    public function view(User $user, Property $property): bool
    {
        return $user->id === $property->partner_id || $user->isAdmin();
    }

    /** Hanya pemilik; properti yang sudah dipesan/terjual dikunci dari perubahan. */
    public function update(User $user, Property $property): bool
    {
        return $user->id === $property->partner_id
            && ! in_array($property->status, [PropertyStatus::Reserved, PropertyStatus::Sold], true);
    }

    public function delete(User $user, Property $property): bool
    {
        return $user->id === $property->partner_id
            && ! in_array($property->status, [PropertyStatus::Reserved, PropertyStatus::Sold], true);
    }

    /** Menandai terjual / melepas reservasi. */
    public function manageSale(User $user, Property $property): bool
    {
        return $user->id === $property->partner_id;
    }
}
