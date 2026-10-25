<?php

namespace App\Services;

use App\Enums\TransactionStatus;
use App\Exceptions\ApiException;
use App\Models\Transaction;
use App\Models\User;
use App\Repositories\Contracts\TransactionRepositoryInterface;
use Illuminate\Support\Facades\DB;

/** Tindakan admin atas transaksi yang perlu ditinjau (audit: siapa, kapan, catatan). */
class TransactionReviewService
{
    public function __construct(private TransactionRepositoryInterface $transactions)
    {
    }

    /** Menutup tanda "perlu ditinjau". */
    public function markReviewed(Transaction $transaction, User $admin, ?string $note = null): Transaction
    {
        return DB::transaction(function () use ($transaction, $admin, $note) {
            return $this->save($this->lock($transaction), $admin, $note, ['needs_review' => false]);
        });
    }

    /**
     * Mencatat bahwa dana SUDAH dikembalikan (di dashboard Midtrans). Tidak memicu refund ke gateway.
     * Hanya untuk transaksi lunas.
     */
    public function markRefunded(Transaction $transaction, User $admin, ?string $note = null): Transaction
    {
        return DB::transaction(function () use ($transaction, $admin, $note) {
            $locked = $this->lock($transaction);

            if ($locked->status !== TransactionStatus::Paid) {
                throw new ApiException('Hanya transaksi berstatus lunas yang dapat dicatat sebagai dikembalikan.', 422);
            }

            return $this->save($locked, $admin, $note, [
                'status' => TransactionStatus::Refunded,
                'needs_review' => false,
            ]);
        });
    }

    private function lock(Transaction $transaction): Transaction
    {
        return $this->transactions->findByCodeForUpdate($transaction->code)
            ?? throw new ApiException('Transaksi tidak ditemukan.', 404);
    }

    private function save(Transaction $transaction, User $admin, ?string $note, array $attributes): Transaction
    {
        /** @var Transaction $updated */
        $updated = $this->transactions->update($transaction, $attributes + [
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
            'review_note' => $note,
        ]);

        return $updated;
    }
}
