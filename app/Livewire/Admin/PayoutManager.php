<?php

namespace App\Livewire\Admin;

use App\Exceptions\ApiException;
use App\Models\User;
use App\Services\PayoutService;
use Livewire\Component;
use Livewire\WithPagination;

/** Pencatatan pencairan saldo mitra (transfer dilakukan admin di luar aplikasi). */
class PayoutManager extends Component
{
    use WithPagination;

    public ?int $selected = null;

    public string $amount = '';

    public string $reference = '';

    public string $note = '';

    public ?string $notice = null;

    public function startPay(int $partnerId): void
    {
        $this->resetErrorBag();
        $this->notice = null;

        $row = app(PayoutService::class)->balances()->first(fn (array $r) => $r['partner']->id === $partnerId);

        if (! $row || $row['available'] <= 0) {
            $this->addError('list', 'Mitra ini tidak memiliki saldo siap dicairkan.');

            return;
        }

        $this->selected = $partnerId;
        $this->amount = (string) (int) floor($row['available']);
        $this->reference = '';
        $this->note = '';
    }

    public function cancel(): void
    {
        $this->selected = null;
        $this->resetErrorBag();
    }

    public function record(): void
    {
        $this->resetErrorBag();
        $this->notice = null;

        if ($this->selected === null) {
            return;
        }

        $this->amount = preg_replace('/\D/', '', $this->amount) ?? '';

        $this->validate([
            'amount' => ['required', 'integer', 'min:1', 'max:99999999999'],
            'reference' => ['nullable', 'string', 'max:120'],
            'note' => ['nullable', 'string', 'max:1000'],
        ], [], ['amount' => 'nominal', 'reference' => 'referensi transfer', 'note' => 'catatan']);

        try {
            $payout = app(PayoutService::class)->record(
                auth()->user(),
                $this->selected,
                (float) $this->amount,
                $this->reference,
                $this->note,
            );
        } catch (ApiException $e) {
            $this->addError('amount', $e->getMessage());

            return;
        }

        $this->notice = 'Pencairan Rp '.number_format((float) $payout->amount, 0, ',', '.').' untuk '
            .($payout->partner->partnerProfile?->business_name ?? $payout->partner->name).' dicatat.';
        $this->selected = null;
        $this->reset(['amount', 'reference', 'note']);
    }

    public function render()
    {
        $service = app(PayoutService::class);

        return view('livewire.admin.payout-manager', [
            'balances' => $service->balances(),
            'history' => $service->history(null, 10),
        ]);
    }
}
