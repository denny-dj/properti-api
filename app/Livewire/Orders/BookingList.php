<?php

namespace App\Livewire\Orders;

use App\Services\BookingService;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class BookingList extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $status = '';

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $filters = ['per_page' => 10];

        if (in_array($this->status, ['pending', 'confirmed', 'rejected', 'cancelled', 'completed'], true)) {
            $filters['status'] = $this->status;
        }

        return view('livewire.orders.booking-list', [
            'bookings' => app(BookingService::class)->listForClient(auth()->user(), $filters),
        ]);
    }
}
