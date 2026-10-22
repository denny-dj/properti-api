<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;

class TransactionPolicy
{
    public function view(User $user, Transaction $transaction): bool
    {
        return $user->id === $transaction->user_id
            || $user->id === $transaction->payee_id
            || $user->can('transactions.view');
    }

    /** Sinkronisasi status ke gateway: pembayar atau admin. */
    public function sync(User $user, Transaction $transaction): bool
    {
        return $user->id === $transaction->user_id || $user->can('transactions.view');
    }
}
