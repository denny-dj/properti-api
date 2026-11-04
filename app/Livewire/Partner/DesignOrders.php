<?php

namespace App\Livewire\Partner;

use App\Exceptions\ApiException;
use App\Models\Transaction;
use App\Services\DeliverableService;
use App\Services\PartnerBalanceService;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/** Pesanan desain (siap bangun & custom): kirim tautan berkas, pantau pelepasan dana. */
class DesignOrders extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $stage = '';

    /** @var array<int|string, string> tautan per id transaksi */
    public array $links = [];

    public ?string $notice = null;

    public function updatedStage(): void
    {
        $this->resetPage();
        $this->notice = null;
    }

    public function submitLink(int $id): void
    {
        $this->resetErrorBag();
        $this->notice = null;

        $transaction = Transaction::where('payee_id', auth()->id())->whereKey($id)->first();

        if (! $transaction) {
            $this->addError('action', 'Pesanan tidak ditemukan.');

            return;
        }

        $link = trim((string) ($this->links[$id] ?? ''));

        if ($link === '') {
            $this->addError("links.{$id}", 'Tempel tautan Google Drive terlebih dahulu.');

            return;
        }

        try {
            app(DeliverableService::class)->submit($transaction, auth()->user(), $link);
        } catch (ApiException $e) {
            $this->addError("links.{$id}", $e->getMessage());

            return;
        }

        unset($this->links[$id]);
        $this->notice = 'Tautan berkas dikirim ke pembeli untuk diperiksa.';
    }

    public function render()
    {
        $balances = app(PartnerBalanceService::class);
        $stage = in_array($this->stage, ['unpaid', 'awaiting_link', 'awaiting_confirmation', 'released'], true) ? $this->stage : null;

        return view('livewire.partner.design-orders', [
            'orders' => $balances->orders(auth()->user(), $stage, 10),
            'balance' => $balances->summary(auth()->user()),
            'awaiting' => $balances->awaitingCount(auth()->user()),
            'payouts' => $balances->recentPayouts(auth()->user(), 5),
        ]);
    }
}
