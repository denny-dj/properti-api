<?php

namespace App\Policies;

use App\Models\ArchitectBooking;
use App\Models\User;

class ArchitectBookingPolicy
{
    public function view(User $user, ArchitectBooking $booking): bool
    {
        return $this->isParty($user, $booking) || $user->isAdmin();
    }

    /** Aksi arsitek: konfirmasi / tolak / selesaikan. */
    public function manage(User $user, ArchitectBooking $booking): bool
    {
        return $user->id === $booking->architect_id;
    }

    /** Klien maupun arsitek boleh membatalkan. */
    public function cancel(User $user, ArchitectBooking $booking): bool
    {
        return $this->isParty($user, $booking);
    }

    private function isParty(User $user, ArchitectBooking $booking): bool
    {
        return $user->id === $booking->client_id || $user->id === $booking->architect_id;
    }
}
