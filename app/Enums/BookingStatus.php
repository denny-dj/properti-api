<?php

namespace App\Enums;

enum BookingStatus: string
{
    case Pending = 'pending';     // menunggu konfirmasi arsitek
    case Confirmed = 'confirmed';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';
    case Completed = 'completed';

    /** Status yang masih "memegang" slot jadwal arsitek. */
    public static function blocking(): array
    {
        return [self::Pending->value, self::Confirmed->value];
    }
}
