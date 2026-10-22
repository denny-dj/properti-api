<div class="mx-auto max-w-4xl px-4 py-8">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold">Pesanan konsultasi</h1>
        <select wire:model.live="status" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
            <option value="">Semua status</option>
            <option value="pending">Menunggu konfirmasi</option>
            <option value="confirmed">Dikonfirmasi</option>
            <option value="completed">Selesai</option>
            <option value="cancelled">Dibatalkan</option>
            <option value="rejected">Ditolak</option>
        </select>
    </div>

    <div class="mt-5 divide-y divide-gray-100 overflow-hidden rounded-2xl border border-gray-200 bg-white">
        @forelse ($bookings as $booking)
            <a href="{{ route('orders.show', $booking->id) }}" wire:key="booking-{{ $booking->id }}" class="flex items-start justify-between gap-4 px-5 py-4 hover:bg-gray-50">
                <div class="min-w-0">
                    <p class="font-medium text-gray-900">{{ $booking->service?->title }}</p>
                    <p class="text-sm text-gray-500">{{ $booking->architect?->partnerProfile?->business_name ?? $booking->architect?->name }}</p>
                    <p class="mt-1 text-sm text-gray-600">{{ $booking->scheduled_start->format('d/m/Y H:i') }} · {{ $booking->meeting_type->value === 'online' ? 'Online' : 'Di lokasi' }}</p>
                    <p class="text-xs text-gray-400">{{ $booking->code }}</p>
                </div>
                <div class="shrink-0 text-right">
                    <x-status-badge :status="$booking->status" context="booking" />
                    <p class="mt-2 text-sm font-semibold text-blue-700"><x-rupiah :value="$booking->price" /></p>
                </div>
            </a>
        @empty
            <div class="p-10 text-center text-gray-500">
                Belum ada pesanan. <a href="{{ route('architects.index') }}" class="font-medium text-blue-700 hover:underline">Cari layanan arsitek</a>.
            </div>
        @endforelse
    </div>

    <div class="mt-6">{{ $bookings->links() }}</div>
</div>
