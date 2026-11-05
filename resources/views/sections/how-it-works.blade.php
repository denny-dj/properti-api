{{-- Alur Concierge 6 tahap. Dipakai di beranda: @include('sections.how-it-works') --}}
@php
    $steps = [
        ['01', 'user', 'Registrasi Akun', 'Daftar sebagai klien dan verifikasi email agar pembayaran dan progres proyek tercatat.', []],
        ['02', 'chat', 'Konsultasi & Kunjungan', 'Pilih cara memulai, didampingi admin lewat WhatsApp.', ['Online', 'Survei lokasi', 'Blueprint siap bangun']],
        ['03', 'wallet', 'Pembayaran Tanda Jadi & Desain', 'Bayar lewat tautan resmi platform. Desain dan gambar kerja mulai dikerjakan.', []],
        ['04', 'factory', 'Produksi Workshop Mitra', 'Fabrikasi material dan komponen di workshop mitra sesuai gambar kerja.', []],
        ['05', 'tools', 'Instalasi di Lokasi', 'Pemasangan oleh tim mitra di lokasi proyek dengan pengawasan.', []],
        ['06', 'key', 'Serah Terima & Rilis Dana', 'Quality control dan serah terima. Dana dirilis ke mitra setelah Anda mengonfirmasi.', []],
    ];
@endphp
<section class="mt-16 bg-slate-900 py-14 text-white md:py-20" aria-labelledby="how-it-works-title">
    <div class="mx-auto max-w-7xl px-4">
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

        <ol class="relative mt-12 grid gap-8 lg:grid-cols-6 lg:gap-5">
            {{-- konektor horizontal (desktop) --}}
            <span class="absolute left-[8%] right-[8%] top-6 hidden h-px bg-gradient-to-r from-blue-500/10 via-blue-500/70 to-emerald-400/50 lg:block" aria-hidden="true"></span>

            @foreach ($steps as $i => [$number, $icon, $title, $text, $chips])
                <li class="relative pl-16 lg:pl-0 lg:text-center">
                    {{-- konektor vertikal (mobile) --}}
                    @unless ($loop->last)
                        <span class="absolute bottom-[-2rem] left-6 top-12 w-px bg-slate-700 lg:hidden" aria-hidden="true"></span>
                    @endunless

                    <div class="absolute left-0 top-0 flex h-12 w-12 items-center justify-center rounded-full border border-blue-500/50 bg-slate-800 text-sm font-bold text-blue-300 ring-4 ring-slate-900 lg:relative lg:mx-auto">
                        {{ $number }}
                    </div>

                    <x-icon :name="$icon" class="mt-0 hidden h-5 w-5 text-emerald-400 lg:mx-auto lg:mt-4 lg:block" />
                    <h3 class="mt-1 text-base font-semibold lg:mt-2">{{ $title }}</h3>
                    <p class="mt-1 text-sm leading-relaxed text-slate-400">{{ $text }}</p>

                    @if ($chips)
                        <div class="mt-3 flex flex-wrap gap-1.5 lg:justify-center">
                            @foreach ($chips as $chip)
                                <span class="rounded-full bg-slate-800 px-2.5 py-1 text-[11px] font-medium text-slate-300 ring-1 ring-slate-700">{{ $chip }}</span>
                            @endforeach
                        </div>
                    @endif
                </li>
            @endforeach
        </ol>
    </div>
</section>
