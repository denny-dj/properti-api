{{-- Alur Concierge 6 tahap (kartu). Dipakai di beranda: @include('sections.how-it-works') --}}
@php
    $steps = [
        ['01', 'user', 'Registrasi Akun', 'Daftar sebagai klien dan verifikasi email agar pembayaran dan progres tercatat.', []],
        ['02', 'chat', 'Konsultasi & Kunjungan', 'Pilih cara memulai, didampingi admin lewat WhatsApp.', ['Online', 'Survei', 'Blueprint']],
        ['03', 'wallet', 'Pembayaran Tanda Jadi & Desain', 'Bayar lewat tautan resmi platform. Desain mulai dikerjakan.', []],
        ['04', 'factory', 'Produksi Workshop Mitra', 'Fabrikasi material dan komponen sesuai gambar kerja.', []],
        ['05', 'tools', 'Instalasi di Lokasi', 'Pemasangan oleh tim mitra dengan pengawasan.', []],
        ['06', 'key', 'Serah Terima & Rilis Dana', 'QC dan serah terima. Dana dirilis ke mitra setelah Anda konfirmasi.', []],
    ];
@endphp
<section class="bg-slate-900 px-4 py-16 text-white" aria-labelledby="how-it-works-title">
    <div class="mx-auto max-w-7xl">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-emerald-400">Managed Concierge</p>
                <h2 id="how-it-works-title" class="mt-2 text-2xl font-bold tracking-tight md:text-3xl">Cara kerja, dari konsultasi sampai serah terima</h2>
                <p class="mt-2 max-w-2xl text-slate-300">Satu pintu bersama admin platform. Anda tidak perlu bernegosiasi sendiri, dan uang Anda tidak berpindah sebelum berkas diterima.</p>
            </div>
            <a href="{{ route('how-it-works') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-emerald-300 hover:text-emerald-200">
                Penjelasan lengkap <x-icon name="arrow-right" class="h-4 w-4" />
            </a>
        </div>

        <ol class="mt-10 grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-6">
            @foreach ($steps as [$number, $icon, $title, $text, $chips])
                <li class="rounded-xl border border-slate-700/50 bg-slate-800/60 p-4 text-center">
                    <span class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-blue-600 text-sm font-bold text-white">{{ $number }}</span>
                    <x-icon :name="$icon" class="mx-auto mt-3 h-5 w-5 text-emerald-400" />
                    <h3 class="mt-2 text-sm font-semibold text-white">{{ $title }}</h3>
                    <p class="mt-1 text-xs leading-relaxed text-slate-400">{{ $text }}</p>

                    @if ($chips)
                        <div class="mt-3 flex flex-wrap justify-center gap-1">
                            @foreach ($chips as $chip)
                                <span class="rounded-full bg-slate-700/60 px-2 py-0.5 text-[10px] font-medium text-slate-300">{{ $chip }}</span>
                            @endforeach
                        </div>
                    @endif
                </li>
            @endforeach
        </ol>
    </div>
</section>
