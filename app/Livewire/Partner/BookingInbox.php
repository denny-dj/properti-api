<?php

namespace App\Livewire\Partner;

use App\Exceptions\ApiException;
use App\Models\ArchitectBooking;
use App\Models\Transaction;
use App\Services\BookingService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/** Pesanan konsultasi masuk untuk arsitek. */
class BookingInbox extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $status = '';

    public ?int $actionId = null;

    public string $actionType = '';   // reject | cancel

    public string $reason = '';

    public ?string $notice = null;

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    private function owned(int $id): ArchitectBooking
    {
        return ArchitectBooking::where('architect_id', auth()->id())->findOrFail($id);
    }

    public function confirm(int $id): void
    {
        $booking = $this->owned($id);
        Gate::authorize('manage', $booking);

        $this->run(function () use ($booking) {
            app(BookingService::class)->confirm($booking);

            return 'Pesanan dikonfirmasi. Klien kini dapat melakukan pembayaran.';
        });
    }

    public function complete(int $id): void
    {
        $booking = $this->owned($id);
        Gate::authorize('manage', $booking);

        $this->run(function () use ($booking) {
            app(BookingService::class)->complete($booking);

            return 'Pesanan diselesaikan.';
        });
    }

    public function startAction(int $id, string $type): void
    {
        if (! in_array($type, ['reject', 'cancel'], true)) {
            return;
        }

        $this->owned($id);
        $this->resetErrorBag();
        $this->actionId = $id;
        $this->actionType = $type;
        $this->reason = '';
    }

    public function dismissAction(): void
    {
        $this->reset(['actionId', 'actionType', 'reason']);
        $this->resetErrorBag();
    }

    public function submitAction(): void
    {
        if ($this->actionId === null) {
            return;
        }

        $booking = $this->owned($this->actionId);
        $type = $this->actionType;

        if ($type === 'reject') {
            Gate::authorize('manage', $booking);
            $this->validate(
                ['reason' => ['required', 'string', 'min:5', 'max:1000']],
                ['reason.required' => 'Alasan penolakan wajib diisi.', 'reason.min' => 'Alasan minimal 5 karakter.'],
            );
        } else {
            Gate::authorize('cancel', $booking);
            $this->validate(['reason' => ['nullable', 'string', 'max:1000']]);
        }

        $reason = $this->reason;

        $this->run(function () use ($booking, $type, $reason) {
            $service = app(BookingService::class);

            if ($type === 'reject') {
                $service->reject($booking, auth()->user(), $reason);

                return 'Pesanan ditolak.';
            }

            $service->cancel($booking, auth()->user(), $reason !== '' ? $reason : null);

            return 'Pesanan dibatalkan.';
        });

        $this->reset(['actionId', 'actionType', 'reason']);
    }

    private function run(callable $action): void
    {
        $this->resetErrorBag('list');

        try {
            $this->notice = $action();
        } catch (ApiException $e) {
            $this->notice = null;
            $this->addError('list', $e->getMessage());
        }
    }

    public function render()
    {
        $filters = ['per_page' => 10];

        if (in_array($this->status, ['pending', 'confirmed', 'rejected', 'cancelled', 'completed'], true)) {
            $filters['status'] = $this->status;
        }

        $bookings = app(BookingService::class)->listForArchitect(auth()->user(), $filters);

        $ids = collect($bookings->items())->pluck('id')->all();
        $paidIds = $ids === [] ? [] : array_map('intval', Transaction::whereIn('architect_booking_id', $ids)
            ->where('status', 'paid')->pluck('architect_booking_id')->all());

        return view('livewire.partner.booking-inbox', compact('bookings', 'paidIds'));
    }
}
