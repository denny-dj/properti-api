<?php

namespace App\Observers;

use App\Enums\VerificationStatus;
use App\Models\PartnerProfile;
use App\Support\Notices;
use App\Support\Notifier;

class PartnerProfileObserver
{
    public function updated(PartnerProfile $profile): void
    {
        if (! $profile->wasChanged('verification_status')) {
            return;
        }

        Notifier::safely(function () use ($profile) {
            $notice = match ($profile->verification_status) {
                VerificationStatus::Approved => Notices::partnerApproved($profile),
                VerificationStatus::Rejected => Notices::partnerRejected($profile),
                default => null,
            };

            if ($notice) {
                Notifier::send($profile->user, $notice);
            }
        });
    }
}
