<div class="mx-auto max-w-3xl px-4 py-8">
    <a href="{{ route('orders.index') }}" class="text-sm text-blue-700 hover:underline">&larr; Semua pesanan</a>

    <div class="mt-3 flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold">{{ $booking->service?->title }}</h1>
            <p class="text-sm text-gray-400">{{ $booking->code }}</p>
        </div>
        <x-status-badge :status="$booking->status" context="booking" />
    </div>

    @error('action')
        <p class="mt-4 rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700">{{ $message }}</p>
    @enderror

    <div class="mt-5 space-y-4 rounded-2xl border border-gray-200 bg-white p-5 text-sm">
        <div class="flex justify-between"><span class="text-gray-500">Arsitek</span><span class="font-medium">{{ $booking->architect?->partnerProfile?->business_name ?? $booking->architect?->name }}</span></div>
        <div class="flex justify-between"><span class="text-gray-500">Jadwal</span><span class="font-medium">{{ $booking->scheduled_start->format('d/m/Y H:i') }} – {{ $booking->scheduled_end->format('H:i') }}</span></div>
        <div class="flex justify-between"><span class="text-gray-500">Pertemuan</span><span class="font-medium">{{ $booking->meeting_type->value === 'online' ? 'Online' : 'Di lokasi' }}</span></div>
        @if ($booking->location)
            <div class="flex justify-between gap-6"><span class="text-gray-500">Lokasi</span><span class="text-right font-medium">{{ $booking->location }}</span></div>
        @endif
        @if ($booking->notes)
            <div><p class="text-gray-500">Catatan</p><p class="mt-1 whitespace-pre-line">{{ $booking->notes }}</p></div>
        @endif
        <div class="flex justify-between border-t border-gray-100 pt-3"><span class="text-gray-500">Biaya</span><span class="text-lg font-semibold text-blue-700"><x-rupiah :value="$booking->price" /></span></div>
        @if ($booking->cancel_reason)
            <div class="rounded-lg bg-gray-50 p-3"><p class="text-gray-500">Alasan {{ $booking->status->value === 'rejected' ? 'penolakan' : 'pembatalan' }}</p><p class="mt-1">{{ $booking->cancel_reason }}</p></div>
        @endif
    </div>

    {{-- Pembayaran --}}
    <div class="mt-5 rounded-2xl border border-gray-200 bg-white p-5">
        <h2 class="font-semibold">Pembayaran</h2>

        @if ($paid)
            <p class="mt-2 text-sm text-green-700">Pesanan ini sudah dibayar. <a href="{{ route('transactions.show', $transaction->code) }}" class="font-medium underline">Lihat transaksi</a></p>
        @elseif ($canPay)
            <p class="mt-2 text-sm text-gray-600">
                @if ($transaction && $transaction->status->value === 'pending')
                    Anda memiliki tagihan yang belum dibayar.
                @else
                    Arsitek sudah mengonfirmasi jadwal. Silakan lakukan pembayaran.
                @endif
            </p>
            <button type="button" wire:click="pay" wire:loading.attr="disabled" class="mt-3 rounded-lg bg-blue-600 px-5 py-2.5 font-medium text-white hover:bg-blue-700">
                <span wire:loading.remove wire:target="pay">{{ $transaction && $transaction->status->value === 'pending' ? 'Lanjutkan pembayaran' : 'Bayar sekarang' }}</span>
                <span wire:loading wire:target="pay">Menyiapkan tagihan...</span>
            </button>
        @elseif ($booking->status->value === 'pending')
            <p class="mt-2 text-sm text-gray-500">Pembayaran dibuka setelah arsitek mengonfirmasi jadwal.</p>
        @else
            <p class="mt-2 text-sm text-gray-500">Tidak ada tagihan untuk pesanan ini.</p>
        @endif
    </div>

    {{-- Aksi --}}
    <div class="mt-5 flex flex-wrap gap-3">
        <button type="button" wire:click="chat" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium hover:bg-gray-50">Chat arsitek</button>
        @if ($canCancel)
            <button type="button" wire:click="$toggle('showCancel')" class="rounded-lg border border-red-300 px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-50">Batalkan pesanan</button>
        @endif
    </div>

    @if ($canCancel && $showCancel)
        <form wire:submit="cancel" class="mt-4 space-y-3 rounded-2xl border border-red-200 bg-red-50 p-5">
            @if ($paid)
                <p class="text-sm text-red-800">Pesanan ini sudah dibayar. Pembatalan tidak mengembalikan dana secara otomatis; pengembalian diproses manual oleh admin.</p>
            @endif
            <label class="block text-sm font-medium">Alasan pembatalan (opsional)</label>
            <textarea rows="3" wire:model="cancelReason" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
            <button type="submit" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">Ya, batalkan pesanan</button>
        </form>
    @endif
</div>
