<section class="bg-slate-900 px-4 py-16 text-white">
    <div class="mx-auto max-w-7xl">
        <div class="mb-10 max-w-2xl">
            <h2 class="text-2xl font-bold tracking-tight md:text-3xl">Cara kerja, dari konsultasi sampai serah terima</h2>
            <p class="mt-2 text-sm text-slate-400">Enam langkah transaksi aman didampingi perwakilan platform.</p>
        </div>

        <div class="grid grid-cols-2 gap-4 lg:grid-cols-6">
            @php
                $stepsData = [
                    ['01', 'Registrasi Akun', 'Daftar dan lengkapi data profil Anda.'],
                    ['02', 'Konsultasi & Kunjungan', 'Jadwalkan survei fisik atau konsultasi.'],
                    ['03', 'Pembayaran Tanda Jadi & Desain', 'Dana tanda jadi diamankan di rekening escrow.'],
                    ['04', 'Produksi Workshop Mitra', 'Pengerjaan modul interior / persiapan konstruksi.'],
                    ['05', 'Instalasi di Lokasi', 'Pelaksanaan fisik dengan pengawasan lapangan.'],
                    ['06', 'Serah Terima & Rilis Dana', 'Dana diteruskan ke mitra setelah konfirmasi serah terima.']
                ];
            @endphp

            @foreach ($stepsData as [$no, $title, $desc])
                <div class="rounded-2xl border border-slate-700/50 bg-slate-800/60 p-4">
                    <span class="text-xl font-bold text-emerald-400">{{ $no }}</span>
                    <h3 class="mt-3 text-sm font-semibold text-white">{{ $title }}</h3>
                    <p class="mt-1 text-xs text-slate-400 leading-relaxed">{{ $desc }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>