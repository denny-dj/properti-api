<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use App\Repositories\Contracts\TransactionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TransactionService
{
    public function __construct(private TransactionRepositoryInterface $transactions)
    {
    }

    public function listForPayer(User $user, array $filters): LengthAwarePaginator
    {
        return $this->transactions->paginateForPayer($user->id, $filters, $this->perPage($filters));
    }

    public function listForPayee(User $partner, array $filters): LengthAwarePaginator
    {
        return $this->transactions->paginateForPayee($partner->id, $filters, $this->perPage($filters));
    }

    public function listForAdmin(array $filters): LengthAwarePaginator
    {
        return $this->transactions->paginateForAdmin($filters, $this->perPage($filters));
    }

    public function summary(array $filters): array
    {
        return $this->transactions->summary($filters);
    }

    public function loadDetail(Transaction $transaction): Transaction
    {
        return $transaction->load(['property', 'architectBooking', 'payer', 'payee']);
    }

    private function perPage(array $filters): int
    {
        return (int) ($filters['per_page'] ?? 15);
    }
}
