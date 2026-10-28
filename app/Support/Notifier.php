<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Pengirim notifikasi yang tidak pernah mengganggu alur bisnis:
 * dikirim setelah transaksi database di-commit, dan kegagalan email hanya dicatat di log.
 */
final class Notifier
{
    public static function send(?User $user, Notification $notification): void
    {
        if (! $user || ! $user->email) {
            return;
        }

        DB::afterCommit(function () use ($user, $notification) {
            try {
                $user->notify($notification);
            } catch (Throwable $e) {
                Log::warning('Notifikasi email gagal dikirim', [
                    'user_id' => $user->id,
                    'notification' => $notification::class,
                    'error' => $e->getMessage(),
                ]);
            }
        });
    }

    /** Email verifikasi untuk akun baru (dilewati bila sudah terverifikasi). */
    public static function verification(User $user): void
    {
        if ($user->hasVerifiedEmail()) {
            return;
        }

        try {
            $user->sendEmailVerificationNotification();
        } catch (Throwable $e) {
            Log::warning('Email verifikasi gagal dikirim', ['user_id' => $user->id, 'error' => $e->getMessage()]);
        }
    }

    /** Bungkus pembuatan notifikasi agar data yang tidak lengkap tidak menggagalkan proses utama. */
    public static function safely(callable $callback): void
    {
        try {
            $callback();
        } catch (Throwable $e) {
            Log::warning('Notifikasi dilewati karena kesalahan', ['error' => $e->getMessage()]);
        }
    }
}
