<?php

namespace App\Livewire\Orders;

use App\Enums\TransactionStatus;
use App\Exceptions\ApiException;
use App\Models\Transaction;
use App\Services\PaymentService;
use App\Services\TransactionService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;

class TransactionDetail extends Component
{
    #[Locked]
    public string $code = '';

    public ?string $notice = null;

    public function mount(string $code): void
    {
        $this->code = $code;
        $this->transaction();
    }

    private function transaction(): Transaction
    {
        $transaction = Transaction::where('code', $this->code)->firstOrFail();
        Gate::authorize('view', $transaction);

        return $transaction;
    }

    /** Lanjutkan pembayaran yang masih menunggu. */
    public function continuePay()
    {
        $transaction = $this->transaction();

        $expired = $transaction->expires_at && $transaction->expires_at->isPast();

        if ($transaction->status !== TransactionStatus::Pending || $expired || ! $transaction->redirect_url) {
            $this->addError('action', 'Tagihan ini tidak dapat dilanjutkan. Silakan buat pemesanan atau pembayaran baru.');

            return;
        }

        return $this->redirect($transaction->redirect_url);
    }

    /** Tarik status terbaru langsung dari Midtrans. */
    public function refreshStatus(): void
    {
        $transaction = $this->transaction();
        Gate::authorize('sync', $transaction);

        try {
            $updated = app(PaymentService::class)->sync($transaction);
            $this->notice = 'Status diperbarui: '.$updated->status->value.'.';
        } catch (ApiException $e) {
            $this->notice = $e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.orders.transaction-detail', [
            'transaction' => app(TransactionService::class)->loadDetail($this->transaction()),
        ]);
    }
}
