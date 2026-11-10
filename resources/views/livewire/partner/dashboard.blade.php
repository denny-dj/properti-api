<div class="space-y-8 pb-12">
    
    {{-- Header Editorial & Status Profil Mitra --}}
    <div class="flex flex-col justify-between gap-4 border-b border-slate-200/80 pb-6 sm:flex-row sm:items-end">
        <div>
            <nav class="mb-2 flex items-center gap-2 text-xs font-medium text-slate-400">
                <a href="{{ route('home') }}" class="hover:text-blue-600 transition-colors">Beranda</a>
                <span>/</span>
                <span class="text-slate-700">Portal Mitra</span>
            </nav>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">Ringkasan</h1>
                @if ($profile)
                    <span class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-1 text-xs font-bold text-slate-700 shadow-sm">
                        <span class="h-2 w-2 rounded-full {{ $profile->verification_status->value === 'verified' ? 'bg-emerald-500' : ($profile->verification_status->value === 'pending' ? 'bg-amber-500' : 'bg-rose-500') }}"></span>
                        {{ $profile->business_name }}
                    </span>
                @endif
            </div>
            <p class="mt-1 text-xs sm:text-sm text-slate-500">Pantau performa komersial, aktivitas inventaris, dan arus penerimaan dana kemitraan Anda.</p>
        </div>

        {{-- Aksi Cepat Tambah Listing (Khusus Tipe Seller) --}}
        @if ($seller)
            <div>
                <a href="{{ route('partner.properties.create') }}" 
                   class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-5 py-2.5 text-xs font-bold text-white shadow-md hover:bg-slate-800 transition-all">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Tambah properti</span>
                </a>
            </div>
        @endif
    </div>

    {{-- Banner Status Verifikasi Akun --}}
    @if (! $profile)
        <div class="rounded-2xl border border-rose-200 bg-rose-50 p-5 text-xs text-rose-800 shadow-sm flex items-start gap-3">
            <svg class="h-5 w-5 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <div>
                <strong class="font-bold">Profil Mitra Tidak Ditemukan:</strong> Data keanggotaan belum terdaftar. Hubungi administrator platform.
            </div>
        </div>
    @elseif ($profile->verification_status->value === 'pending')
        <div class="rounded-2xl border border-amber-200 bg-amber-50/70 p-5 text-xs text-amber-900 shadow-sm flex items-start gap-3.5">
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-800 font-bold">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="space-y-1">
                <p class="font-bold text-amber-950">Menunggu Tinjauan Verifikasi Administrator</p>
                <p class="text-amber-800 leading-relaxed">
                    Akun mitra Anda menunggu verifikasi administrator. Setelah disetujui, menu {{ $profile->partner_type->value === 'architect' ? 'layanan, jadwal, dan pesanan' : 'properti' }} akan aktif.
                </p>
            </div>
        </div>
    @elseif ($profile->verification_status->value === 'rejected')
        <div class="rounded-2xl border border-rose-200 bg-rose-50 p-5 text-xs text-rose-900 shadow-sm flex items-start gap-3.5">
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-rose-100 text-rose-700">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </div>
            <div class="space-y-1">
                <p class="font-bold text-rose-950">Pengajuan Kemitraan Ditolak</p>
                <p class="text-rose-800 leading-relaxed">
                    Verifikasi akun Anda ditolak.@if ($profile->rejection_reason) Alasan: {{ $profile->rejection_reason }}@endif Hubungi administrator untuk bantuan.
                </p>
            </div>
        </div>
    @endif

    {{-- KARTU METRIK KEUANGAN (ARUS PENDAPATAN) --}}
    <div class="grid gap-5 sm:grid-cols-2">
        {{-- Pembayaran Diterima --}}
        <div class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm transition-all hover:shadow-md">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Arus Kas Masuk</span>
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </span>
            </div>
            <p class="mt-3 text-xs font-medium text-slate-500">Pembayaran diterima</p>
            <p class="mt-1 font-mono text-2xl sm:text-3xl font-extrabold tracking-tight text-emerald-600">
                <x-rupiah :value="$revenue['paid']" />
            </p>
            <p class="mt-2 text-[11px] text-slate-400">Total nominal transaksi yang telah berhasil dicairkan ke rekening Anda.</p>
        </div>

        {{-- Menunggu Pembayaran --}}
        <div class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm transition-all hover:shadow-md">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Dalam Proses Escrow</span>
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <p class="mt-3 text-xs font-medium text-slate-500">Menunggu pembayaran</p>
            <p class="mt-1 font-mono text-2xl sm:text-3xl font-extrabold tracking-tight text-amber-600">
                <x-rupiah :value="$revenue['pending']" />
            </p>
            <p class="mt-2 text-[11px] text-slate-400">Booking fee atau dana termin yang sedang ditampung pada rekening penampung resmi.</p>
        </div>
    </div>

    {{-- SECTION: INVENTARIS PROPERTI (UNTUK MITRA DEVELOPER / SELLER) --}}
    @if ($seller)
        <section class="space-y-4">
            <div class="flex flex-col justify-between gap-2 sm:flex-row sm:items-end">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Properti</h2>
                    <p class="text-xs text-slate-500">Distribusi status seluruh portofolio unit listing Anda.</p>
                </div>
                <div class="flex items-center gap-2 text-xs font-semibold text-slate-600 bg-white border border-slate-200/80 rounded-xl px-3 py-1.5 shadow-sm">
                    <svg class="h-3.5 w-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <span>Total dilihat: <strong class="text-slate-900">{{ number_format($seller['views'], 0, ',', '.') }}</strong> kali.</span>
                </div>
            </div>

            {{-- Grid Status Listing --}}
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-6">
                @foreach (['draft' => 'Draft', 'pending_review' => 'Menunggu verifikasi', 'published' => 'Tayang', 'reserved' => 'Dipesan', 'rejected' => 'Ditolak', 'sold' => 'Terjual'] as $key => $label)
                    <a href="{{ route('partner.properties.index', ['status' => $key]) }}" 
                       class="group rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm transition-all hover:border-slate-400 hover:shadow-md">
                        <p class="font-mono text-2xl font-extrabold text-slate-900 group-hover:text-blue-600 transition-colors">
                            {{ $seller['by_status'][$key] ?? 0 }}
                        </p>
                        <p class="mt-1 text-[11px] font-semibold text-slate-500 leading-tight">{{ $label }}</p>
                    </a>
                @endforeach
            </div>

            {{-- Tabel Unit Terbaru Ditambahkan --}}
            @if ($recentProperties->isNotEmpty())
                <div class="mt-5 rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm">
                    <div class="mb-4 flex items-center justify-between border-b border-slate-100 pb-3">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Aktivitas Listing Terakhir</h3>
                        <a href="{{ route('partner.properties.index') }}" class="text-xs font-bold text-blue-600 hover:text-blue-700 transition-colors">
                            Lihat Semua Unit &rarr;
                        </a>
                    </div>
                    
                    <div class="divide-y divide-slate-100">
                        @foreach ($recentProperties as $p)
                            <a href="{{ route('partner.properties.edit', $p->id) }}" 
                               class="flex items-center justify-between gap-4 py-3.5 transition-colors hover:bg-slate-50/70 -mx-3 px-3 rounded-xl">
                                <div class="min-w-0 flex items-center gap-3">
                                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-500 text-xs font-mono font-bold">
                                        #{{ $p->id }}
                                    </div>
                                    <span class="truncate text-xs sm:text-sm font-bold text-slate-900 hover:text-blue-600 transition-colors">
                                        {{ $p->title }}
                                    </span>
                                </div>
                                <div class="shrink-0">
                                    <x-status-badge :status="$p->status" context="property" />
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </section>
    @endif

    {{-- SECTION: ARSITEK & DESAIN CETAK BIRU (JIKA TERSEDIA) --}}
    @if ($architect && $design)
        <section class="space-y-4">
            <div class="flex flex-col justify-between gap-2 sm:flex-row sm:items-end">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Desain &amp; serah terima</h2>
                    <p class="text-xs text-slate-500">Status pesanan cetak biru dan saldo pelepasan pembayaran escrow arsitektur.</p>
                </div>
                <a href="{{ route('partner.portfolios.create') }}" 
                   class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-700 shadow-sm hover:bg-slate-50 hover:border-slate-300 transition-all">
                    <span>Tambah portofolio</span> &rarr;
                </a>
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <a href="{{ route('partner.design-orders', ['stage' => 'awaiting_link']) }}" 
                   class="rounded-3xl border border-amber-200 bg-amber-50/60 p-5 shadow-sm transition-all hover:bg-amber-50 hover:shadow-md">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-amber-800">Tindakan Diperlukan</span>
                    <p class="mt-2 font-mono text-3xl font-extrabold text-amber-900">{{ $design['awaiting'] }}</p>
                    <p class="mt-1 text-xs font-semibold text-amber-800">Perlu kirim berkas</p>
                </a>

                <div class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Tertahan Escrow</span>
                    <p class="mt-2 font-mono text-xl sm:text-2xl font-extrabold text-amber-600">
                        <x-rupiah :value="$design['balance']['pending_release']" />
                    </p>
                    <p class="mt-1 text-xs text-slate-500">Menunggu dilepas (belum dikonfirmasi pembeli)</p>
                </div>

                <div class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Dana Cair</span>
                    <p class="mt-2 font-mono text-xl sm:text-2xl font-extrabold text-emerald-600">
                        <x-rupiah :value="$design['balance']['available']" />
                    </p>
                    <p class="mt-1 text-xs text-slate-500">Siap dicairkan</p>
                </div>
            </div>
        </section>
    @endif

    {{-- SECTION: KONSULTASI ARSITEK (JIKA LEGACY UI AKTIF) --}}
    @if ($architect && config('concierge.legacy_ui', true))
        <section class="space-y-4">
            <h2 class="text-lg font-bold text-slate-900">Konsultasi</h2>
            <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
                <a href="{{ route('partner.bookings', ['status' => 'pending']) }}" 
                   class="rounded-2xl border border-amber-200 bg-amber-50/70 p-4 transition-all hover:shadow-md">
                    <p class="font-mono text-2xl font-extrabold text-amber-900">{{ $architect['pending'] }}</p>
                    <p class="text-xs font-semibold text-amber-800">Menunggu konfirmasi</p>
                </a>

                <a href="{{ route('partner.bookings', ['status' => 'confirmed']) }}" 
                   class="rounded-2xl border border-slate-200/80 bg-white p-4 transition-all hover:shadow-md">
                    <p class="font-mono text-2xl font-extrabold text-slate-900">{{ $architect['upcoming'] }}</p>
                    <p class="text-xs text-slate-500">Jadwal mendatang</p>
                </a>

                <div class="rounded-2xl border border-slate-200/80 bg-white p-4">
                    <p class="font-mono text-2xl font-extrabold text-slate-900">{{ $architect['completed'] }}</p>
                    <p class="text-xs text-slate-500">Selesai</p>
                </div>

                <a href="{{ route('partner.services') }}" 
                   class="rounded-2xl border border-slate-200/80 bg-white p-4 transition-all hover:shadow-md">
                    <p class="font-mono text-2xl font-extrabold text-slate-900">{{ $architect['services'] }}</p>
                    <p class="text-xs text-slate-500">Layanan</p>
                </a>
            </div>

            {{-- Jadwal Konsultasi Terdekat --}}
            @if ($upcoming->isNotEmpty())
                <div class="mt-6 rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm">
                    <h3 class="mb-4 text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100 pb-3">Jadwal terdekat</h3>
                    <div class="divide-y divide-slate-100">
                        @foreach ($upcoming as $b)
                            <div class="flex items-center justify-between gap-4 py-3.5">
                                <div class="min-w-0">
                                    <p class="truncate text-xs sm:text-sm font-bold text-slate-900">{{ $b->service?->title }}</p>
                                    <p class="text-xs text-slate-500 mt-0.5">
                                        {{ $b->scheduled_start->format('d/m/Y H:i') }} · <span class="font-semibold text-slate-700">{{ $b->client?->name }}</span>
                                    </p>
                                </div>
                                <div class="shrink-0">
                                    <x-status-badge :status="$b->status" context="booking" />
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </section>
    @endif

</div>