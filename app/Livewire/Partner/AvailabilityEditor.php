<?php

namespace App\Livewire\Partner;

use App\Services\AvailabilityService;
use Livewire\Component;

/** Jadwal kerja mingguan arsitek (satu hari boleh punya beberapa rentang jam). */
class AvailabilityEditor extends Component
{
    /** @var array<int, array{day: int, start: string, end: string}> */
    public array $rows = [];

    public ?string $notice = null;

    public function mount(): void
    {
        $this->rows = app(AvailabilityService::class)->get(auth()->user())
            ->map(fn ($a) => [
                'day' => (int) $a->day_of_week,
                'start' => substr((string) $a->start_time, 0, 5),
                'end' => substr((string) $a->end_time, 0, 5),
            ])
            ->values()
            ->all();
    }

    public function addRange(int $day): void
    {
        if ($day < 1 || $day > 7) {
            return;
        }

        $hasRange = collect($this->rows)->contains(fn ($r) => (int) $r['day'] === $day);

        $this->rows[] = [
            'day' => $day,
            'start' => $hasRange ? '13:00' : '09:00',
            'end' => $hasRange ? '17:00' : '12:00',
        ];
        $this->notice = null;
    }

    public function removeRange(int $index): void
    {
        unset($this->rows[$index]);
        $this->rows = array_values($this->rows);
        $this->notice = null;
    }

    /** Preset: Senin-Jumat 09:00-16:00. */
    public function weekdays(): void
    {
        $this->rows = collect(range(1, 5))
            ->map(fn ($d) => ['day' => $d, 'start' => '09:00', 'end' => '16:00'])
            ->all();
        $this->notice = null;
    }

    public function save(): void
    {
        $this->resetErrorBag();

        $byDay = [];

        foreach ($this->rows as $row) {
            $day = (int) ($row['day'] ?? 0);
            $start = (string) ($row['start'] ?? '');
            $end = (string) ($row['end'] ?? '');

            if ($day < 1 || $day > 7 || ! preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $start) || ! preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $end)) {
                $this->addError('rows', 'Setiap jam harus berformat JJ:MM yang valid.');

                return;
            }

            if ($end <= $start) {
                $this->addError('rows', 'Jam selesai harus lebih besar dari jam mulai.');

                return;
            }

            $byDay[$day][] = ['day_of_week' => $day, 'start_time' => $start, 'end_time' => $end];
        }

        $flat = [];

        foreach ($byDay as $day => $ranges) {
            usort($ranges, fn ($a, $b) => strcmp($a['start_time'], $b['start_time']));

            foreach ($ranges as $i => $range) {
                if ($i > 0 && $range['start_time'] < $ranges[$i - 1]['end_time']) {
                    $this->addError('rows', 'Ada rentang jam yang saling tumpang tindih pada hari yang sama.');

                    return;
                }

                $flat[] = $range;
            }
        }

        app(AvailabilityService::class)->replace(auth()->user(), $flat);
        $this->notice = 'Jadwal kerja disimpan.';
    }

    public function render()
    {
        return view('livewire.partner.availability-editor');
    }
}
