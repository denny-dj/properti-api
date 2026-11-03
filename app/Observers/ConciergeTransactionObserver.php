<?php

namespace App\Observers;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Support\ConciergeNotices;
use App\Support\Notifier;

/**
 * Pelengkap TransactionObserver (tanpa menyentuh alur webhook): saat invoice desain lunas,
 * mitra diminta mengirim tautan berkas. Properti -> reserved sudah ditangani PaymentService.
 */
class ConciergeTransactionObserver
{
    private static array $previous = [];

    public function updating(Transaction $transaction): void
    {
        if ($transaction->isDirty('status')) {
            self::$previous[$transaction->id] = $transaction->getOriginal('status');
        }
    }

    public function updated(Transaction $transaction): void
    {
        if (! $transaction->wasChanged('status')) {
            return;
        }

        $old = self::$previous[$transaction->id] ?? null;
        unset(self::$previous[$transaction->id]);

        Notifier::safely(function () use ($transaction, $old) {
            $deliverable = in_array($transaction->type, [TransactionType::ArchitectBlueprint, TransactionType::ArchitectCustomBooking], true);

            if ($deliverable && $transaction->status === TransactionStatus::Paid && $old !== TransactionStatus::Paid) {
                Notifier::send($transaction->payee, ConciergeNotices::deliverableDue($transaction));
            }
        });
    }
}
