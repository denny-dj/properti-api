<?php

namespace App\Observers;

use App\Enums\PropertyStatus;
use App\Models\Property;
use App\Support\Notices;
use App\Support\Notifier;

class PropertyObserver
{
    /** Status lama disimpan di sini karena pada event "updated" nilai asli sudah tersinkron. */
    private static array $previous = [];

    public function updating(Property $property): void
    {
        if ($property->isDirty('status')) {
            self::$previous[$property->id] = $property->getOriginal('status');
        }
    }

    public function updated(Property $property): void
    {
        if (! $property->wasChanged('status')) {
            return;
        }

        $old = self::$previous[$property->id] ?? null;
        unset(self::$previous[$property->id]);

        Notifier::safely(function () use ($property, $old) {
            if ($property->status === PropertyStatus::Published && $old === PropertyStatus::PendingReview) {
                Notifier::send($property->partner, Notices::propertyApproved($property));
            } elseif ($property->status === PropertyStatus::Rejected) {
                Notifier::send($property->partner, Notices::propertyRejected($property));
            }
        });
    }
}
