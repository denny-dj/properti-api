<?php

namespace App\Livewire\Orders;

use App\Services\TransactionService;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

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

        return view('livewire.orders.transaction-list', [
            'transactions' => app(TransactionService::class)->listForPayer(auth()->user(), $filters),
        ]);
    }
}
