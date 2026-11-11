@php
    $methods = [
        'bank_transfer' => 'Transfer bank / Virtual Account',
        'echannel'      => 'Mandiri Bill Payment',
        'credit_card'   => 'Kartu kredit',
        'gopay'         => 'GoPay',
        'shopeepay'     => 'ShopeePay',
        'qris'          => 'QRIS',
    ];
    $isPending = $transaction->status->value === 'pending';
    $expired = $transaction->expires_at && $transaction->expires_at->isPast();
@endphp

{{-- ROOT ELEMENT TUNGGAL LIVEWIRE 3 --}}
<div class="mx-auto max-w-4xl px-4 sm:px-6 py-8 sm:py-10 space-y-8 pb-16">
    
    {{-- Navigasi Kembali & Header Editorial --}}
    <div class="space-y-4 border-b border-slate-200/80 pb-6">
        <a href="{{ route('transactions.index') }}" 
           class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-900 transition-colors">
            <span>&larr; Semua transaksi</span>
        </a>

        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="space-y-1.5">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="font-mono rounded-md bg-slate-100 px-2.5 py-0.5 text-xs font-bold text-slate-700">
                        {{ $transaction->code }}
                    </span>
                    <span class="rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-slate-600">
                        {{ $transaction->type->value === 'property_booking_fee' ? 'Booking fee properti' : 'Konsultasi arsitek' }}
                    </span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">
                    {{ $transaction->description }}
                </h1>
                <p class="text-xs text-slate-400 font-mono">{{ $transaction->code }}</p>
            </div>

            <div class="shrink-0 pt-1">
                <x-status-badge :status="$transaction->status" context="transaction" />
            </div>
        </div>
    </div>

    {{-- Feedback Galat & Notifikasi --}}
    @error('action')
        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-xs font-semibold text-rose-700 shadow-sm flex items-center gap-2">
            <svg class="h-4 w-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <span>{{ $message }}</span>
        </div>
    @enderror

    @if ($notice)
        <div class="rounded-2xl border border-blue-200 bg-blue-50/70 px-4 py-3 text-xs font-semibold text-blue-900 shadow-sm flex items-center gap-2">
            <svg class="h-4 w-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ $notice }}</span>
        </div>
    @endif

    {{-- KARTU DETAIL PARAMETER TRANSAKSI --}}
    <div class="overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm">
        <div class="divide-y divide-slate-100 px-6 text-xs sm:text-sm">
            {{-- Jumlah Tagihan --}}
            <div class="flex items-center justify-between py-4">
                <span class="text-slate-500 font-medium">Jumlah</span>
                <span class="font-mono text-xl sm:text-2xl font-extrabold text-blue-700">
                    <x-rupiah :value="$transaction->amount" />
                </span>
            </div>

            {{-- Jenis Transaksi --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 py-3.5">
                <span class="text-slate-500 font-medium">Jenis</span>
                <span class="font-medium text-slate-900">
                    {{ $transaction->type->value === 'property_booking_fee' ? 'Booking fee properti' : 'Konsultasi arsitek' }}
                </span>
            </div>

            {{-- Properti Terkait --}}
            @if ($transaction->property)
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2 py-3.5">
                    <span class="text-slate-500 font-medium shrink-0">Properti</span>
                    <span class="sm:text-right font-medium text-slate-900 leading-relaxed">{{ $transaction->property->title }}</span>
                </div>
            @endif

            {{-- Pesanan Terkait --}}
            @if ($transaction->architectBooking)
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 py-3.5">
                    <span class="text-slate-500 font-medium">Pesanan</span>
                    <a href="{{ route('orders.show', $transaction->architectBooking->id) }}" 
                       class="inline-flex items-center gap-1 font-mono font-medium text-blue-700 hover:underline">
                        <span>{{ $transaction->architectBooking->code }}</span>
                    </a>
                </div>
            @endif

            {{-- Mitra Penerima --}}
            @if ($transaction->payee)
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 py-3.5">
                    <span class="text-slate-500 font-medium">Penerima</span>
                    <span class="font-medium text-slate-900">{{ $transaction->payee->name }}</span>
                </div>
            @endif

            {{-- Metode Pembayaran --}}
            @if ($transaction->payment_type)
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 py-3.5">
                    <span class="text-slate-500 font-medium">Metode</span>
                    <div class="sm:text-right font-medium text-slate-900">
                        <span>{{ $methods[$transaction->payment_type] ?? $transaction->payment_type }}</span>
                        @if ($transaction->payment_channel)
                            <span class="font-mono text-xs uppercase text-slate-500">({{ strtoupper($transaction->payment_channel) }})</span>
                        @endif
                        @if ($transaction->installment_term)
                            <span class="text-slate-600">· cicilan {{ $transaction->installment_term }} bulan</span>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Waktu Dibuat --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 py-3.5">
                <span class="text-slate-500 font-medium">Dibuat</span>
                <span class="font-mono text-slate-700">{{ $transaction->created_at->format('d/m/Y H:i') }}</span>
            </div>

            {{-- Waktu Pelunasan / Batas Pembayaran --}}
            @if ($transaction->paid_at)
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 py-3.5">
                    <span class="text-slate-500 font-medium">Dibayar</span>
                    <span class="font-mono font-bold text-emerald-600">{{ $transaction->paid_at->format('d/m/Y H:i') }}</span>
                </div>
            @elseif ($isPending && $transaction->expires_at)
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 py-3.5">
                    <span class="text-slate-500 font-medium">Batas pembayaran</span>
                    <div class="font-mono font-semibold {{ $expired ? 'text-red-600' : 'text-slate-800' }}">
                        <span>{{ $transaction->expires_at->format('d/m/Y H:i') }}</span>
                        @if ($expired)
                            <span> (lewat)</span>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- KARTU PELEPASAN ESCROW BERKAS DESAIN ARSITEK --}}
    @if (in_array($transaction->type->value, ['architect_blueprint', 'architect_custom'], true) && $transaction->status->value === 'paid')
        <div class="rounded-3xl border border-emerald-200 bg-emerald-50/50 p-6 sm:p-7 shadow-sm space-y-3">
            <h2 class="font-semibold text-emerald-950">Berkas desain</h2>
            
            @if (! $transaction->deliverable_link)
                <p class="text-xs sm:text-sm text-emerald-900 leading-relaxed">
                    Pembayaran diterima. Menunggu arsitek mengirim tautan berkas (DED, 3D, RAB).
                </p>
            @else
                <p class="text-xs sm:text-sm text-emerald-900 leading-relaxed">
                    Periksa isi folder berikut. Tekan konfirmasi hanya bila berkas sudah lengkap dan sesuai; konfirmasi melepas dana ke arsitek.
                </p>

                <div class="pt-1">
                    <a href="{{ $transaction->deliverable_link }}" 
                       target="_blank" 
                       rel="noopener noreferrer nofollow" 
                       class="inline-block break-all font-mono text-xs sm:text-sm font-medium text-blue-700 underline">
                        {{ $transaction->deliverable_link }}
                    </a>
                </div>

                @if ($transaction->client_confirmed_at)
                    <p class="text-xs sm:text-sm font-medium text-emerald-950 pt-2">
                        Dikonfirmasi pada {{ $transaction->client_confirmed_at->format('d/m/Y H:i') }}.
                    </p>
                @elseif ($transaction->user_id === auth()->id())
                    <div class="pt-2">
                        <button type="button" 
                                wire:click="confirmDeliverable" 
                                wire:loading.attr="disabled"
                                wire:confirm="Konfirmasi bahwa berkas sudah diterima dan sesuai? Dana akan dilepas ke arsitek."
                                class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-xs font-bold text-white shadow hover:bg-emerald-700 transition-all cursor-pointer disabled:opacity-50">
                            Konfirmasi Penerimaan Berkas
                        </button>
                    </div>
                @endif
            @endif
        </div>
    @endif

    {{-- KELOMPOK TOMBOL AKSI PEMBAYARAN --}}
    <div class="flex flex-wrap items-center gap-3 pt-2">
        @if ($isPending && ! $expired)
            <button type="button" 
                    wire:click="continuePay" 
                    wire:loading.attr="disabled" 
                    class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-xs font-medium text-white shadow hover:bg-blue-700 transition-all cursor-pointer disabled:opacity-50">
                <span>Lanjutkan pembayaran</span>
            </button>
        @endif

        @if ($isPending || $transaction->status->value === 'expired')
            <button type="button" 
                    wire:click="refreshStatus" 
                    wire:loading.attr="disabled" 
                    class="inline-flex items-center gap-1.5 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-xs font-medium text-slate-700 shadow-2xs hover:bg-slate-50 transition-all cursor-pointer disabled:opacity-50">
                <span wire:loading.remove wire:target="refreshStatus">Cek status pembayaran</span>
                <span wire:loading wire:target="refreshStatus">Memeriksa...</span>
            </button>
        @endif
    </div>

    {{-- NOTIFIKASI BOOKING FEE PROPERTI LUNAS --}}
    @if ($transaction->status->value === 'paid' && $transaction->type->value === 'property_booking_fee')
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-xs sm:text-sm text-emerald-800 leading-relaxed shadow-2xs">
            Booking fee diterima. Properti kini berstatus dipesan atas nama Anda; penjual akan menindaklanjuti melalui chat.
        </div>
    @endif

</div>