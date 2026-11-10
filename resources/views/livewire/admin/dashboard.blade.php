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

{{-- SATU ROOT ELEMENT TUNGGAL UNTUK LIVEWIRE 3 --}}
<div class="space-y-7 pb-10">
    
    {{-- Header Editorial --}}
    <div class="flex flex-col justify-between gap-4 border-b border-slate-200/80 pb-6 sm:flex-row sm:items-end">
        <div>
            <nav class="mb-2 flex items-center gap-2 text-xs font-medium text-slate-400">
                <a href="{{ route('home') }}" class="hover:text-blue-600 transition-colors">Platform</a>
                <span>/</span>
                <span class="text-slate-700">Pusat Kendali Eksekutif</span>
            </nav>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">Ringkasan</h1>
            <p class="mt-1 text-xs sm:text-sm text-slate-500">Pemantauan sentral moderasi inventaris, arus kas escrow, antrean verifikasi legalitas, dan prospek concierge.</p>
        </div>

        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-1.5 text-xs font-bold text-slate-700 shadow-2xs">
                <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Operasional Aktif</span>
            </span>
        </div>
    </div>

    {{-- SECTION 1: ANTREAN TINDAKAN (ACTION REQUIRED) --}}
    <div class="space-y-3">
        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400">Antrean Memerlukan Tindakan</h2>

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
                    <span class="text-[11px] font-bold uppercase tracking-wider {{ $badges['transactions'] > 0 ? 'text-rose-700' : 'text-slate-400' }}">Escrow &amp; Bukti Bayar</span>
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
    </div>

    {{-- SECTION 2: CONCIERGE, PROSPEK & PENCAIRAN MITRA --}}
    <div class="space-y-3">
        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400">Operasional Concierge &amp; Likuiditas</h2>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            {{-- Prospek Baru --}}
            <a href="{{ route('admin.leads', ['status' => 'new']) }}" 
               class="group rounded-3xl border p-5 shadow-2xs transition-all hover:shadow-md {{ ($badges['leads'] ?? 0) > 0 ? 'border-emerald-300 bg-emerald-50/70 hover:bg-emerald-50' : 'border-slate-200/80 bg-white hover:border-slate-300' }}">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold uppercase tracking-wider {{ ($badges['leads'] ?? 0) > 0 ? 'text-emerald-800' : 'text-slate-400' }}">Akuisisi Prospek</span>
                    <span class="flex h-7 w-7 items-center justify-center rounded-xl {{ ($badges['leads'] ?? 0) > 0 ? 'bg-emerald-200/80 text-emerald-900' : 'bg-slate-100 text-slate-400' }}">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"/></svg>
                    </span>
                </div>
                <p class="mt-3 font-mono text-3xl font-extrabold tracking-tight {{ ($badges['leads'] ?? 0) > 0 ? 'text-emerald-950' : 'text-slate-900' }}">{{ $badges['leads'] ?? 0 }}</p>
                <p class="mt-1 text-xs font-semibold {{ ($badges['leads'] ?? 0) > 0 ? 'text-emerald-800' : 'text-slate-500' }}">Prospek baru (WhatsApp &amp; KPR)</p>
            </a>

            {{-- Saldo Belum Dicairkan --}}
            @if ($outstanding !== null)
                <a href="{{ route('admin.payouts') }}" 
                   class="group rounded-3xl border border-slate-200/80 bg-white p-5 shadow-2xs transition-all hover:border-slate-300 hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Pencairan Dana Mitra</span>
                        <span class="flex h-7 w-7 items-center justify-center rounded-xl bg-amber-50 text-amber-700">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </span>
                    </div>
                    <p class="mt-3 font-mono text-xl sm:text-2xl font-extrabold tracking-tight text-amber-600">
                        <x-rupiah :value="$outstanding" />
                    </p>
                    <p class="mt-1 text-xs font-semibold text-slate-600">Saldo mitra belum dicairkan</p>
                </a>
            @endif

            {{-- Pembuatan Invoice Cepat --}}
            <a href="{{ route('admin.invoices') }}" 
               class="group rounded-3xl border border-blue-200 bg-blue-50/60 p-5 shadow-2xs transition-all hover:bg-blue-50 hover:shadow-md">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-blue-800">Billing Cepat</span>
                    <span class="flex h-7 w-7 items-center justify-center rounded-xl bg-blue-200/80 text-blue-900">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                    </span>
                </div>
                <p class="mt-3 text-lg font-bold text-blue-950">Invoice cepat</p>
                <p class="mt-1 text-xs text-blue-800">Buat tautan pembayaran untuk dikirim via WhatsApp</p>
            </a>
        </div>
    </div>

    {{-- SECTION 3: REKAPITULASI ARUS PENDAPATAN & KEUANGAN --}}
    <div class="overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-6 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h2 class="text-sm font-bold text-slate-900">Performa Finansial Platform</h2>
                <p class="text-xs text-slate-400">Penerimaan kas, piutang invoice aktif, dan total transaksi tervalidasi.</p>
            </div>
            <a href="{{ route('admin.transactions') }}" class="text-xs font-bold text-blue-600 hover:text-blue-700 transition-colors">
                Buka Jurnal Keuangan &rarr;
            </a>
        </div>

        <div class="grid divide-y divide-slate-100 sm:grid-cols-2 sm:divide-y-0 sm:divide-x lg:grid-cols-4">
            {{-- Total Pembayaran Lunas --}}
            <div class="p-6 space-y-1">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Akumulasi</span>
                <p class="font-mono text-2xl font-extrabold tracking-tight text-emerald-600">
                    <x-rupiah :value="$overview['revenue']['paid_total']" />
                </p>
                <p class="text-xs text-slate-500 pt-1">Total pembayaran lunas</p>
            </div>

            {{-- Bulan Ini --}}
            <div class="p-6 space-y-1">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Periode Berjalan</span>
                <p class="font-mono text-2xl font-extrabold tracking-tight text-slate-900">
                    <x-rupiah :value="$overview['revenue']['month_total']" />
                </p>
                <p class="text-xs text-slate-500 pt-1">Bulan ini</p>
            </div>

            {{-- Menunggu Pembayaran --}}
            <div class="p-6 space-y-1">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Piutang Escrow</span>
                <p class="font-mono text-2xl font-extrabold tracking-tight text-amber-600">
                    <x-rupiah :value="$overview['revenue']['pending_total']" />
                </p>
                <p class="text-xs text-slate-500 pt-1">Menunggu pembayaran</p>
            </div>

            {{-- Transaksi Lunas --}}
            <div class="p-6 space-y-1 bg-slate-50/40">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Volume Berhasil</span>
                <p class="font-mono text-2xl font-extrabold tracking-tight text-slate-900">
                    {{ number_format($overview['revenue']['paid_count'], 0, ',', '.') }}
                </p>
                <p class="text-xs text-slate-500 pt-1">Transaksi lunas</p>
            </div>
        </div>
    </div>

    {{-- SECTION 4: DISTRIBUSI PENGGUNA, INVENTARIS, & KONSULTASI --}}
    <div class="grid gap-6 lg:grid-cols-3">
        
        {{-- Pengguna --}}
        <div class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-2xs space-y-4">
            <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Pengguna</h2>
                    <p class="text-[11px] text-slate-400 mt-0.5">Basis data akun terdaftar</p>
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
                    <span>Disuspend</span>
                    <span class="font-mono font-bold">{{ $overview['users']['suspended'] }}</span>
                </li>
            </ul>
        </div>

        {{-- Properti --}}
        <div class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-2xs space-y-4">
            <div class="border-b border-slate-100 pb-3">
                <h2 class="text-sm font-bold text-slate-900">Properti</h2>
                <p class="text-[11px] text-slate-400 mt-0.5">Distribusi siklus listing</p>
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
                        <h2 class="text-sm font-bold text-slate-900">Pesanan konsultasi</h2>
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

    {{-- SECTION 5: DETAIL ANTREAN KURASI AKTIF --}}
    <div class="grid gap-6 lg:grid-cols-2">
        
        {{-- Antrean Verifikasi Mitra --}}
        <div class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-2xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Mitra menunggu verifikasi</h2>
                    <p class="text-[11px] text-slate-400 mt-0.5">Pemeriksaan legalitas profil kemitraan baru</p>
                </div>
                <a href="{{ route('admin.partners') }}" class="text-xs font-bold text-blue-600 hover:text-blue-700 transition-colors">
                    Kelola &rarr;
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
                        Tidak ada antrean.
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Antrean Moderasi Properti --}}
        <div class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-2xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Properti menunggu moderasi</h2>
                    <p class="text-[11px] text-slate-400 mt-0.5">Kurasi listing sebelum dipublikasikan ke portal publik</p>
                </div>
                <a href="{{ route('admin.properties') }}" class="text-xs font-bold text-blue-600 hover:text-blue-700 transition-colors">
                    Kelola &rarr;
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
                        Tidak ada antrean.
                    </div>
                @endforelse
            </div>
        </div>

    </div>

</div>