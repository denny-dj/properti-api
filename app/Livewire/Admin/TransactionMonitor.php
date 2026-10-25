<?php

namespace App\Livewire\Admin;

use App\Exceptions\ApiException;
use App\Livewire\Admin\Concerns\RequiresPermission;
use App\Models\Transaction;
use App\Services\PaymentService;
use App\Services\TransactionReviewService;
use App\Services\TransactionService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class TransactionMonitor extends Component
{
    use RequiresPermission, WithPagination;

    #[Url(except: '')]
    public string $status = '';

    #[Url(except: '')]
    public string $type = '';

    #[Url(except: false)]
    public bool $needsReview = false;

    #[Url(except: '')]
    public string $from = '';

    #[Url(except: '')]
    public string $to = '';

    public ?int $expandedId = null;

    public ?int $reviewingId = null;

    public string $reviewMode = '';   // review | refund

    public string $reviewNote = '';

    public ?string $notice = null;

    public function mount(): void
    {
        $this->requirePermission('transactions.view');
    }

    public function updated($name, $value = null): void
    {
        if (in_array($name, ['status', 'type', 'needsReview', 'from', 'to'], true)) {
            $this->resetPage();
            $this->expandedId = null;
        }
    }

    public function toggle(int $id): void
    {
        $this->expandedId = $this->expandedId === $id ? null : $id;
    }

    /** Tarik status terbaru langsung dari Midtrans. */
    public function sync(int $id): void
    {
        $this->requirePermission('transactions.view');

        $transaction = Transaction::findOrFail($id);
        Gate::authorize('sync', $transaction);

        $this->run(function () use ($transaction) {
            $updated = app(PaymentService::class)->sync($transaction);

            return 'Status disinkronkan: '.$updated->status->value.'.';
        });
    }

    public function startReview(int $id, string $mode): void
    {
        $this->requirePermission('transactions.view');

        if (! in_array($mode, ['review', 'refund'], true)) {
            return;
        }

        Transaction::findOrFail($id);

        $this->resetErrorBag();
        $this->reviewingId = $id;
        $this->reviewMode = $mode;
        $this->reviewNote = '';
    }

    public function dismissReview(): void
    {
        $this->reset(['reviewingId', 'reviewMode', 'reviewNote']);
        $this->resetErrorBag();
    }

    public function submitReview(): void
    {
        $this->requirePermission('transactions.view');

        if ($this->reviewingId === null) {
            return;
        }

        $this->validate(['reviewNote' => ['nullable', 'string', 'max:1000']]);

        $transaction = Transaction::findOrFail($this->reviewingId);
        $mode = $this->reviewMode;
        $note = trim($this->reviewNote) !== '' ? trim($this->reviewNote) : null;

        $this->run(function () use ($transaction, $mode, $note) {
            $service = app(TransactionReviewService::class);

            if ($mode === 'refund') {
                $service->markRefunded($transaction, auth()->user(), $note);

                return 'Transaksi dicatat sebagai dikembalikan.';
            }

            $service->markReviewed($transaction, auth()->user(), $note);

            return 'Transaksi ditandai sudah ditinjau.';
        });

        $this->reset(['reviewingId', 'reviewMode', 'reviewNote']);
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

    private function filters(): array
    {
        $filters = [];

        if (in_array($this->status, ['pending', 'paid', 'failed', 'expired', 'cancelled', 'refunded'], true)) {
            $filters['status'] = $this->status;
        }

        if (in_array($this->type, ['property_booking_fee', 'architect_booking'], true)) {
            $filters['type'] = $this->type;
        }

        if ($this->needsReview) {
            $filters['needs_review'] = 1;
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->from)) {
            $filters['from'] = $this->from;
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->to)) {
            $filters['to'] = $this->to;
        }

        return $filters;
    }

    public function render()
    {
        $filters = $this->filters();
        $service = app(TransactionService::class);

        return view('livewire.admin.transaction-monitor', [
            'transactions' => $service->listForAdmin($filters + ['per_page' => 10]),
            'summary' => $service->summary($filters),
        ]);
    }
}
