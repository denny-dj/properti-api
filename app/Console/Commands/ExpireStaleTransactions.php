<?php

namespace App\Console\Commands;

use App\Repositories\Contracts\TransactionRepositoryInterface;
use Illuminate\Console\Command;

class ExpireStaleTransactions extends Command
{
    protected $signature = 'payments:expire-stale';

    protected $description = 'Tandai transaksi pending yang melewati batas waktu sebagai expired (melepas tahanan properti).';

    public function handle(TransactionRepositoryInterface $transactions): int
    {
        $count = $transactions->expireStale();

        $this->info("{$count} transaksi ditandai kedaluwarsa.");

        return self::SUCCESS;
    }
}
