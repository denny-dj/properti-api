<?php

namespace App\Support;

use App\Models\PartnerPayout;
use App\Models\Transaction;
use App\Notifications\EventNotification;

/** Email untuk alur serah terima berkas. Tautan berkas sengaja TIDAK dikirim lewat email. */
final class ConciergeNotices
{
    private static function rp($v): string
    {
        return 'Rp '.number_format((float) $v, 0, ',', '.');
    }

    /** Ke mitra: pembayaran masuk, segera kirim tautan berkas. */
    public static function deliverableDue(Transaction $t): EventNotification
    {
        return new EventNotification(
            'Pembayaran diterima: kirim tautan berkas desain',
            [
                "Pembeli telah membayar \"{$t->description}\" sebesar ".self::rp($t->amount).'.',
                'Hak bersih Anda setelah komisi platform: '.self::rp($t->net_partner_amount).' (dilepas setelah pembeli mengonfirmasi penerimaan berkas).',
                'Silakan kirim tautan Google Drive berisi berkas final (DED, 3D, RAB) melalui dashboard atau API mitra.',
                'Kode transaksi: '.$t->code,
            ],
            'Buka dashboard mitra',
            route('partner.dashboard'),
        );
    }

    /** Ke pembeli: berkas siap diperiksa. */
    public static function deliverableSubmitted(Transaction $t): EventNotification
    {
        return new EventNotification(
            'Berkas desain Anda siap diperiksa',
            [
                "Arsitek telah mengirim tautan berkas untuk \"{$t->description}\".",
                'Buka halaman transaksi, periksa isi Google Drive, lalu tekan "Konfirmasi Penerimaan Berkas" bila sudah lengkap dan sesuai.',
                'Kode transaksi: '.$t->code,
            ],
            'Periksa berkas',
            route('transactions.show', $t->code),
        );
    }

    /** Ke mitra: admin sudah mentransfer pencairan. */
    public static function payoutRecorded(PartnerPayout $payout): EventNotification
    {
        $lines = [
            'Admin telah mencatat pencairan saldo Anda sebesar '.self::rp($payout->amount).'.',
            'Tanggal: '.$payout->paid_at->format('d/m/Y H:i'),
        ];

        if ($payout->reference) {
            $lines[] = 'Referensi transfer: '.$payout->reference;
        }

        $lines[] = 'Bila dana belum masuk dalam 1x24 jam kerja, hubungi admin dengan menyertakan referensi di atas.';

        return new EventNotification('Pencairan saldo dicatat', $lines, 'Lihat pesanan desain', route('partner.design-orders'));
    }

    /** Ke mitra: dana dilepas. */
    public static function deliverableConfirmed(Transaction $t): EventNotification
    {
        return new EventNotification(
            'Pembeli mengonfirmasi berkas: dana dilepas',
            [
                "Pembeli mengonfirmasi penerimaan berkas untuk \"{$t->description}\".",
                'Dana '.self::rp($t->net_partner_amount).' kini masuk saldo yang dapat dicairkan.',
                'Kode transaksi: '.$t->code,
            ],
            'Lihat pembayaran masuk',
            route('partner.transactions'),
        );
    }
}
