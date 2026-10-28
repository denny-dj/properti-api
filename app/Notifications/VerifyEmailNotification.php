<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmailNotification extends VerifyEmail implements ShouldQueue
{
    use Queueable;

    /** Percobaan ulang bila server email sedang bermasalah. */
    public int $tries = 3;

    public array $backoff = [30, 120];

    protected function buildMailMessage($url): MailMessage
    {
        $minutes = config('auth.verification.expire', 60);

        return (new MailMessage)
            ->subject('Verifikasi alamat email Anda · '.config('app.name'))
            ->greeting('Selamat datang!')
            ->line('Satu langkah lagi: verifikasi alamat email Anda agar dapat memesan, chat, dan membayar.')
            ->action('Verifikasi email', $url)
            ->line("Tautan ini berlaku selama {$minutes} menit. Anda dapat meminta tautan baru kapan saja setelah masuk.")
            ->line('Jika Anda tidak membuat akun, abaikan email ini.')
            ->salutation('Salam, tim '.config('app.name'));
    }
}
