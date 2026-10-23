@php
    $methods = ['bank_transfer' => 'Virtual Account / transfer', 'echannel' => 'Mandiri Bill', 'credit_card' => 'Kartu kredit', 'gopay' => 'GoPay', 'shopeepay' => 'ShopeePay', 'qris' => 'QRIS'];
@endphp
<div class="space-y-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold">Pembayaran masuk</h1>
        <select wire:model.live="status" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
            <option value="">Semua status</option>
            <option value="paid">Lunas</option>
            <option value="pending">Menunggu pembayaran</option>
            <option value="expired">Kedaluwarsa</option>
            <option value="failed">Gagal</option>
            <option value="cancelled">Dibatalkan</option>
            <option value="refunded">Dikembalikan</option>
        </select>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div class="rounded-2xl border border-gray-200 bg-white p-5"><p class="text-sm text-gray-500">Total diterima</p><p class="mt-1 text-2xl font-bold text-green-700"><x-rupiah :value="$revenue['paid']" /></p></div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5"><p class="text-sm text-gray-500">Menunggu pembayaran</p><p class="mt-1 text-2xl font-bold text-amber-700"><x-rupiah :value="$revenue['pending']" /></p></div>
    </div>
    <p class="text-xs text-gray-400">Dana masuk ke akun penerima pembayaran platform. Pencairan ke rekening mitra diatur oleh administrator.</p>

    <div class="divide-y divide-gray-100 overflow-hidden rounded-2xl border border-gray-200 bg-white" wire:loading.class="opacity-60">
        @forelse ($transactions as $t)
            <div wire:key="ptrx-{{ $t->id }}" class="flex flex-wrap items-start justify-between gap-4 px-5 py-4">
                <div class="min-w-0">
                    <p class="font-medium">{{ $t->description }}</p>
                    <p class="text-sm text-gray-500">Dari {{ $t->payer?->name }} · {{ $t->created_at->format('d/m/Y H:i') }}</p>
                    <p class="text-xs text-gray-400">{{ $t->code }}@if ($t->payment_type) · {{ $methods[$t->payment_type] ?? $t->payment_type }}@endif @if ($t->installment_term) · cicilan {{ $t->installment_term }} bulan @endif</p>
                </div>
                <div class="text-right">
                    <x-status-badge :status="$t->status" context="transaction" />
                    <p class="mt-2 text-sm font-semibold text-blue-700"><x-rupiah :value="$t->amount" /></p>
                </div>
            </div>
        @empty
            <div class="p-10 text-center text-gray-500">Belum ada pembayaran masuk.</div>
        @endforelse
    </div>

    <div>{{ $transactions->links() }}</div>
</div>
