<?php

namespace App\Livewire;

use App\Exceptions\ApiException;
use App\Services\ArchitectCatalogService;
use App\Services\SlotService;
use DateTimeImmutable;
use Livewire\Attributes\Locked;
use Livewire\Component;

/** Pemilih tanggal + daftar slot jadwal kosong untuk satu layanan arsitek. */
class ArchitectSlots extends Component
{
    #[Locked]
    public string $slug = '';

    public string $date = '';

    public function mount(string $slug): void
    {
        $this->slug = $slug;
        $this->date = now()->addDay()->toDateString();
    }

    public function render()
    {
        $min = now()->toDateString();
        $max = now()->addDays(SlotService::MAX_ADVANCE_DAYS)->toDateString();

        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $this->date);
        $valid = $parsed && $parsed->format('Y-m-d') === $this->date;

        $data = null;
        $error = null;

        if (! $valid || $this->date < $min || $this->date > $max) {
            $error = 'Pilih tanggal antara hari ini dan '.SlotService::MAX_ADVANCE_DAYS.' hari ke depan.';
        } else {
            try {
                $data = app(ArchitectCatalogService::class)->slots($this->slug, $this->date);
            } catch (ApiException $e) {
                $error = $e->getMessage();
            }
        }

        return view('livewire.architect-slots', compact('data', 'error', 'min', 'max'));
    }
}
