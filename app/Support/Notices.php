<?php

namespace App\Support;

use App\Models\ArchitectBooking;
use App\Models\PartnerProfile;
use App\Models\Property;
use App\Models\Transaction;
use App\Notifications\EventNotification;

/** Isi email untuk setiap peristiwa penting (bahasa Indonesia). */
final class Notices
{
    private static function rp($value): string
    {
        return 'Rp '.number_format((float) $value, 0, ',', '.');
    }

    private static function methodLabel(Transaction $t): string
    {
        $labels = [
            'bank_transfer' => 'Virtual Account / transfer bank', 'echannel' => 'Mandiri Bill',
            'credit_card' => 'Kartu kredit', 'gopay' => 'GoPay', 'shopeepay' => 'ShopeePay', 'qris' => 'QRIS',
        ];

        $label = $labels[$t->payment_type] ?? ($t->payment_type ?: '-');

        return $label.($t->installment_term ? " (cicilan {$t->installment_term} bulan)" : '');
    }

    // ---------- Mitra ----------

    public static function partnerApproved(PartnerProfile $p): EventNotification
    {
        $next = $p->partner_type->value === 'architect'
            ? 'Anda sekarang dapat membuat layanan, mengatur jadwal kerja, dan menerima pesanan konsultasi.'
            : 'Anda sekarang dapat memposting dan mengelola properti.';

        return new EventNotification(
            'Akun mitra Anda telah diverifikasi',
            ["Selamat! Akun mitra \"{$p->business_name}\" telah diverifikasi administrator.", $next],
            'Buka dashboard mitra',
            route('partner.dashboard'),
        );
    }

    public static function partnerRejected(PartnerProfile $p): EventNotification
    {
        return new EventNotification(
            'Verifikasi akun mitra Anda ditolak',
            [
                "Pendaftaran mitra \"{$p->business_name}\" belum dapat kami verifikasi.",
                'Alasan: '.($p->rejection_reason ?: '-'),
                'Hubungi administrator bila Anda ingin mengajukan ulang.',
            ],
        );
    }

    // ---------- Properti ----------

    public static function propertyApproved(Property $p): EventNotification
    {
        return new EventNotification(
            'Properti Anda disetujui dan kini tayang',
            ["Properti \"{$p->title}\" telah diverifikasi dan tampil di situs."],
            'Lihat properti',
            route('properties.show', $p->slug),
        );
    }

    public static function propertyRejected(Property $p): EventNotification
    {
        return new EventNotification(
            'Properti Anda ditolak',
            [
                "Properti \"{$p->title}\" belum dapat ditayangkan.",
                'Alasan: '.($p->rejection_reason ?: '-'),
                'Perbaiki data atau foto, lalu ajukan kembali.',
            ],
            'Perbaiki properti',
            route('partner.properties.edit', $p->id),
        );
    }

    // ---------- Konsultasi arsitek ----------

    public static function bookingRequested(ArchitectBooking $b): EventNotification
    {
        $lines = [
            "{$b->client->name} memesan \"{$b->service->title}\".",
            'Jadwal: '.$b->scheduled_start->format('d/m/Y H:i').' – '.$b->scheduled_end->format('H:i'),
            'Pertemuan: '.($b->meeting_type->value === 'online' ? 'Online' : 'Di lokasi'),
        ];

        if ($b->notes) {
            $lines[] = 'Catatan klien: '.$b->notes;
        }

        return new EventNotification('Pesanan konsultasi baru', $lines, 'Tinjau pesanan', route('partner.bookings'));
    }

    public static function bookingConfirmed(ArchitectBooking $b): EventNotification
    {
        return new EventNotification(
            'Pesanan konsultasi Anda dikonfirmasi',
            [
                "Arsitek mengonfirmasi \"{$b->service->title}\" pada ".$b->scheduled_start->format('d/m/Y H:i').'.',
                'Silakan lakukan pembayaran sebesar '.self::rp($b->price).' agar jadwal terjamin.',
            ],
            'Lihat pesanan & bayar',
            route('orders.show', $b->id),
        );
    }

    public static function bookingRejected(ArchitectBooking $b): EventNotification
    {
        return new EventNotification(
            'Pesanan konsultasi Anda ditolak',
            [
                "Arsitek tidak dapat menerima \"{$b->service->title}\" pada ".$b->scheduled_start->format('d/m/Y H:i').'.',
                'Alasan: '.($b->cancel_reason ?: '-'),
                'Anda dapat memilih jadwal lain.',
            ],
            'Lihat pesanan',
            route('orders.show', $b->id),
        );
    }

    public static function bookingCancelled(ArchitectBooking $b, bool $forArchitect): EventNotification
    {
        $who = $forArchitect ? 'Klien' : 'Arsitek';
        $lines = [
            "$who membatalkan \"{$b->service->title}\" (".$b->scheduled_start->format('d/m/Y H:i').').',
        ];

        if ($b->cancel_reason) {
            $lines[] = 'Alasan: '.$b->cancel_reason;
        }

        return new EventNotification(
            'Pesanan konsultasi dibatalkan',
            $lines,
            'Lihat pesanan',
            $forArchitect ? route('partner.bookings') : route('orders.show', $b->id),
        );
    }

    // ---------- Pembayaran ----------

    public static function paymentReceipt(Transaction $t): EventNotification
    {
        return new EventNotification(
            'Pembayaran Anda diterima',
            [
                "Terima kasih! Pembayaran untuk \"{$t->description}\" telah kami terima.",
                'Jumlah: '.self::rp($t->amount),
                'Metode: '.self::methodLabel($t),
                'Kode transaksi: '.$t->code,
            ],
            'Lihat transaksi',
            route('transactions.show', $t->code),
        );
    }

    public static function paymentReceived(Transaction $t): EventNotification
    {
        return new EventNotification(
            'Pembayaran masuk',
            [
                "Ada pembayaran baru untuk \"{$t->description}\".",
                'Jumlah: '.self::rp($t->amount),
                'Kode transaksi: '.$t->code,
            ],
            'Lihat pembayaran masuk',
            route('partner.transactions'),
        );
    }

    public static function paymentRefunded(Transaction $t): EventNotification
    {
        return new EventNotification(
            'Pengembalian dana dicatat',
            [
                "Pengembalian dana untuk \"{$t->description}\" sebesar ".self::rp($t->amount).' telah dicatat.',
                'Waktu dana tiba di rekening Anda bergantung pada bank atau penyedia pembayaran.',
                'Kode transaksi: '.$t->code,
            ],
            'Lihat transaksi',
            route('transactions.show', $t->code),
        );
    }
}
