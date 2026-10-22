@php
    $methods = [
        'bank_transfer' => 'Transfer bank / Virtual Account',
        'echannel' => 'Mandiri Bill Payment',
        'credit_card' => 'Kartu kredit',
        'gopay' => 'GoPay',
        'shopeepay' => 'ShopeePay',
        'qris' => 'QRIS',
    ];
    $isPending = $transaction->status->value === 'pending';
    $expired = $transaction->expires_at && $transaction->expires_at->isPast();
@endphp
<div class="mx-auto max-w-3xl px-4 py-8">
    <a href="{{ route('transactions.index') }}" class="text-sm text-blue-700 hover:underline">&larr; Semua transaksi</a>

    <div class="mt-3 flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold">{{ $transaction->description }}</h1>
            <p class="text-sm text-gray-400">{{ $transaction->code }}</p>
        </div>
        <x-status-badge :status="$transaction->status" context="transaction" />
    </div>

    @error('action')
        <p class="mt-4 rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700">{{ $message }}</p>
    @enderror
    @if ($notice)
        <p class="mt-4 rounded-lg bg-blue-50 px-3 py-2 text-sm text-blue-800">{{ $notice }}</p>
    @endif

    <div class="mt-5 space-y-3 rounded-2xl border border-gray-200 bg-white p-5 text-sm">
        <div class="flex justify-between"><span class="text-gray-500">Jumlah</span><span class="text-lg font-semibold text-blue-700"><x-rupiah :value="$transaction->amount" /></span></div>
        <div class="flex justify-between"><span class="text-gray-500">Jenis</span><span class="font-medium">{{ $transaction->type->value === 'property_booking_fee' ? 'Booking fee properti' : 'Konsultasi arsitek' }}</span></div>
        @if ($transaction->property)
            <div class="flex justify-between gap-6"><span class="text-gray-500">Properti</span><span class="text-right font-medium">{{ $transaction->property->title }}</span></div>
        @endif
        @if ($transaction->architectBooking)
            <div class="flex justify-between"><span class="text-gray-500">Pesanan</span><a href="{{ route('orders.show', $transaction->architectBooking->id) }}" class="font-medium text-blue-700 hover:underline">{{ $transaction->architectBooking->code }}</a></div>
        @endif
        @if ($transaction->payee)
            <div class="flex justify-between"><span class="text-gray-500">Penerima</span><span class="font-medium">{{ $transaction->payee->name }}</span></div>
        @endif
        @if ($transaction->payment_type)
            <div class="flex justify-between"><span class="text-gray-500">Metode</span>
                <span class="font-medium">{{ $methods[$transaction->payment_type] ?? $transaction->payment_type }}@if ($transaction->payment_channel) ({{ strtoupper($transaction->payment_channel) }})@endif @if ($transaction->installment_term) · cicilan {{ $transaction->installment_term }} bulan @endif</span>
            </div>
        @endif
        <div class="flex justify-between"><span class="text-gray-500">Dibuat</span><span>{{ $transaction->created_at->format('d/m/Y H:i') }}</span></div>
        @if ($transaction->paid_at)
            <div class="flex justify-between"><span class="text-gray-500">Dibayar</span><span>{{ $transaction->paid_at->format('d/m/Y H:i') }}</span></div>
        @elseif ($isPending && $transaction->expires_at)
            <div class="flex justify-between"><span class="text-gray-500">Batas pembayaran</span><span class="{{ $expired ? 'text-red-600' : '' }}">{{ $transaction->expires_at->format('d/m/Y H:i') }}{{ $expired ? ' (lewat)' : '' }}</span></div>
        @endif
    </div>

    <div class="mt-5 flex flex-wrap gap-3">
        @if ($isPending && ! $expired)
            <button type="button" wire:click="continuePay" wire:loading.attr="disabled" class="rounded-lg bg-blue-600 px-5 py-2.5 font-medium text-white hover:bg-blue-700">Lanjutkan pembayaran</button>
        @endif
        @if ($isPending || $transaction->status->value === 'expired')
            <button type="button" wire:click="refreshStatus" wire:loading.attr="disabled" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium hover:bg-gray-50">
                <span wire:loading.remove wire:target="refreshStatus">Cek status pembayaran</span>
                <span wire:loading wire:target="refreshStatus">Memeriksa...</span>
            </button>
        @endif
    </div>

    @if ($transaction->status->value === 'paid' && $transaction->type->value === 'property_booking_fee')
        <p class="mt-5 rounded-xl bg-green-50 p-4 text-sm text-green-800">Booking fee diterima. Properti kini berstatus dipesan atas nama Anda; penjual akan menindaklanjuti melalui chat.</p>
    @endif
</div>
