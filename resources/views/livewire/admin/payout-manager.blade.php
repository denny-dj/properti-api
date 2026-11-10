@php 
    $input = 'w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 shadow-2xs transition-all'; 
@endphp

{{-- ROOT ELEMENT TUNGGAL LIVEWIRE 3 --}}
<div class="space-y-8 pb-12">
    
    {{-- Header Editorial --}}
    <div class="flex flex-col justify-between gap-4 border-b border-slate-200/80 pb-6 sm:flex-row sm:items-end">
        <div>
            <nav class="mb-2 flex items-center gap-2 text-xs font-medium text-slate-400">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-blue-600 transition-colors">Pusat Kendali</a>
                <span>/</span>
                <span class="text-slate-700">Manajemen Finansial</span>
            </nav>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">Pencairan Dana Mitra</h1>
            <p class="mt-1 text-xs sm:text-sm text-slate-500">Saldo siap cair merupakan akumulasi dana penyelesaian pesanan yang telah dikonfirmasi klien dikurangi pencairan yang sudah dicatat sebelumnya.</p>
        </div>
    </div>

    {{-- Banner Peringatan Operasional --}}
    <div class="flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50/70 p-4 shadow-sm">
        <svg class="mt-0.5 h-5 w-5 shrink-0 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <div class="space-y-1">
            <h4 class="text-sm font-bold text-amber-900">Catatan Operasional Pencairan Manual</h4>
            <p class="text-xs text-amber-800 leading-relaxed">Data rekening bank milik mitra arsitek atau developer saat ini belum tersimpan secara terpusat di aplikasi. Harap hubungi mitra via WhatsApp untuk meminta rincian rekening tujuan, lakukan transfer internet banking secara manual, lalu isi referensi transfer pada kolom di bawah agar histori mutasi dapat dilacak.</p>
        </div>
    </div>

    {{-- Feedback Notifikasi & Galat --}}
    @if ($notice)
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs font-semibold text-emerald-800 shadow-sm flex items-center gap-2">
            <svg class="h-4 w-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>{{ $notice }}</span>
        </div>
    @endif

    @error('list')
        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-xs font-semibold text-rose-700 shadow-sm">
            {{ $message }}
        </div>
    @enderror

    <div class="grid gap-8 lg:grid-cols-5 lg:items-start">
        
        {{-- KOLOM UTAMA: DAFTAR SALDO & FORM PENCAIRAN --}}
        <div class="lg:col-span-3 space-y-4">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400">Distribusi Saldo &amp; Komisi Kemitraan</h2>
            
            <div class="divide-y divide-slate-100 overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm transition-opacity duration-200" 
                 wire:loading.class="opacity-60">
                @forelse ($balances as $row)
                    @php $p = $row['partner']; @endphp
                    <div wire:key="bal-{{ $p->id }}" class="space-y-4 p-5 sm:p-6 transition-colors hover:bg-slate-50/50">
                        
                        {{-- Baris Rincian Mitra --}}
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0 flex-1 space-y-1">
                                <h3 class="truncate text-sm font-bold text-slate-900">{{ $p->partnerProfile?->business_name ?? $p->name }}</h3>
                                <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-slate-500">
                                    <span>{{ $p->email }}</span>
                                    @if ($p->phone)
                                        <span class="text-slate-300">·</span>
                                        <span class="font-mono">{{ $p->phone }}</span>
                                    @endif
                                </div>
                            </div>
                            
                            {{-- Nominal Saldo Mengendap --}}
                            <div class="text-left sm:text-right shrink-0 border-t border-slate-100 pt-3 sm:border-t-0 sm:pt-0">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Siap Dicairkan</p>
                                <p class="font-mono text-lg sm:text-xl font-extrabold tracking-tight {{ $row['available'] > 0 ? 'text-emerald-600' : 'text-slate-400' }}">
                                    <x-rupiah :value="$row['available']" />
                                </p>
                            </div>
                        </div>

                        {{-- Rincian Akuntansi Saldo --}}
                        <div class="rounded-xl border border-slate-200/80 bg-slate-50 px-3.5 py-2.5 text-[11px] font-semibold text-slate-500 flex flex-wrap gap-x-4 gap-y-2">
                            <span class="inline-flex items-center gap-1.5">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                                <span>Dilepas: <span class="font-mono font-bold text-slate-700"><x-rupiah :value="$row['released']" /></span></span>
                            </span>
                            <span class="inline-flex items-center gap-1.5">
                                <span class="h-1.5 w-1.5 rounded-full bg-blue-400"></span>
                                <span>Tercairkan: <span class="font-mono font-bold text-slate-700"><x-rupiah :value="$row['paid_out']" /></span></span>
                            </span>
                            <span class="inline-flex items-center gap-1.5">
                                <span class="h-1.5 w-1.5 rounded-full bg-amber-400"></span>
                                <span>Belum Dikonfirmasi Klien: <span class="font-mono font-bold text-slate-700"><x-rupiah :value="$row['pending_release']" /></span></span>
                            </span>
                        </div>

                        {{-- Panel Form Aksi Pencairan --}}
                        @if ($selected === $p->id)
                            <div class="rounded-2xl border border-emerald-200/80 bg-emerald-50/50 p-5 shadow-sm space-y-4">
                                <h4 class="text-[11px] font-bold uppercase tracking-wider text-emerald-900 border-b border-emerald-100 pb-2">Catat Eksekusi Mutasi Rekening</h4>
                                <div class="grid gap-4 sm:grid-cols-3">
                                    <div>
                                        <label class="block text-[11px] font-bold text-emerald-900 uppercase tracking-wider mb-1.5">Nominal Transfer (Rp)</label>
                                        <input type="text" inputmode="numeric" wire:model="amount" class="{{ $input }}">
                                        @error('amount')<p class="mt-1 text-[10px] font-semibold text-rose-600">{{ $message }}</p>@enderror
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label class="block text-[11px] font-bold text-emerald-900 uppercase tracking-wider mb-1.5">Bukti Referensi Transaksi</label>
                                        <input type="text" wire:model="reference" class="{{ $input }}" placeholder="mis. BCA 05/11 no. ref 1234567">
                                        @error('reference')<p class="mt-1 text-[10px] font-semibold text-rose-600">{{ $message }}</p>@enderror
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-emerald-900 uppercase tracking-wider mb-1.5">Catatan (Opsional)</label>
                                    <textarea rows="2" wire:model="note" class="{{ $input }}" placeholder="Catatan internal pencairan..."></textarea>
                                    @error('note')<p class="mt-1 text-[10px] font-semibold text-rose-600">{{ $message }}</p>@enderror
                                </div>
                                <div class="flex items-center gap-2 pt-2 border-t border-emerald-100/70">
                                    <button type="button" 
                                            wire:click="record" 
                                            wire:confirm="Simpan pencatatan ini? Pastikan dana telah Anda transfer melalui bank karena form ini tidak otomatis memotong dana gateway." 
                                            class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-4 py-2 text-xs font-bold text-white shadow hover:bg-emerald-700 transition-all cursor-pointer">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        <span>Catat Mutasi</span>
                                    </button>
                                    <button type="button" 
                                            wire:click="cancel" 
                                            class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 transition-all cursor-pointer">
                                        <span>Batal</span>
                                    </button>
                                </div>
                            </div>
                        @elseif ($row['available'] > 0)
                            <div class="pt-1">
                                <button type="button" 
                                        wire:click="startPay({{ $p->id }})" 
                                        class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-1.5 text-xs font-bold text-slate-700 shadow-2xs hover:bg-slate-50 hover:border-slate-300 transition-all cursor-pointer">
                                    <svg class="h-3.5 w-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                    <span>Catat Pencairan</span>
                                </button>
                            </div>
                        @endif

                    </div>
                @empty
                    <div class="p-12 text-center">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <h3 class="mt-4 text-sm font-bold text-slate-900">Belum Ada Catatan Saldo Mitra</h3>
                        <p class="mt-1 text-xs text-slate-500 max-w-xs mx-auto">Saldo komisi akan muncul secara otomatis setelah pembeli menyelesaikan pesanan dan mengonfirmasi penerimaan berkas.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- KOLOM SAMPING: LOG RIWAYAT PENCAIRAN (TICKER) --}}
        <div class="lg:col-span-2 space-y-4">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400">Histori Catatan Transfer</h2>
            
            <div class="divide-y divide-slate-100 overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm">
                @forelse ($history as $h)
                    <div wire:key="hist-{{ $h->id }}" class="flex flex-col gap-2.5 p-4 sm:p-5 transition-colors hover:bg-slate-50/50">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-bold text-slate-900">{{ $h->partner?->partnerProfile?->business_name ?? $h->partner?->name }}</p>
                                <p class="font-mono text-[10px] text-slate-400 mt-0.5">Tercatat: {{ $h->paid_at->format('d/m/Y H:i') }}</p>
                            </div>
                            <p class="shrink-0 font-mono text-sm font-extrabold text-slate-900">
                                <x-rupiah :value="$h->amount" />
                            </p>
                        </div>
                        
                        <div class="space-y-1">
                            @if ($h->reference)
                                <p class="flex items-start gap-1.5 text-[11px] font-semibold text-slate-600">
                                    <svg class="h-3 w-3 mt-0.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span class="truncate">Ref: <span class="font-mono text-slate-800">{{ $h->reference }}</span></span>
                                </p>
                            @endif
                            <p class="flex items-start gap-1.5 text-[11px] font-medium text-slate-500">
                                <svg class="h-3 w-3 mt-0.5 text-slate-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                <span>Auditor: {{ $h->recorder?->name ?? 'Sistem' }}</span>
                            </p>
                            @if ($h->note)
                                <p class="rounded-lg bg-slate-50 border border-slate-100 p-2 mt-1.5 text-[10px] text-slate-500 leading-relaxed italic">
                                    "{{ $h->note }}"
                                </p>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-xs text-slate-400">
                        Belum ada pencairan tercatat.
                    </div>
                @endforelse
            </div>
            
            @if ($history->hasPages())
                <div class="pt-2">
                    {{ $history->links() }}
                </div>
            @endif
        </div>

    </div>
</div>