<?php

namespace App\Observers;

use App\Enums\BookingStatus;
use App\Models\ArchitectBooking;
use App\Support\Notices;
use App\Support\Notifier;

class ArchitectBookingObserver
{
    public function created(ArchitectBooking $booking): void
    {
        Notifier::safely(function () use ($booking) {
            Notifier::send($booking->architect, Notices::bookingRequested($booking));
        });
    }

    public function updated(ArchitectBooking $booking): void
    {
        if (! $booking->wasChanged('status')) {
            return;
        }

        Notifier::safely(function () use ($booking) {
            match ($booking->status) {
                BookingStatus::Confirmed => Notifier::send($booking->client, Notices::bookingConfirmed($booking)),
                BookingStatus::Rejected => Notifier::send($booking->client, Notices::bookingRejected($booking)),
                BookingStatus::Cancelled => $booking->cancelled_by === $booking->client_id
                    ? Notifier::send($booking->architect, Notices::bookingCancelled($booking, true))
                    : Notifier::send($booking->client, Notices::bookingCancelled($booking, false)),
                default => null,
            };
        });
    }
}
