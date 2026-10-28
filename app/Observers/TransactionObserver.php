<?php

namespace App\Observers;

use App\Enums\TransactionStatus;
use App\Models\Transaction;
use App\Support\Notices;
use App\Support\Notifier;

class TransactionObserver
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
            if ($transaction->status === TransactionStatus::Paid && $old !== TransactionStatus::Paid) {
                Notifier::send($transaction->payer, Notices::paymentReceipt($transaction));
                Notifier::send($transaction->payee, Notices::paymentReceived($transaction));
            } elseif ($transaction->status === TransactionStatus::Refunded) {
                Notifier::send($transaction->payer, Notices::paymentRefunded($transaction));
            }
        });
    }
}
