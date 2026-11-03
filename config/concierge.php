<?php

return [

    // Nomor WhatsApp resmi admin: format internasional tanpa "+" (mis. 6281234567890).
    'admin_wa_number' => env('ADMIN_WA_NUMBER'),

    'lead_prefix' => 'LEAD',

    // true  = tampilan lama masih ada: menu "Arsitek (konsultasi)", tombol "Chat penjual" dan "Bayar booking fee"
    //         langsung di detail properti, serta layanan arsitek di beranda.
    // false = antarmuka concierge murni: seluruh minat diarahkan ke WhatsApp admin.
    'legacy_ui' => (bool) env('CONCIERGE_LEGACY_UI', true),

    // Komisi platform. Persen arsitek dinegosiasikan admin per transaksi dalam rentang min-max.
    'fees' => [
        'property_percent' => 2.0,        // dari HARGA PROPERTI (management fee)
        'architect_default_percent' => 12.5,
        'architect_min_percent' => 10.0,
        'architect_max_percent' => 15.0,  // dari NILAI INVOICE
    ],

    'invoice' => [
        'max_expiry_hours' => 72,
    ],

    // Tautan serah terima berkas hanya boleh ke host berikut (anti-phishing). Tambahkan cloud lain bila perlu.
    'deliverable_hosts' => [
        'drive.google.com',
        'docs.google.com',
        'drive.usercontent.google.com',
    ],
];
