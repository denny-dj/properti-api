<?php

return [

    // Driver gateway aktif. Saat ini tersedia: "midtrans". (Xendit: lihat catatan di PaymentServiceProvider)
    'default' => env('PAYMENT_GATEWAY', 'midtrans'),

    // Batas waktu pembayaran (jam). Juga menjadi masa "tahan" properti saat checkout.
    'expiry_hours' => 24,

    // Halaman tujuan setelah pembayaran selesai di Snap (URL frontend). Boleh kosong.
    'finish_url' => env('PAYMENT_FINISH_URL'),

    // ASUMSI BISNIS (silakan sesuaikan): booking fee properti = persentase dari harga,
    // dibatasi min/max, dibulatkan ke ribuan.
    'property_booking_fee' => [
        'percent' => 1.0,
        'min' => 1_000_000,
        'max' => 50_000_000,
    ],

    'midtrans' => [
        'server_key' => env('MIDTRANS_SERVER_KEY'),
        'client_key' => env('MIDTRANS_CLIENT_KEY'),
        'is_production' => (bool) env('MIDTRANS_IS_PRODUCTION', false),

        // Metode yang ditampilkan di halaman Snap (VA, kartu kredit, e-wallet, QRIS).
        // Pastikan tiap metode sudah aktif di akun Midtrans Anda.
        'enabled_payments' => [
            'credit_card',
            'bca_va', 'bni_va', 'bri_va', 'permata_va', 'echannel', 'other_va',
            'gopay', 'shopeepay', 'qris',
        ],

        // Cicilan kartu kredit: hanya ditawarkan bila nominal >= batas ini.
        // Bank harus sudah diaktifkan untuk cicilan di akun Midtrans.
        'installment_min_amount' => 500_000,
        'installment_terms' => [
            'bni' => [3, 6, 12],
            'mandiri' => [3, 6, 12],
            'bca' => [3, 6, 12],
            'cimb' => [3, 6, 12],
            'mega' => [3, 6, 12],
            'offline' => [3, 6, 12],
        ],
    ],
];
