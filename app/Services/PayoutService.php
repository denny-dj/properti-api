<?php

namespace App\Services;

use App\Enums\RoleName;
use App\Exceptions\ApiException;
use App\Models\PartnerPayout;
use App\Models\User;
use App\Repositories\Contracts\ConciergeTransactionRepositoryInterface;
use App\Repositories\Contracts\PartnerPayoutRepositoryInterface;
use App\Support\ConciergeNotices;
use App\Support\Notifier;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Pencairan saldo mitra. Admin mentransfer secara manual lalu mencatatnya di sini;
 * saldo siap cair = dana dilepas (dikonfirmasi pembeli) dikurangi pencairan yang tercatat.
 */
class PayoutService
{
    public function __construct(
        private PartnerPayoutRepositoryInterface $payouts,
        private ConciergeTransactionRepositoryInterface $transactions,
    ) {
    }

    /** @return Collection<int, array{partner: User, released: float, paid_out: float, available: float, pending_release: float}> */
    public function balances(): Collection
    {
        $balances = $this->transactions->balancesByPartner();
        $paid = $this->payouts->totalsByPartner();
        $ids = array_values(array_unique(array_merge(array_keys($balances), array_keys($paid))));
        $partners = User::with('partnerProfile')->whereIn('id', $ids)->get()->keyBy('id');

        return collect($ids)
            ->filter(fn (int $id) => $partners->has($id))
            ->map(function (int $id) use ($balances, $paid, $partners) {
                $released = (float) ($balances[$id]['released'] ?? 0);
                $paidOut = (float) ($paid[$id] ?? 0);

                return [
                    'partner' => $partners[$id],
                    'released' => $released,
                    'paid_out' => $paidOut,
                    'available' => max(0.0, $released - $paidOut),
                    'pending_release' => (float) ($balances[$id]['pending_release'] ?? 0),
                ];
            })
            ->sortByDesc('available')
            ->values();
    }

    /** Total kewajiban ke mitra yang belum ditransfer. */
    public function outstandingTotal(): float
    {
        return (float) $this->balances()->sum('available');
    }

    public function history(?int $partnerId = null, int $perPage = 15): LengthAwarePaginator
    {
        return $this->payouts->paginateHistory($partnerId, $perPage);
    }

    public function record(User $admin, int $partnerId, float $amount, ?string $reference = null, ?string $note = null): PartnerPayout
    {
        $payout = DB::transaction(function () use ($admin, $partnerId, $amount, $reference, $note) {
            // Kunci baris mitra agar dua pencairan bersamaan tidak melebihi saldo.
            $partner = User::whereKey($partnerId)->lockForUpdate()->first();

            if (! $partner || ! $partner->hasRole(RoleName::Partner->value)) {
                throw new ApiException('Mitra tidak ditemukan.', 404);
            }

            if ($amount <= 0) {
                throw new ApiException('Nominal pencairan harus lebih dari 0.', 422);
            }

            $available = $this->transactions->balanceFor($partnerId)['released'] - $this->payouts->totalFor($partnerId);

            if ($amount > $available + 0.005) {
                throw new ApiException('Nominal melebihi saldo siap dicairkan (Rp '.number_format(max(0.0, $available), 0, ',', '.').').', 422);
            }

            /** @var PartnerPayout $payout */
            $payout = $this->payouts->create([
                'partner_id' => $partnerId,
                'amount' => $amount,
                'reference' => $this->clean($reference),
                'note' => $this->clean($note),
                'paid_at' => now(),
                'recorded_by' => $admin->id,
            ]);

            return $payout;
        });

        Log::info('partner.payout.recorded', [
            'payout_id' => $payout->id, 'partner_id' => $partnerId, 'amount' => $amount, 'by' => $admin->id,
        ]);

        Notifier::safely(fn () => Notifier::send($payout->partner, ConciergeNotices::payoutRecorded($payout)));

        return $payout->load(['partner', 'recorder']);
    }

    private function clean(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
