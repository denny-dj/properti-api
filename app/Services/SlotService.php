<?php

namespace App\Services;

use App\Models\ArchitectService;
use App\Repositories\Contracts\AvailabilityRepositoryInterface;
use App\Repositories\Contracts\BookingRepositoryInterface;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/** Menghitung slot kosong: jadwal mingguan arsitek dikurangi booking aktif. */
class SlotService
{
    public const MIN_LEAD_MINUTES = 120;   // pemesanan minimal 2 jam sebelum mulai
    public const MAX_ADVANCE_DAYS = 60;    // pemesanan maksimal 60 hari ke depan

    public function __construct(
        private AvailabilityRepositoryInterface $availabilities,
        private BookingRepositoryInterface $bookings,
    ) {
    }

    /** @return Collection<int, array{start: CarbonImmutable, end: CarbonImmutable}> */
    public function availableSlots(ArchitectService $service, CarbonImmutable $date): Collection
    {
        $tz = config('app.timezone');
        $day = $date->setTimezone($tz)->startOfDay();
        $duration = $service->duration_minutes;
        $earliest = CarbonImmutable::now($tz)->addMinutes(self::MIN_LEAD_MINUTES);

        $windows = $this->availabilities->forArchitect($service->architect_id)
            ->where('day_of_week', $day->dayOfWeekIso);

        $booked = $this->bookings->activeBetween($service->architect_id, $day, $day->endOfDay());

        $slots = collect();

        foreach ($windows as $window) {
            $cursor = $day->setTimeFromTimeString($window->start_time);
            $windowEnd = $day->setTimeFromTimeString($window->end_time);

            while ($cursor->addMinutes($duration)->lessThanOrEqualTo($windowEnd)) {
                $end = $cursor->addMinutes($duration);

                if ($cursor->greaterThanOrEqualTo($earliest) && ! $this->overlaps($booked, $cursor, $end)) {
                    $slots->push(['start' => $cursor, 'end' => $end]);
                }

                $cursor = $end;
            }
        }

        return $slots->sortBy(fn (array $s) => $s['start']->getTimestamp())->values();
    }

    public function isAvailable(ArchitectService $service, CarbonImmutable $start): bool
    {
        return $this->availableSlots($service, $start->startOfDay())
            ->contains(fn (array $s) => $s['start']->equalTo($start));
    }

    private function overlaps($booked, CarbonImmutable $start, CarbonImmutable $end): bool
    {
        return $booked->contains(
            fn ($b) => $b->scheduled_start->lt($end) && $b->scheduled_end->gt($start)
        );
    }
}
