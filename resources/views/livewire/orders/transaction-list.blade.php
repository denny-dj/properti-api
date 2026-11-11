{{-- ROOT ELEMENT TUNGGAL LIVEWIRE 3 --}}
<div class="mx-auto max-w-5xl px-4 sm:px-6 py-8 sm:py-10 space-y-8 pb-16">
    
    {{-- Header Editorial Pembeli --}}
    <div class="flex flex-col justify-between gap-4 border-b border-slate-200/80 pb-6 sm:flex-row sm:items-end">
        <div>
            <nav class="mb-2 flex items-center gap-2 text-xs font-medium text-slate-400">
                <a href="{{ url('/') }}" class="hover:text-blue-600 transition-colors">Beranda</a>
                <span>/</span>
                <span class="text-slate-700">Aktivitas Saya</span>
            </nav>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">Transaksi &amp; Pembayaran</h1>
            <p class="mt-1 text-xs sm:text-sm text-slate-500">Rekam jejak tagihan konsultasi, pembelian cetak biru desain, dan riwayat mutasi dana di platform.</p>
        </div>

        {{-- Filter Status Transaksi --}}
        <div class="relative w-full sm:w-auto">
            <select wire:model.live="status" 
                    class="w-full sm:w-auto appearance-none rounded-xl border border-slate-200 bg-white py-2.5 pl-4 pr-9 text-xs font-semibold text-slate-700 shadow-2xs focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900 transition-all cursor-pointer">
                <option value="">Semua status transaksi</option>
                <option value="pending">Menunggu pembayaran</option>
                <option value="paid">Lunas</option>
                <option value="expired">Kedaluwarsa</option>
                <option value="failed">Gagal</option>
                <option value="cancelled">Dibatalkan</option>
                <option value="refunded">Dikembalikan</option>
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- DAFTAR MUTASI TRANSAKSI PEMBELI --}}
    <div class="divide-y divide-slate-100 overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm transition-opacity duration-200"
         wire:loading.class="opacity-60">
        @forelse ($transactions as $t)
            <a href="{{ route('transactions.show', $t->code) }}" 
               wire:key="trx-{{ $t->id }}" 
               class="group flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6 transition-all hover:bg-slate-50/60">
                
                {{-- Deskripsi Transaksi --}}
                <div class="min-w-0 flex-1 space-y-2">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="font-mono rounded-md bg-slate-100 px-2.5 py-0.5 text-[10px] font-bold text-slate-700">
                            {{ $t->code }}
                        </span>
                        <x-status-badge :status="$t->status" context="transaction" />
                    </div>

                    <h2 class="text-sm sm:text-base font-bold text-slate-900 group-hover:text-blue-600 transition-colors tracking-tight">
                        {{ $t->description }}
                    </h2>

                    <div class="flex flex-wrap items-center gap-1.5 text-xs text-slate-500 font-medium">
                        <svg class="h-3.5 w-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span class="font-mono">{{ $t->created_at->format('d/m/Y H:i') }} WIB</span>
                    </div>
                </div>

                {{-- Nominal & Indikator Navigasi --}}
                <div class="flex items-center justify-between sm:flex-col sm:items-end sm:justify-center border-t border-slate-100 pt-3 sm:border-t-0 sm:pt-0 shrink-0">
                    <div class="text-left sm:text-right">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Tagihan</span>
                        <p class="font-mono text-base sm:text-lg font-extrabold tracking-tight text-blue-700">
                            <x-rupiah :value="$t->amount" />
                        </p>
                    </div>

                    <div class="flex items-center gap-1.5 text-xs font-semibold text-blue-600 sm:mt-2 group-hover:translate-x-0.5 transition-transform">
                        <span>Rincian mutasi</span>
                        <svg class="h-3.5 w-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </div>
            </a>
        @empty
            <div class="p-12 text-center">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <h3 class="mt-4 text-sm font-bold text-slate-900">Belum ada riwayat transaksi.</h3>
                <p class="mt-1 text-xs text-slate-500 max-w-sm mx-auto">Tagihan dari pesanan konsultasi arsitek atau jasa terkait akan terekam dan dikelola secara aman di halaman ini.</p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="pt-2">
        {{ $transactions->links() }}
    </div>

</div>