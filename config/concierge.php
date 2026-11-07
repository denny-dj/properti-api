<?php

return [

    // Nomor WhatsApp resmi admin: format internasional tanpa "+" (mis. 6281234567890).
    'admin_wa_number' => env('ADMIN_WA_NUMBER'),

    'lead_prefix' => 'LEAD',

    // Foto cadangan kartu portofolio yang belum punya cover (dipilih berdasarkan id agar konsisten).
    // Ganti/tambah URL sesuai kebutuhan; gambar yang gagal dimuat disembunyikan otomatis.
    'fallback_portfolio_images' => [
        'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?auto=format&fit=crop&w=1200&q=80',
    ],

    // Kartu portofolio menampilkan rentang RAB = estimasi ± persen ini (0 = tampilkan angka tunggal).
    'rab_range_percent' => (float) env('CONCIERGE_RAB_RANGE_PERCENT', 10),

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
