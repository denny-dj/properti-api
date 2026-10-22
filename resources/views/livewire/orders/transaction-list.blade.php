<div class="mx-auto max-w-4xl px-4 py-8">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold">Transaksi</h1>
        <select wire:model.live="status" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
            <option value="">Semua status</option>
            <option value="pending">Menunggu pembayaran</option>
            <option value="paid">Lunas</option>
            <option value="expired">Kedaluwarsa</option>
            <option value="failed">Gagal</option>
            <option value="cancelled">Dibatalkan</option>
            <option value="refunded">Dikembalikan</option>
        </select>
    </div>

    <div class="mt-5 divide-y divide-gray-100 overflow-hidden rounded-2xl border border-gray-200 bg-white">
        @forelse ($transactions as $t)
            <a href="{{ route('transactions.show', $t->code) }}" wire:key="trx-{{ $t->id }}" class="flex items-start justify-between gap-4 px-5 py-4 hover:bg-gray-50">
                <div class="min-w-0">
                    <p class="font-medium text-gray-900">{{ $t->description }}</p>
                    <p class="text-xs text-gray-400">{{ $t->code }} · {{ $t->created_at->format('d/m/Y H:i') }}</p>
                </div>
                <div class="shrink-0 text-right">
                    <x-status-badge :status="$t->status" context="transaction" />
                    <p class="mt-2 text-sm font-semibold text-blue-700"><x-rupiah :value="$t->amount" /></p>
                </div>
            </a>
        @empty
            <div class="p-10 text-center text-gray-500">Belum ada transaksi.</div>
        @endforelse
    </div>

    <div class="mt-6">{{ $transactions->links() }}</div>
</div>
