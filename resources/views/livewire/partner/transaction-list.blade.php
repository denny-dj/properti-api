@php
    $methods = [
        'bank_transfer' => 'Virtual Account / Transfer Bank',
        'echannel'      => 'Mandiri Bill',
        'credit_card'   => 'Kartu Kredit',
        'gopay'         => 'GoPay',
        'shopeepay'     => 'ShopeePay',
        'qris'          => 'QRIS',
    ];
@endphp

<div class="space-y-6 pb-8">
    
    {{-- Header Editorial & Status --}}
    <div class="flex flex-col justify-between gap-4 border-b border-slate-200/80 pb-6 sm:flex-row sm:items-end">
        <div>
            <nav class="mb-2 flex items-center gap-2 text-xs font-medium text-slate-400">
                <a href="{{ route('partner.dashboard') }}" class="hover:text-blue-600 transition-colors">Portal Mitra</a>
                <span>/</span>
                <span class="text-slate-700">Keuangan</span>
            </nav>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">Pembayaran masuk</h1>
            <p class="mt-1 text-xs sm:text-sm text-slate-500">Rekapitulasi riwayat transaksi, dana booking fee escrow, dan status pelunasan unit.</p>
        </div>

        {{-- Filter Status Transaksi --}}
        <div class="relative w-full sm:w-56">
            <select wire:model.live="status" 
                    class="w-full appearance-none rounded-xl border border-slate-200 bg-white py-2.5 pl-3.5 pr-9 text-xs font-semibold text-slate-700 shadow-sm focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900 transition-all cursor-pointer">
                <option value="">Semua Status Transaksi</option>
                <option value="paid">Lunas (Selesai)</option>
                <option value="pending">Menunggu Pembayaran</option>
                <option value="expired">Kedaluwarsa</option>
                <option value="failed">Gagal</option>
                <option value="cancelled">Dibatalkan</option>
                <option value="refunded">Dikembalikan (Refund)</option>
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- Kartu Ringkasan Finansial --}}
    <div class="grid gap-5 sm:grid-cols-2">
        {{-- Total Diterima --}}
        <div class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm transition-all hover:shadow-md">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Dana Terverifikasi</span>
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </span>
            </div>
            <p class="mt-3 text-xs font-medium text-slate-500">Total diterima</p>
            <p class="mt-1 font-mono text-2xl sm:text-3xl font-extrabold tracking-tight text-emerald-600">
                <x-rupiah :value="$revenue['paid']" />
            </p>
            <p class="mt-2 text-[11px] text-slate-400">Total akumulasi transaksi yang telah sukses diverifikasi sistem perbankan.</p>
        </div>

        {{-- Menunggu Pembayaran --}}
        <div class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm transition-all hover:shadow-md">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Menunggu Pelunasan</span>
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
            </div>
            <p class="mt-3 text-xs font-medium text-slate-500">Menunggu pembayaran</p>
            <p class="mt-1 font-mono text-2xl sm:text-3xl font-extrabold tracking-tight text-amber-600">
                <x-rupiah :value="$revenue['pending']" />
            </p>
            <p class="mt-2 text-[11px] text-slate-400">Faktur transaksi aktif yang menunggu transfer dari pembeli atau klien.</p>
        </div>
    </div>

    {{-- Catatan Alur Escrow Platform --}}
    <div class="flex items-start gap-2.5 rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-3 text-xs text-slate-500">
        <svg class="h-4 w-4 text-slate-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span>Dana masuk ke rekening penampung resmi platform. Penjadwalan pencairan (*payout*) ke rekening bank mitra dikoordinasikan secara berkala oleh administrator.</span>
    </div>

    {{-- Daftar Riwayat Transaksi Masuk --}}
    <div class="divide-y divide-slate-100 overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm transition-opacity duration-200" 
         wire:loading.class="opacity-60">
        @forelse ($transactions as $t)
            <div wire:key="ptrx-{{ $t->id }}" 
                 class="flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between transition-colors hover:bg-slate-50/60">
                
                {{-- Detail Deskripsi & Pembayar --}}
                <div class="min-w-0 space-y-1.5">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded-md bg-slate-100 px-2 py-0.5 font-mono text-[10px] font-bold text-slate-700">
                            {{ $t->code }}
                        </span>
                        <h2 class="truncate text-sm font-bold text-slate-900">{{ $t->description }}</h2>
                    </div>

                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-slate-500">
                        <span class="font-medium text-slate-700">Oleh: {{ $t->payer?->name ?? 'Pengguna Anonim' }}</span>
                        <span class="text-slate-300">·</span>
                        <span>{{ $t->created_at->format('d/m/Y H:i') }}</span>
                    </div>

                    {{-- Metode Pembayaran & Cicilan --}}
                    <div class="flex flex-wrap items-center gap-2 pt-0.5">
                        @if ($t->payment_type)
                            <span class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2 py-0.5 text-[11px] font-medium text-slate-600 shadow-2xs">
                                <svg class="h-3 w-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                {{ $methods[$t->payment_type] ?? $t->payment_type }}
                            </span>
                        @endif

                        @if ($t->installment_term)
                            <span class="rounded-lg bg-blue-50 border border-blue-100 px-2 py-0.5 text-[11px] font-semibold text-blue-700">
                                Cicilan {{ $t->installment_term }} bulan
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Status & Nominal Transaksi --}}
                <div class="flex items-center justify-between sm:flex-col sm:items-end sm:justify-center border-t border-slate-100 pt-3 sm:border-t-0 sm:pt-0 shrink-0">
                    <x-status-badge :status="$t->status" context="transaction" />
                    <p class="mt-1.5 font-mono text-base font-extrabold tracking-tight text-slate-900">
                        <x-rupiah :value="$t->amount" />
                    </p>
                </div>
            </div>
        @empty
            <div class="p-12 text-center">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                </div>
                <h3 class="mt-4 text-sm font-bold text-slate-900">Belum ada pembayaran masuk.</h3>
                <p class="mt-1 text-xs text-slate-500 max-w-sm mx-auto">Setiap transaksi booking fee atau pelunasan dari pembeli akan tercatat di sini secara otomatis.</p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="pt-2">
        {{ $transactions->links() }}
    </div>

</div>