<div class="space-y-5">
    <h1 class="text-2xl font-bold">Pesanan konsultasi</h1>

    <div class="flex flex-wrap items-center gap-3">
        <select wire:model.live="status" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
            <option value="">Semua status</option>
            <option value="pending">Menunggu konfirmasi</option>
            <option value="confirmed">Dikonfirmasi</option>
            <option value="completed">Selesai</option>
            <option value="cancelled">Dibatalkan</option>
            <option value="rejected">Ditolak</option>
        </select>
        <label class="flex items-center gap-2 text-sm text-gray-600">Dari <input type="date" wire:model.live="from" class="rounded-lg border border-gray-300 px-3 py-2 text-sm"></label>
        <label class="flex items-center gap-2 text-sm text-gray-600">Sampai <input type="date" wire:model.live="to" class="rounded-lg border border-gray-300 px-3 py-2 text-sm"></label>
    </div>

    <div class="divide-y divide-gray-100 overflow-hidden rounded-2xl border border-gray-200 bg-white" wire:loading.class="opacity-60">
        @forelse ($bookings as $b)
            @php $isPaid = in_array($b->id, $paidIds, true); @endphp
            <div wire:key="abk-{{ $b->id }}" class="flex flex-wrap items-start justify-between gap-4 px-5 py-4">
                <div class="min-w-0">
                    <p class="font-medium">{{ $b->service?->title }}</p>
                    <p class="text-sm text-gray-600">{{ $b->scheduled_start->format('d/m/Y H:i') }} – {{ $b->scheduled_end->format('H:i') }} · {{ $b->meeting_type->value === 'online' ? 'Online' : 'Di lokasi' }}</p>
                    <p class="text-sm text-gray-500">Klien: {{ $b->client?->name }} · Arsitek: {{ $b->architect?->partnerProfile?->business_name ?? $b->architect?->name }}</p>
                    <p class="text-xs text-gray-400">{{ $b->code }}</p>
                    @if ($b->cancel_reason)<p class="mt-1 text-sm text-gray-600">Alasan: {{ $b->cancel_reason }}</p>@endif
                </div>
                <div class="text-right">
                    <x-status-badge :status="$b->status" context="booking" />
                    <p class="mt-1 text-sm font-semibold text-blue-700"><x-rupiah :value="$b->price" /></p>
                    <p class="text-xs {{ $isPaid ? 'text-green-700' : 'text-gray-400' }}">{{ $isPaid ? 'Sudah dibayar' : 'Belum dibayar' }}</p>
                </div>
            </div>
        @empty
            <div class="p-10 text-center text-gray-500">Tidak ada pesanan pada filter ini.</div>
        @endforelse
    </div>

    <div>{{ $bookings->links() }}</div>
</div>
