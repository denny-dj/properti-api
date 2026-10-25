<?php

namespace App\Livewire\Admin;

use App\Livewire\Admin\Concerns\RequiresPermission;
use App\Models\Transaction;
use App\Services\BookingService;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/** Pantauan seluruh pesanan konsultasi (baca-saja). */
class BookingMonitor extends Component
{
    use RequiresPermission, WithPagination;

    #[Url(except: '')]
    public string $status = '';

    #[Url(except: '')]
    public string $from = '';

    #[Url(except: '')]
    public string $to = '';

    public function mount(): void
    {
        $this->requirePermission('bookings.view');
    }

    public function updated($name, $value = null): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $filters = ['per_page' => 15];

        if (in_array($this->status, ['pending', 'confirmed', 'rejected', 'cancelled', 'completed'], true)) {
            $filters['status'] = $this->status;
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->from)) {
            $filters['from'] = $this->from;
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->to)) {
            $filters['to'] = $this->to;
        }

        $bookings = app(BookingService::class)->listForAdmin($filters);

        $ids = collect($bookings->items())->pluck('id')->all();
        $paidIds = $ids === [] ? [] : array_map('intval', Transaction::whereIn('architect_booking_id', $ids)
            ->where('status', 'paid')->pluck('architect_booking_id')->all());

        return view('livewire.admin.booking-monitor', compact('bookings', 'paidIds'));
    }
}
