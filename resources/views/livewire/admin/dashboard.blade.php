@php
    $propertyLabels = [
        'draft'          => 'Draft',
        'pending_review' => 'Menunggu verifikasi',
        'published'      => 'Tayang',
        'reserved'       => 'Dipesan',
        'rejected'       => 'Ditolak',
        'sold'           => 'Terjual',
    ];
    $bookingLabels = [
        'pending'   => 'Menunggu',
        'confirmed' => 'Dikonfirmasi',
        'completed' => 'Selesai',
        'cancelled' => 'Dibatalkan',
        'rejected'  => 'Ditolak',
    ];
    $roleLabels = [
        'user'    => 'Pembeli',
        'partner' => 'Mitra Bisnis',
        'admin'   => 'Administrator',
    ];
    $pendingBookings = $overview['bookings']['pending'] ?? 0;
@endphp

<div class="space-y-7 pb-10">
    
    {{-- Header Editorial --}}
    <div class="flex flex-col justify-between gap-4 border-b border-slate-200/80 pb-6 sm:flex-row sm:items-end">
        <div>
            <nav class="mb-2 flex items-center gap-2 text-xs font-medium text-slate-400">
                <a href="{{ route('home') }}" class="hover:text-blue-600 transition-colors">Platform</a>
                <span>/</span>
                <span class="text-slate-700">Pusat Kendali Eksekutif</span>
            </nav>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">Ringkasan Sistem</h1>
            <p class="mt-1 text-xs sm:text-sm text-slate-500">Pemantauan sentral moderasi inventaris, arus kas escrow, antrean verifikasi legalitas, dan prospek concierge.</p>
        </div>

        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-700 shadow-2xs">
                <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Operasional Aktif</span>
            </span>
        </div>
    </div>

    {{-- 1. TRIAGE ANTREAN TINDAKAN (ACTION REQUIRED) --}}
    <section class="space-y-3">
        <div class="flex items-center justify-between">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400">Antrean Memerlukan Tindakan</h2>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            {{-- Verifikasi Mitra --}}
            <a href="{{ route('admin.partners') }}" 
               class="group relative overflow-hidden rounded-3xl border p-5 shadow-2xs transition-all hover:shadow-md {{ $badges['partners'] > 0 ? 'border-amber-300 bg-amber-50/70 hover:bg-amber-50' : 'border-slate-200/80 bg-white hover:border-slate-300' }}">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold uppercase tracking-wider {{ $badges['partners'] > 0 ? 'text-amber-800' : 'text-slate-400' }}">Kemitraan Baru</span>
                    <span class="flex h-7 w-7 items-center justify-center rounded-xl {{ $badges['partners'] > 0 ? 'bg-amber-200/80 text-amber-900' : 'bg-slate-100 text-slate-400' }}">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </span>
                </div>
                <p class="mt-3 font-mono text-3xl font-extrabold tracking-tight {{ $badges['partners'] > 0 ? 'text-amber-950' : 'text-slate-900' }}">{{ $badges['partners'] }}</p>
                <p class="mt-1 text-xs font-semibold {{ $badges['partners'] > 0 ? 'text-amber-800' : 'text-slate-500' }}">Mitra menunggu verifikasi</p>
            </a>

            {{-- Moderasi Properti --}}
            <a href="{{ route('admin.properties') }}" 
               class="group relative overflow-hidden rounded-3xl border p-5 shadow-2xs transition-all hover:shadow-md {{ $badges['properties'] > 0 ? 'border-amber-300 bg-amber-50/70 hover:bg-amber-50' : 'border-slate-200/80 bg-white hover:border-slate-300' }}">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold uppercase tracking-wider {{ $badges['properties'] > 0 ? 'text-amber-800' : 'text-slate-400' }}">Kurasi Unit</span>
                    <span class="flex h-7 w-7 items-center justify-center rounded-xl {{ $badges['properties'] > 0 ? 'bg-amber-200/80 text-amber-900' : 'bg-slate-100 text-slate-400' }}">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </span>
                </div>
                <p class="mt-3 font-mono text-3xl font-extrabold tracking-tight {{ $badges['properties'] > 0 ? 'text-amber-950' : 'text-slate-900' }}">{{ $badges['properties'] }}</p>
                <p class="mt-1 text-xs font-semibold {{ $badges['properties'] > 0 ? 'text-amber-800' : 'text-slate-500' }}">Properti menunggu moderasi</p>
            </a>

            {{-- Transaksi Escrow --}}
            <a href="{{ route('admin.transactions', ['needsReview' => 'true']) }}" 
               class="group relative overflow-hidden rounded-3xl border p-5 shadow-2xs transition-all hover:shadow-md {{ $badges['transactions'] > 0 ? 'border-rose-300 bg-rose-50/70 hover:bg-rose-50' : 'border-slate-200/80 bg-white hover:border-slate-300' }}">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold uppercase tracking-wider {{ $badges['transactions'] > 0 ? 'text-rose-700' : 'text-slate-400' }}">Escrow & Bukti Bayar</span>
                    <span class="flex h-7 w-7 items-center justify-center rounded-xl {{ $badges['transactions'] > 0 ? 'bg-rose-200/80 text-rose-800' : 'bg-slate-100 text-slate-400' }}">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </span>
                </div>
                <p class="mt-3 font-mono text-3xl font-extrabold tracking-tight {{ $badges['transactions'] > 0 ? 'text-rose-950' : 'text-slate-900' }}">{{ $badges['transactions'] }}</p>
                <p class="mt-1 text-xs font-semibold {{ $badges['transactions'] > 0 ? 'text-rose-700' : 'text-slate-500' }}">Transaksi perlu ditinjau</p>
            </a>

            {{-- Pesanan Arsitek --}}
            <a href="{{ route('admin.bookings', ['status' => 'pending']) }}" 
               class="group relative overflow-hidden rounded-3xl border border-slate-200/80 bg-white p-5 shadow-2xs transition-all hover:border-slate-300 hover:shadow-md">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Jadwal Sesi</span>
                    <span class="flex h-7 w-7 items-center justify-center rounded-xl bg-slate-100 text-slate-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </span>
                </div>
                <p class="mt-3 font-mono text-3xl font-extrabold tracking-tight text-slate-900">{{ $pendingBookings }}</p>
                <p class="mt-1 text-xs font-semibold text-slate-500">Pesanan menunggu arsitek</p>
            </a>
        </div>
    </section>

    {{-- 2. COMMAND PANEL FINANSIAL TERPADU (MENGHAPUS FRAGMENTASI KOTAK) --}}
    <section class="overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-6 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h2 class="text-sm font-bold text-slate-900">Performa Finansial & Likuiditas Platform</h2>
                <p class="text-xs text-slate-400">Rekapitulasi total penerimaan, piutang escrow, dan alokasi pencairan mitra.</p>
            </div>
            <a href="{{ route('admin.transactions') }}" class="text-xs font-bold text-blue-600 hover:text-blue-700 transition-colors">
                Buka Jurnal Keuangan &rarr;
            </a>
        </div>

        <div class="grid divide-y divide-slate-100 sm:grid-cols-2 sm:divide-y-0 sm:divide-x lg:grid-cols-4">
            {{-- Total Pembayaran Lunas --}}
            <div class="p-6 space-y-1">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Akumulasi Lunas</span>
                <p class="font-mono text-2xl font-extrabold tracking-tight text-emerald-600">
                    <x-rupiah :value="$overview['revenue']['paid_total']" />
                </p>
                <p class="text-[11px] text-slate-500 pt-1">Dari {{ number_format($overview['revenue']['paid_count'], 0, ',', '.') }} transaksi berhasil</p>
            </div>

            {{-- Bulan Ini --}}
            <div class="p-6 space-y-1">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Penerimaan Bulan Ini</span>
                <p class="font-mono text-2xl font-extrabold tracking-tight text-slate-900">
                    <x-rupiah :value="$overview['revenue']['month_total']" />
                </p>
                <p class="text-[11px] text-slate-500 pt-1">Arus kas masuk periode berjalan</p>
            </div>

            {{-- Menunggu Pembayaran --}}
            <div class="p-6 space-y-1">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Piutang Escrow Aktif</span>
                <p class="font-mono text-2xl font-extrabold tracking-tight text-amber-600">
                    <x-rupiah :value="$overview['revenue']['pending_total']" />
                </p>
                <p class="text-[11px] text-slate-500 pt-1">Invoice terbit belum ditransfer</p>
            </div>

            {{-- Saldo Belum Dicairkan --}}
            <div class="p-6 space-y-1 bg-slate-50/40">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Saldo Mitra Tertahan</span>
                <p class="font-mono text-2xl font-extrabold tracking-tight text-slate-800">
                    <x-rupiah :value="$outstanding ?? 0" />
                </p>
                <div class="pt-1">
                    <a href="{{ route('admin.payouts') }}" class="text-[11px] font-bold text-blue-600 hover:underline">
                        Kelola Pencairan &rarr;
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- 3. ALAT CONCIERGE & AKUISISI --}}
    <div class="grid gap-5 sm:grid-cols-2">
        {{-- Prospek Baru --}}
        <a href="{{ route('admin.leads', ['status' => 'new']) }}" 
           class="group flex items-center justify-between gap-4 rounded-3xl border border-slate-200/80 bg-white p-5 shadow-2xs transition-all hover:border-slate-300 hover:shadow-md">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="flex h-2 w-2 rounded-full {{ ($badges['leads'] ?? 0) > 0 ? 'bg-emerald-500' : 'bg-slate-300' }}"></span>
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Pusat Lead Concierge</span>
                </div>
                <h3 class="text-base font-bold text-slate-900 group-hover:text-blue-600 transition-colors">
                    {{ $badges['leads'] ?? 0 }} Prospek Baru Masuk
                </h3>
                <p class="text-xs text-slate-400">Pengajuan simulasi KPR bank dan konsultasi langsung via WhatsApp.</p>
            </div>
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-slate-50 text-slate-700 border border-slate-100 group-hover:bg-slate-900 group-hover:text-white transition-all">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"/></svg>
            </div>
        </a>

        {{-- Invoice Cepat --}}
        <a href="{{ route('admin.invoices') }}" 
           class="group flex items-center justify-between gap-4 rounded-3xl border border-slate-200/80 bg-white p-5 shadow-2xs transition-all hover:border-slate-300 hover:shadow-md">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Billing Seketika</span>
                </div>
                <h3 class="text-base font-bold text-slate-900 group-hover:text-blue-600 transition-colors">
                    Generate Tautan Invoice Cepat
                </h3>
                <p class="text-xs text-slate-400">Buat tagihan transaksi langsung untuk dibagikan ke klien concierge.</p>
            </div>
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-slate-50 text-slate-700 border border-slate-100 group-hover:bg-slate-900 group-hover:text-white transition-all">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
            </div>
        </a>
    </div>

    {{-- 4. DISTRIBUSI PENGGUNA & INVENTARIS --}}
    <div class="grid gap-6 lg:grid-cols-3">
        
        {{-- Pengguna --}}
        <div class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-2xs space-y-4">
            <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Basis Pengguna</h2>
                    <p class="text-[11px] text-slate-400 mt-0.5">Klasifikasi hak akses akun</p>
                </div>
                <span class="font-mono text-xl font-extrabold text-slate-900">
                    {{ number_format($overview['users']['total'], 0, ',', '.') }}
                </span>
            </div>

            <ul class="divide-y divide-slate-100 text-xs">
                @foreach ($roleLabels as $key => $label)
                    <li class="flex items-center justify-between py-2.5">
                        <span class="text-slate-600">{{ $label }}</span>
                        <span class="font-mono font-bold text-slate-900">{{ $overview['users']['by_role'][$key] ?? 0 }}</span>
                    </li>
                @endforeach
                <li class="flex items-center justify-between py-2 text-rose-700 bg-rose-50/60 -mx-2 px-2.5 rounded-xl mt-1.5 font-medium">
                    <span>Akun Disuspend</span>
                    <span class="font-mono font-bold">{{ $overview['users']['suspended'] }}</span>
                </li>
            </ul>
        </div>

        {{-- Inventaris Properti --}}
        <div class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-2xs space-y-4">
            <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Status Inventaris</h2>
                    <p class="text-[11px] text-slate-400 mt-0.5">Distribusi siklus unit listing</p>
                </div>
            </div>

            <ul class="divide-y divide-slate-100 text-xs">
                @foreach ($propertyLabels as $key => $label)
                    <li class="flex items-center justify-between py-2.5">
                        <span class="text-slate-600">{{ $label }}</span>
                        <span class="font-mono font-bold text-slate-900">{{ $overview['properties'][$key] ?? 0 }}</span>
                    </li>
                @endforeach
            </ul>
        </div>

        {{-- Pesanan Konsultasi --}}
        @if (config('concierge.legacy_ui', true))
            <div class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-2xs space-y-4">
                <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Konsultasi Arsitek</h2>
                        <p class="text-[11px] text-slate-400 mt-0.5">Status jadwal temu klien</p>
                    </div>
                </div>

                <ul class="divide-y divide-slate-100 text-xs">
                    @foreach ($bookingLabels as $key => $label)
                        <li class="flex items-center justify-between py-2.5">
                            <span class="text-slate-600">{{ $label }}</span>
                            <span class="font-mono font-bold text-slate-900">{{ $overview['bookings'][$key] ?? 0 }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

    </div>

    {{-- 5. DETAIL ANTREAN KURASI AKTIF --}}
    <div class="grid gap-6 lg:grid-cols-2">
        
        {{-- Antrean Verifikasi Mitra --}}
        <div class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-2xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Mitra Menunggu Verifikasi</h2>
                    <p class="text-[11px] text-slate-400 mt-0.5">Pemeriksaan legalitas profil kemitraan baru</p>
                </div>
                <a href="{{ route('admin.partners') }}" class="text-xs font-bold text-blue-600 hover:text-blue-700 transition-colors">
                    Kelola Antrean &rarr;
                </a>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse ($pendingPartners as $p)
                    <div class="flex items-center justify-between py-3 transition-colors hover:bg-slate-50/70 -mx-2 px-2.5 rounded-xl">
                        <div class="min-w-0 space-y-0.5">
                            <p class="truncate text-xs sm:text-sm font-bold text-slate-900">{{ $p->business_name }}</p>
                            <p class="text-xs text-slate-500">{{ $p->user?->name }} · <span class="font-medium text-slate-700">{{ $p->partner_type->value === 'architect' ? 'Arsitek' : 'Developer / Agen' }}</span></p>
                        </div>
                        <span class="shrink-0 rounded-lg bg-amber-50 border border-amber-200/80 px-2.5 py-1 text-[11px] font-bold text-amber-800">
                            Perlu Verifikasi
                        </span>
                    </div>
                @empty
                    <div class="py-8 text-center text-xs text-slate-400">
                        Tidak ada antrean verifikasi mitra saat ini.
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Antrean Moderasi Properti --}}
        <div class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-2xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Properti Menunggu Moderasi</h2>
                    <p class="text-[11px] text-slate-400 mt-0.5">Kurasi listing sebelum dipublikasikan ke portal publik</p>
                </div>
                <a href="{{ route('admin.properties') }}" class="text-xs font-bold text-blue-600 hover:text-blue-700 transition-colors">
                    Kelola Antrean &rarr;
                </a>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse ($pendingProperties as $p)
                    <div class="flex items-center justify-between py-3 transition-colors hover:bg-slate-50/70 -mx-2 px-2.5 rounded-xl">
                        <div class="min-w-0 space-y-0.5">
                            <p class="truncate text-xs sm:text-sm font-bold text-slate-900">{{ $p->title }}</p>
                            <p class="text-xs text-slate-500">{{ $p->partner?->name }} · <span class="font-medium text-slate-700">{{ $p->city }}</span></p>
                        </div>
                        <span class="shrink-0 rounded-lg bg-amber-50 border border-amber-200/80 px-2.5 py-1 text-[11px] font-bold text-amber-800">
                            Kurasi Listing
                        </span>
                    </div>
                @empty
                    <div class="py-8 text-center text-xs text-slate-400">
                        Tidak ada listing properti yang menunggu kurasi.
                    </div>
                @endforelse
            </div>
        </div>

    </div>

</div>