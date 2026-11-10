@php
    $methods = [
        'bank_transfer' => 'Virtual Account / Transfer Bank',
        'echannel'      => 'Mandiri Bill',
        'credit_card'   => 'Kartu Kredit',
        'gopay'         => 'GoPay',
        'shopeepay'     => 'ShopeePay',
        'qris'          => 'QRIS',
    ];
    $typeLabels = [
        'property_booking_fee' => 'Booking fee properti',
        'architect_booking'    => 'Konsultasi arsitek',
    ];
@endphp

{{-- ROOT ELEMENT TUNGGAL LIVEWIRE 3 --}}
<div class="space-y-6 pb-12">
    
    {{-- Header Editorial --}}
    <div class="flex flex-col justify-between gap-4 border-b border-slate-200/80 pb-6 sm:flex-row sm:items-end">
        <div>
            <nav class="mb-2 flex items-center gap-2 text-xs font-medium text-slate-400">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-blue-600 transition-colors">Pusat Kendali</a>
                <span>/</span>
                <span class="text-slate-700">Audit Transaksi</span>
            </nav>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">Transaksi</h1>
            <p class="mt-1 text-xs sm:text-sm text-slate-500">Monitoring mutasi rekening penampung escrow, verifikasi gateway Midtrans, audit booking fee, dan rekapitulasi audit.</p>
        </div>
    </div>

    {{-- KOTAK UTILITY EKSPOR CSV KERTAS KERJA KEUANGAN --}}
    <details class="group overflow-hidden rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm transition-all">
        <summary class="flex cursor-pointer items-center justify-between text-xs font-bold text-slate-800 hover:text-blue-600 list-none">
            <div class="flex items-center gap-2.5">
                <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-slate-100 text-slate-600 group-hover:bg-slate-900 group-hover:text-white transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <span>Ekspor CSV untuk kertas kerja keuangan</span>
            </div>
            <span class="rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1 text-[11px] font-semibold text-slate-600 group-open:rotate-180 transition-transform">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </span>
        </summary>

        <div class="border-t border-slate-100 pt-4 mt-4">
            <form method="GET" action="{{ route('admin.transactions.export') }}" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-6">
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Jenis</label>
                    <select name="jenis" class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs font-semibold text-slate-700 focus:bg-white focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900">
                        <option value="properti">Properti (booking fee)</option>
                        <option value="desain">Desain arsitek</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Status</label>
                    <select name="status" class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs font-semibold text-slate-700 focus:bg-white focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900">
                        <option value="">Lunas + dikembalikan</option>
                        <option value="paid">Lunas saja</option>
                        <option value="refunded">Dikembalikan saja</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Dari tanggal</label>
                    <input type="date" name="from" class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs font-semibold text-slate-700 focus:bg-white focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900">
                </div>
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Sampai tanggal</label>
                    <input type="date" name="to" class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs font-semibold text-slate-700 focus:bg-white focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900">
                </div>
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Pemisah kolom</label>
                    <select name="delimiter" class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs font-semibold text-slate-700 focus:bg-white focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900">
                        <option value="semicolon">Titik koma (Excel Indonesia)</option>
                        <option value="comma">Koma (Google Sheets / Excel Inggris)</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 rounded-xl bg-slate-900 px-4 py-2 text-xs font-bold text-white shadow hover:bg-slate-800 transition-all cursor-pointer">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        <span>Unduh CSV</span>
                    </button>
                </div>
            </form>
            <p class="mt-3 text-[11px] text-slate-400 leading-relaxed">
                Jenis "Properti": tempel kolom A sampai J ke sheet PROPERTY_TRANSACTIONS; kolom setelahnya hanya rujukan. Biaya Payment Gateway aktual tidak tersimpan di aplikasi dan harus diisi dari laporan settlement Midtrans.
            </p>
        </div>
    </details>

    {{-- KARTU RINGKASAN FINANSIAL --}}
    <div class="grid gap-4 sm:grid-cols-3">
        {{-- Total Nominal Lunas --}}
        <div class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-2xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Terverifikasi</span>
            <p class="mt-2 font-mono text-xl sm:text-2xl font-extrabold tracking-tight text-emerald-600">
                <x-rupiah :value="$summary['paid_total_amount']" />
            </p>
            <p class="mt-1 text-xs text-slate-500">Lunas (sesuai filter)</p>
        </div>

        {{-- Frekuensi Transaksi Lunas --}}
        <div class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-2xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Volume Berhasil</span>
            <p class="mt-2 font-mono text-xl sm:text-2xl font-extrabold tracking-tight text-slate-900">
                {{ $summary['paid_count'] }}
            </p>
            <p class="mt-1 text-xs text-slate-500">Jumlah transaksi lunas</p>
        </div>

        {{-- Breakdown Status --}}
        <div class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-2xs flex flex-col justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Distribusi Status</span>
            <div class="mt-2 flex flex-wrap gap-1.5">
                @forelse ($summary['by_status'] as $row)
                    <span class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-slate-50 px-2 py-0.5 text-xs text-slate-700">
                        <span class="font-mono font-bold text-slate-900">{{ $row['count'] }}</span>
                        <span class="text-slate-500">{{ $row['status'] }}</span>
                    </span>
                @empty
                    <span class="text-xs text-slate-400">-</span>
                @endforelse
            </div>
            <p class="mt-2 text-xs text-slate-500">Per status</p>
        </div>
    </div>

    {{-- TOOLBAR FILTER INTERAKTIF --}}
    <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex flex-wrap items-center gap-3">
                
                {{-- Filter Status --}}
                <div class="relative w-full sm:w-auto">
                    <select wire:model.live="status" class="w-full sm:w-auto appearance-none rounded-xl border border-slate-200 bg-slate-50/50 py-2 pl-3.5 pr-8 text-xs font-semibold text-slate-700 shadow-sm focus:bg-white focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900 transition-all cursor-pointer">
                        <option value="">Semua status</option>
                        <option value="paid">Lunas</option>
                        <option value="pending">Menunggu pembayaran</option>
                        <option value="expired">Kedaluwarsa</option>
                        <option value="failed">Gagal</option>
                        <option value="cancelled">Dibatalkan</option>
                        <option value="refunded">Dikembalikan</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-slate-400">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>

                {{-- Filter Jenis --}}
                <div class="relative w-full sm:w-auto">
                    <select wire:model.live="type" class="w-full sm:w-auto appearance-none rounded-xl border border-slate-200 bg-slate-50/50 py-2 pl-3.5 pr-8 text-xs font-semibold text-slate-700 shadow-sm focus:bg-white focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900 transition-all cursor-pointer">
                        <option value="">Semua jenis</option>
                        <option value="property_booking_fee">Booking fee properti</option>
                        <option value="architect_booking">Konsultasi arsitek</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-slate-400">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>

                {{-- Checkbox Perlu Ditinjau --}}
                <label class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs font-semibold text-slate-700 cursor-pointer hover:bg-slate-100/60 transition-colors">
                    <input type="checkbox" wire:model.live="needsReview" class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900">
                    <span>Perlu ditinjau saja</span>
                </label>

                {{-- Rentang Tanggal --}}
                <div class="flex flex-wrap items-center gap-2 text-xs text-slate-600">
                    <label class="flex items-center gap-1.5 font-medium">
                        <span>Dari</span>
                        <input type="date" wire:model.live="from" class="rounded-xl border border-slate-200 bg-slate-50/50 px-2.5 py-1.5 text-xs text-slate-700 focus:bg-white focus:border-slate-900 focus:outline-none">
                    </label>
                    <label class="flex items-center gap-1.5 font-medium">
                        <span>Sampai</span>
                        <input type="date" wire:model.live="to" class="rounded-xl border border-slate-200 bg-slate-50/50 px-2.5 py-1.5 text-xs text-slate-700 focus:bg-white focus:border-slate-900 focus:outline-none">
                    </label>
                </div>
            </div>

            {{-- Indikator Livewire --}}
            <div wire:loading class="text-xs font-medium text-slate-400 flex items-center gap-1.5 self-start lg:self-auto">
                <svg class="h-3.5 w-3.5 animate-spin text-slate-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                <span>Memperbarui log transaksi...</span>
            </div>
        </div>
    </div>

    {{-- Feedback Notifikasi & Error --}}
    @if ($notice)
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs font-semibold text-emerald-800 shadow-sm flex items-center gap-2">
            <svg class="h-4 w-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span>{{ $notice }}</span>
        </div>
    @endif

    @error('list')
        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-xs font-semibold text-rose-700 shadow-sm">
            {{ $message }}
        </div>
    @enderror

    {{-- JURNAL BUKU KAS TRANSAKSI --}}
    <div class="divide-y divide-slate-100 overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm transition-opacity duration-200" 
         wire:loading.class="opacity-60">
        @forelse ($transactions as $t)
            <div wire:key="atrx-{{ $t->id }}" class="space-y-4 p-5 sm:p-6 transition-colors hover:bg-slate-50/50">
                
                {{-- Baris Transaksi Utama --}}
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0 flex-1 space-y-1.5">
                        
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="font-mono rounded-md bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-700">
                                {{ $t->code }}
                            </span>
                            <x-status-badge :status="$t->status" context="transaction" />
                            @if ($t->needs_review)
                                <span class="rounded-lg bg-rose-50 border border-rose-200/80 px-2 py-0.5 text-[10px] font-bold text-rose-700">
                                    Perlu ditinjau
                                </span>
                            @endif
                            <span class="rounded-md bg-slate-50 px-2 py-0.5 text-[10px] font-semibold text-slate-500 border border-slate-200/60">
                                {{ $typeLabels[$t->type->value] ?? $t->type->value }}
                            </span>
                        </div>

                        <h2 class="truncate text-sm font-bold text-slate-900">{{ $t->description }}</h2>

                        <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-slate-500">
                            <span class="font-medium text-slate-700">{{ $t->payer?->name ?? 'Anonim' }}</span>
                            <span class="text-slate-300">&rarr;</span>
                            <span class="font-medium text-slate-700">{{ $t->payee?->name ?? '-' }}</span>
                            <span class="text-slate-300">·</span>
                            <span class="font-mono text-slate-400">{{ $t->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>

                    {{-- Nominal & Tombol Standar Terpisah Rapi --}}
                    <div class="flex items-center justify-between gap-4 sm:flex-col sm:items-end sm:justify-center border-t border-slate-100 pt-3 sm:border-t-0 sm:pt-0 shrink-0">
                        <p class="font-mono text-base sm:text-lg font-extrabold tracking-tight text-slate-900">
                            <x-rupiah :value="$t->amount" />
                        </p>
                        
                        <button type="button" 
                                wire:click="toggle({{ $t->id }})" 
                                class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-1.5 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 hover:border-slate-300 hover:text-slate-900 transition-all cursor-pointer">
                            <span>{{ $expandedId === $t->id ? 'Tutup Detail' : 'Detail & Tindakan' }}</span>
                            <svg class="h-3.5 w-3.5 text-slate-400 transition-transform {{ $expandedId === $t->id ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- DRAWER DETAIL INSPEKSI GATEWAY & AUDIT TRAIL --}}
                @if ($expandedId === $t->id)
                    <div class="rounded-3xl border border-slate-200/90 bg-slate-50/60 p-5 sm:p-6 space-y-5">
                        <div class="border-b border-slate-200/70 pb-3">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Detail Rekonsiliasi Gateway Midtrans</h3>
                        </div>

                        <dl class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 text-xs">
                            <div class="rounded-2xl border border-slate-200/80 bg-white p-3.5 shadow-2xs">
                                <dt class="font-bold uppercase tracking-wider text-[10px] text-slate-400">Metode</dt>
                                <dd class="mt-1 font-semibold text-slate-900">
                                    {{ $t->payment_type ? ($methods[$t->payment_type] ?? $t->payment_type) : '-' }}
                                    @if ($t->payment_channel) ({{ strtoupper($t->payment_channel) }})@endif 
                                    @if ($t->installment_term) · cicilan {{ $t->installment_term }} bulan @endif
                                </dd>
                            </div>

                            <div class="rounded-2xl border border-slate-200/80 bg-white p-3.5 shadow-2xs">
                                <dt class="font-bold uppercase tracking-wider text-[10px] text-slate-400">ID transaksi gateway</dt>
                                <dd class="mt-1 font-mono font-semibold text-slate-900">{{ $t->gateway_transaction_id ?: '-' }}</dd>
                            </div>

                            <div class="rounded-2xl border border-slate-200/80 bg-white p-3.5 shadow-2xs">
                                <dt class="font-bold uppercase tracking-wider text-[10px] text-slate-400">Status fraud</dt>
                                <dd class="mt-1 font-semibold text-slate-900">
                                    @if ($t->fraud_status)
                                        <span class="rounded-md px-1.5 py-0.5 text-[11px] font-bold uppercase {{ $t->fraud_status === 'accept' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                            {{ $t->fraud_status }}
                                        </span>
                                    @else
                                        -
                                    @endif
                                </dd>
                            </div>

                            <div class="rounded-2xl border border-slate-200/80 bg-white p-3.5 shadow-2xs">
                                <dt class="font-bold uppercase tracking-wider text-[10px] text-slate-400">Dibayar</dt>
                                <dd class="mt-1 font-mono font-semibold text-slate-900">{{ $t->paid_at?->format('d/m/Y H:i') ?? '-' }}</dd>
                            </div>

                            <div class="rounded-2xl border border-slate-200/80 bg-white p-3.5 shadow-2xs">
                                <dt class="font-bold uppercase tracking-wider text-[10px] text-slate-400">Batas pembayaran</dt>
                                <dd class="mt-1 font-mono font-semibold text-slate-900">{{ $t->expires_at?->format('d/m/Y H:i') ?? '-' }}</dd>
                            </div>

                            <div class="rounded-2xl border border-slate-200/80 bg-white p-3.5 shadow-2xs">
                                <dt class="font-bold uppercase tracking-wider text-[10px] text-slate-400">Objek</dt>
                                <dd class="mt-1 font-semibold text-slate-900 truncate">
                                    @if ($t->property)
                                        {{ $t->property->title }}
                                    @elseif ($t->architectBooking)
                                        {{ $t->architectBooking->code }}
                                    @else 
                                        - 
                                    @endif
                                </dd>
                            </div>

                            @if ($t->reviewed_at)
                                <div class="sm:col-span-2 lg:col-span-3 rounded-2xl border border-slate-200/80 bg-white p-3.5 shadow-2xs">
                                    <dt class="font-bold uppercase tracking-wider text-[10px] text-slate-400">Peninjauan terakhir</dt>
                                    <dd class="mt-1 font-semibold text-slate-900">
                                        {{ $t->reviewed_at->format('d/m/Y H:i') }}
                                        @if ($t->review_note) <span class="text-slate-500 font-normal">— {{ $t->review_note }}</span>@endif
                                    </dd>
                                </div>
                            @endif
                        </dl>

                        {{-- Gateway Raw Payload --}}
                        @if ($t->gateway_payload)
                            <details class="group rounded-2xl border border-slate-200/80 bg-white p-3.5 shadow-2xs">
                                <summary class="cursor-pointer text-xs font-semibold text-slate-600 hover:text-slate-900 list-none flex items-center justify-between">
                                    <span>Payload terakhir dari gateway</span>
                                    <svg class="h-3.5 w-3.5 group-open:rotate-180 transition-transform text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </summary>
                                <pre class="mt-3 max-h-60 overflow-auto rounded-xl bg-slate-900 p-3 font-mono text-[11px] text-slate-200">{{ json_encode($t->gateway_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                            </details>
                        @endif

                        {{-- Tombol Aksi Pengelolaan Escrow --}}
                        <div class="flex flex-wrap items-center gap-2 pt-2 border-t border-slate-200/70">
                            <button type="button" 
                                    wire:click="sync({{ $t->id }})" 
                                    wire:loading.attr="disabled" 
                                    wire:target="sync({{ $t->id }})" 
                                    class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-1.5 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 hover:border-slate-300 transition-all cursor-pointer">
                                <svg class="h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                <span>Sinkronkan dari Midtrans</span>
                            </button>

                            @if ($t->needs_review)
                                <button type="button" 
                                        wire:click="startReview({{ $t->id }}, 'review')" 
                                        class="inline-flex items-center gap-1.5 rounded-xl bg-blue-600 px-3.5 py-1.5 text-xs font-bold text-white shadow hover:bg-blue-700 transition-all cursor-pointer">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    <span>Tandai sudah ditinjau</span>
                                </button>
                            @endif

                            @if ($t->status->value === 'paid')
                                <button type="button" 
                                        wire:click="startReview({{ $t->id }}, 'refund')" 
                                        class="inline-flex items-center gap-1.5 rounded-xl border border-rose-200 bg-rose-50/70 px-3.5 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-100 transition-all cursor-pointer">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                                    <span>Catat pengembalian dana</span>
                                </button>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- FORM TINJAUAN / CATATAN PENGEMBALIAN DANA --}}
                @if ($reviewingId === $t->id)
                    <form wire:submit="submitReview" 
                          class="space-y-3 rounded-3xl border p-5 shadow-sm {{ $reviewMode === 'refund' ? 'border-rose-200 bg-rose-50/70' : 'border-blue-200 bg-blue-50/60' }}">
                        @if ($reviewMode === 'refund')
                            <div class="flex items-start gap-2.5 text-xs text-rose-900 leading-relaxed">
                                <svg class="h-4 w-4 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                <span>Ini hanya <strong>mencatat</strong> bahwa dana sudah dikembalikan. Lakukan pengembalian dana terlebih dahulu di dashboard Midtrans. Untuk booking fee properti yang batal, pastikan penjual sudah melepas reservasi.</span>
                            </div>
                        @endif

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider mb-1.5 {{ $reviewMode === 'refund' ? 'text-rose-950' : 'text-blue-950' }}">
                                Catatan (opsional)
                            </label>
                            <textarea rows="2.5" 
                                      wire:model="reviewNote" 
                                      class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-slate-900 shadow-2xs"></textarea>
                            @error('reviewNote')<p class="mt-1 text-xs font-semibold text-rose-700">{{ $message }}</p>@enderror
                        </div>

                        <div class="flex items-center gap-2 pt-1">
                            <button type="submit" 
                                    class="inline-flex items-center rounded-xl px-4 py-2 text-xs font-bold text-white shadow transition-all cursor-pointer {{ $reviewMode === 'refund' ? 'bg-rose-600 hover:bg-rose-700' : 'bg-blue-600 hover:bg-blue-700' }}">
                                <span>{{ $reviewMode === 'refund' ? 'Catat dikembalikan' : 'Simpan tinjauan' }}</span>
                            </button>
                            <button type="button" 
                                    wire:click="dismissReview" 
                                    class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 transition-all cursor-pointer">
                                <span>Batal</span>
                            </button>
                        </div>
                    </form>
                @endif

            </div>
        @empty
            <div class="p-12 text-center">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                </div>
                <h3 class="mt-4 text-sm font-bold text-slate-900">Tidak ada transaksi pada filter ini.</h3>
                <p class="mt-1 text-xs text-slate-500 max-w-sm mx-auto">Seluruh transaksi pembayaran lunas, pending escrow, dan riwayat refund akan tercatat di sini.</p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="pt-2">
        {{ $transactions->links() }}
    </div>

</div>