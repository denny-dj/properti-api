<?php

namespace App\Support;

use App\Exceptions\ApiException;
use App\Models\User;

class EmailVerification
{
    /** Aksi penting pembeli (chat, pesan, bayar) memerlukan email terverifikasi, di web maupun API. */
    public static function require(User $user): void
    {
        if (! $user->hasVerifiedEmail()) {
            throw new ApiException(
                'Verifikasi email Anda terlebih dahulu. Periksa kotak masuk atau kirim ulang tautan di '.route('verification.notice').'.',
                403,
            );
        }
    }
}
