<?php

namespace App\Livewire\Admin;

use App\Services\PayoutService;
use App\Services\RevenueSummaryService;
use Carbon\Carbon;
use Livewire\Attributes\Url;
use Livewire\Component;

/** Rekap pendapatan platform: komisi 2% properti dan 10-15% arsitek, serta dana mitra tertahan/dilepas. */
class RevenueDashboard extends Component
{
    #[Url(except: '')]
    public string $from = '';

    #[Url(except: '')]
    public string $to = '';

    public function preset(string $name): void
    {
        [$this->from, $this->to] = match ($name) {
            'month' => [now()->startOfMonth()->toDateString(), now()->toDateString()],
            'last_month' => [now()->subMonthNoOverflow()->startOfMonth()->toDateString(), now()->subMonthNoOverflow()->endOfMonth()->toDateString()],
            'year' => [now()->startOfYear()->toDateString(), now()->toDateString()],
            default => ['', ''],
        };
    }

    private function validDate(string $value): ?string
    {
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1 && Carbon::hasFormat($value, 'Y-m-d') ? $value : null;
    }

    public function render()
    {
        $from = $this->validDate($this->from);
        $to = $this->validDate($this->to);

        if ($from && $to && $to < $from) {
            [$from, $to] = [$to, $from];
        }

        return view('livewire.admin.revenue-dashboard', [
            'summary' => app(RevenueSummaryService::class)->summary($from, $to),
            'outstanding' => auth()->user()?->can('payouts.manage') ? app(PayoutService::class)->outstandingTotal() : null,
        ]);
    }
}
