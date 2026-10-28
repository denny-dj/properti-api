<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPassword implements ShouldQueue
{
    use Queueable;

    /** Percobaan ulang bila server email sedang bermasalah. */
    public int $tries = 3;

    public array $backoff = [30, 120];

    protected function buildMailMessage($url): MailMessage
    {
        $minutes = config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60);

        return (new MailMessage)
            ->subject('Atur ulang password · '.config('app.name'))
            ->greeting('Halo!')
            ->line('Kami menerima permintaan untuk mengatur ulang password akun Anda.')
            ->action('Atur ulang password', $url)
            ->line("Tautan ini berlaku selama {$minutes} menit.")
            ->line('Jika Anda tidak meminta pengaturan ulang password, abaikan email ini; password Anda tidak berubah.')
            ->salutation('Salam, tim '.config('app.name'));
    }
}
