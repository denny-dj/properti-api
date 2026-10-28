<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ---------- Tugas terjadwal (butuh cron: * * * * * php artisan schedule:run) ----------

// Transaksi pending yang melewati batas waktu -> expired (melepas tahanan properti)
Schedule::command('payments:expire-stale')->everyFifteenMinutes()->withoutOverlapping();

// Kebersihan data
Schedule::command('queue:prune-failed --hours=168')->daily();
Schedule::command('sanctum:prune-expired --hours=24')->daily();
Schedule::command('auth:clear-resets')->daily();
