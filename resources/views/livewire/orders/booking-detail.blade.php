{{-- ROOT ELEMENT TUNGGAL LIVEWIRE 3 --}}
<div class="mx-auto max-w-4xl px-4 sm:px-6 py-8 sm:py-10 space-y-8 pb-16">
    
    {{-- Navigasi Kembali & Header Editorial --}}
    <div class="space-y-4 border-b border-slate-200/80 pb-6">
        <a href="{{ route('orders.index') }}" 
           class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-900 transition-colors">
            <span>&larr; Semua pesanan</span>
        </a>

        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="space-y-1.5">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="font-mono rounded-md bg-slate-100 px-2.5 py-0.5 text-xs font-bold text-slate-700">
                        {{ $booking->code }}
                    </span>
                    <span class="rounded-lg border px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ $booking->meeting_type->value === 'online' ? 'border-indigo-200/80 bg-indigo-50 text-indigo-700' : 'border-emerald-200/80 bg-emerald-50 text-emerald-700' }}">
                        Sesi {{ $booking->meeting_type->value === 'online' ? 'Online' : 'Di lokasi' }}
                    </span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">
                    {{ $booking->service?->title }}
                </h1>
                <p class="text-xs text-slate-400 font-mono">{{ $booking->code }}</p>
            </div>

            <div class="shrink-0 pt-1">
                <x-status-badge :status="$booking->status" context="booking" />
            </div>
        </div>
    </div>

    {{-- Feedback Galat Aksi --}}
    @error('action')
        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-xs font-semibold text-rose-700 shadow-sm flex items-center gap-2">
            <svg class="h-4 w-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <span>{{ $message }}</span>
        </div>
    @enderror

    {{-- KARTU DETAIL SESI & ARSITEK --}}
    <div class="overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm">
        <div class="divide-y divide-slate-100 px-6 text-xs sm:text-sm">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 py-3.5">
                <span class="text-slate-400 font-medium">Arsitek</span>
                <span class="font-bold text-slate-900">{{ $booking->architect?->partnerProfile?->business_name ?? $booking->architect?->name }}</span>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 py-3.5">
                <span class="text-slate-400 font-medium">Jadwal</span>
                <span class="font-mono font-semibold text-slate-800">{{ $booking->scheduled_start->format('d/m/Y H:i') }} – {{ $booking->scheduled_end->format('H:i') }}</span>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 py-3.5">
                <span class="text-slate-400 font-medium">Pertemuan</span>
                <span class="font-semibold text-slate-800">{{ $booking->meeting_type->value === 'online' ? 'Online' : 'Di lokasi' }}</span>
            </div>

            @if ($booking->location)
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2 py-3.5">
                    <span class="text-slate-400 font-medium shrink-0">Lokasi</span>
                    <span class="sm:text-right font-medium text-slate-800 leading-relaxed">{{ $booking->location }}</span>
                </div>
            @endif

            @if ($booking->notes)
                <div class="py-3.5 space-y-1">
                    <span class="text-slate-400 font-medium block">Catatan</span>
                    <div class="rounded-2xl border border-slate-200/70 bg-slate-50/60 p-3.5 font-medium text-slate-700 leading-relaxed whitespace-pre-line text-xs">
                        {{ $booking->notes }}
                    </div>
                </div>
            @endif

            <div class="flex items-center justify-between py-4">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Biaya</span>
                <span class="font-mono text-lg sm:text-xl font-extrabold text-blue-700">
                    <x-rupiah :value="$booking->price" />
                </span>
            </div>

            @if ($booking->cancel_reason)
                <div class="py-4">
                    <div class="rounded-2xl border border-rose-200 bg-rose-50/70 p-4 text-xs space-y-1">
                        <span class="font-bold uppercase tracking-wider text-rose-900">
                            Alasan {{ $booking->status->value === 'rejected' ? 'penolakan' : 'pembatalan' }}
                        </span>
                        <p class="font-medium text-rose-800 leading-relaxed">{{ $booking->cancel_reason }}</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- KARTU PEMBAYARAN --}}
    <div class="rounded-3xl border border-slate-200/80 bg-white p-6 sm:p-7 shadow-sm space-y-3">
        <h2 class="font-semibold text-slate-900">Pembayaran</h2>

        @if ($paid)
            <p class="text-xs sm:text-sm text-emerald-700 font-medium">
                Pesanan ini sudah dibayar. 
                <a href="{{ route('transactions.show', $transaction->code) }}" class="font-bold text-emerald-800 underline">Lihat transaksi</a>
            </p>
        @elseif ($canPay)
            <div class="space-y-4">
                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                    @if ($transaction && $transaction->status->value === 'pending')
                        Anda memiliki tagihan yang belum dibayar.
                    @else
                        Arsitek sudah mengonfirmasi jadwal. Silakan lakukan pembayaran.
                    @endif
                </p>

                <button type="button" 
                        wire:click="pay" 
                        wire:loading.attr="disabled" 
                        class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-6 py-2.5 text-xs font-bold text-white shadow hover:bg-slate-800 transition-all cursor-pointer disabled:opacity-50">
                    <span wire:loading.remove wire:target="pay">{{ $transaction && $transaction->status->value === 'pending' ? 'Lanjutkan pembayaran' : 'Bayar sekarang' }}</span>
                    <span wire:loading wire:target="pay" class="flex items-center gap-1.5">
                        <svg class="h-3.5 w-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        <span>Menyiapkan tagihan...</span>
                    </span>
                </button>
            </div>
        @elseif ($booking->status->value === 'pending')
            <p class="text-xs sm:text-sm text-slate-500">Pembayaran dibuka setelah arsitek mengonfirmasi jadwal.</p>
        @else
            <p class="text-xs sm:text-sm text-slate-500">Tidak ada tagihan untuk pesanan ini.</p>
        @endif
    </div>

    {{-- KELOMPOK TOMBOL AKSI UTAMA --}}
    <div class="flex flex-wrap items-center gap-3 pt-2">
        <button type="button" 
                wire:click="chat" 
                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 hover:border-slate-300 transition-all cursor-pointer">
            <svg class="h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
            <span>Chat arsitek</span>
        </button>

        @if ($canCancel)
            <button type="button" 
                    wire:click="$toggle('showCancel')" 
                    class="inline-flex items-center gap-1.5 rounded-xl border border-rose-200 bg-rose-50/70 px-4 py-2.5 text-xs font-bold text-rose-700 hover:bg-rose-100 transition-all cursor-pointer">
                <span>Batalkan pesanan</span>
            </button>
        @endif
    </div>

    {{-- DRAWER FORMULIR PEMBATALAN PESANAN --}}
    @if ($canCancel && $showCancel)
        <form wire:submit="cancel" 
              class="space-y-4 rounded-3xl border border-rose-200 bg-rose-50/60 p-5 sm:p-6 shadow-sm">
            @if ($paid)
                <p class="text-xs sm:text-sm text-rose-800 leading-relaxed">
                    Pesanan ini sudah dibayar. Pembatalan tidak mengembalikan dana secara otomatis; pengembalian diproses manual oleh admin.
                </p>
            @endif

            <div class="space-y-1.5">
                <label class="block text-xs font-bold uppercase tracking-wider text-rose-950">Alasan pembatalan (opsional)</label>
                <textarea rows="3" 
                          wire:model="cancelReason" 
                          class="w-full rounded-xl border border-rose-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500 shadow-2xs"></textarea>
            </div>

            <div class="flex items-center gap-2 pt-1">
                <button type="submit" 
                        class="inline-flex items-center rounded-xl bg-rose-600 px-5 py-2.5 text-xs font-bold text-white shadow hover:bg-rose-700 transition-all cursor-pointer">
                    <span>Ya, batalkan pesanan</span>
                </button>
            </div>
        </form>
    @endif

</div>