<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Email generik: subjek, beberapa baris isi, dan satu tombol aksi opsional. */
class EventNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /** Percobaan ulang bila server email sedang bermasalah. */
    public int $tries = 3;

    public array $backoff = [30, 120];

    /** @param  array<int, string>  $lines */
    public function __construct(
        public readonly string $subject,
        public readonly array $lines,
        public readonly ?string $actionText = null,
        public readonly ?string $actionUrl = null,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject($this->subject.' · '.config('app.name'))
            ->greeting('Halo, '.$notifiable->name.'!');

        foreach ($this->lines as $line) {
            $mail->line($line);
        }

        if ($this->actionText && $this->actionUrl) {
            $mail->action($this->actionText, $this->actionUrl);
        }

        return $mail->salutation('Salam, tim '.config('app.name'));
    }
}
