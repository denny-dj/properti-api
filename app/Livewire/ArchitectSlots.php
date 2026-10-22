<?php

namespace App\Livewire;

use App\Exceptions\ApiException;
use App\Services\ArchitectCatalogService;
use App\Services\BookingService;
use App\Services\ChatService;
use App\Services\SlotService;
use DateTimeImmutable;
use Livewire\Attributes\Locked;
use Livewire\Component;

/** Pemilih tanggal, slot jadwal kosong, dan formulir pemesanan konsultasi arsitek. */
class ArchitectSlots extends Component
{
    #[Locked]
    public string $slug = '';

    public string $date = '';

    public ?string $selected = null;   // "Y-m-d H:i"

    public string $meeting_type = 'online';

    public string $location = '';

    public string $notes = '';

    public function mount(string $slug): void
    {
        $this->slug = $slug;
        $this->date = now()->addDay()->toDateString();
    }

    public function updatedDate(): void
    {
        $this->selected = null;
        $this->resetErrorBag();
    }

    public function selectSlot(string $start): void
    {
        [$data] = $this->loadSlots();

        // Hanya slot yang benar-benar tersedia yang boleh dipilih.
        if ($data && collect($data['slots'])->contains('start', $start)) {
            $this->selected = $start;
            $this->resetErrorBag();
        }
    }

    public function goLogin()
    {
        session()->put('url.intended', route('architects.show', $this->slug));

        return redirect()->route('login');
    }

    public function chat()
    {
        $user = auth()->user();

        if (! $user) {
            return $this->goLogin();
        }

        abort_unless($user->hasRole('user'), 403);

        try {
            $service = app(ArchitectCatalogService::class)->showPublic($this->slug);
            $conversation = app(ChatService::class)->start($user, ['architect_service_id' => $service->id]);
        } catch (ApiException $e) {
            $this->addError('booking', $e->getMessage());

            return;
        }

        return redirect()->route('chat.room', $conversation->id);
    }

    public function book()
    {
        $user = auth()->user();

        if (! $user) {
            return $this->goLogin();
        }

        abort_unless($user->hasRole('user'), 403);

        if (! $this->selected) {
            $this->addError('booking', 'Pilih slot jadwal terlebih dahulu.');

            return;
        }

        $this->validate([
            'meeting_type' => ['required', 'in:online,onsite'],
            'location' => ['required_if:meeting_type,onsite', 'nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ], [
            'location.required_if' => 'Alamat lokasi wajib diisi untuk pertemuan di lokasi.',
        ]);

        try {
            $service = app(ArchitectCatalogService::class)->showPublic($this->slug);

            $booking = app(BookingService::class)->create($user, [
                'architect_service_id' => $service->id,
                'scheduled_start' => $this->selected,
                'meeting_type' => $this->meeting_type,
                'location' => $this->meeting_type === 'onsite' ? $this->location : null,
                'notes' => $this->notes !== '' ? $this->notes : null,
            ]);
        } catch (ApiException $e) {
            $this->addError('booking', $e->getMessage());
            $this->selected = null;

            return;
        }

        session()->flash('status', 'Pesanan terkirim ke arsitek. Anda dapat membayar setelah arsitek mengonfirmasi jadwal.');

        return redirect()->route('orders.show', $booking->id);
    }

    /** @return array{0: ?array, 1: ?string} [data slot, pesan error] */
    private function loadSlots(): array
    {
        $min = now()->toDateString();
        $max = now()->addDays(SlotService::MAX_ADVANCE_DAYS)->toDateString();

        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $this->date);
        $valid = $parsed && $parsed->format('Y-m-d') === $this->date;

        if (! $valid || $this->date < $min || $this->date > $max) {
            return [null, 'Pilih tanggal antara hari ini dan '.SlotService::MAX_ADVANCE_DAYS.' hari ke depan.'];
        }

        try {
            return [app(ArchitectCatalogService::class)->slots($this->slug, $this->date), null];
        } catch (ApiException $e) {
            return [null, $e->getMessage()];
        }
    }

    public function render()
    {
        [$data, $error] = $this->loadSlots();

        $user = auth()->user();

        return view('livewire.architect-slots', [
            'data' => $data,
            'error' => $error,
            'min' => now()->toDateString(),
            'max' => now()->addDays(SlotService::MAX_ADVANCE_DAYS)->toDateString(),
            'isGuest' => $user === null,
            'isBuyer' => $user !== null && $user->hasRole('user'),
        ]);
    }
}
