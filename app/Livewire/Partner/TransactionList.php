<?php

namespace App\Livewire\Partner;

use App\Services\PartnerStatsService;
use App\Services\TransactionService;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/** Pembayaran masuk untuk partner sebagai penerima. */
class TransactionList extends Component
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

        if (in_array($this->status, ['pending', 'paid', 'failed', 'expired', 'cancelled', 'refunded'], true)) {
            $filters['status'] = $this->status;
        }

        return view('livewire.partner.transaction-list', [
            'transactions' => app(TransactionService::class)->listForPayee(auth()->user(), $filters),
            'revenue' => app(PartnerStatsService::class)->revenue(auth()->user()),
        ]);
    }
}
