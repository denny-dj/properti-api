<div class="space-y-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold">Pesanan masuk</h1>
        <select wire:model.live="status" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
            <option value="">Semua status</option>
            <option value="pending">Menunggu konfirmasi</option>
            <option value="confirmed">Dikonfirmasi</option>
            <option value="completed">Selesai</option>
            <option value="cancelled">Dibatalkan</option>
            <option value="rejected">Ditolak</option>
        </select>
    </div>

    @if ($notice)
        <p class="rounded-lg bg-green-50 px-3 py-2 text-sm text-green-800">{{ $notice }}</p>
    @endif
    @error('list')
        <p class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700">{{ $message }}</p>
    @enderror

    <div class="divide-y divide-gray-100 overflow-hidden rounded-2xl border border-gray-200 bg-white" wire:loading.class="opacity-60">
        @forelse ($bookings as $b)
            @php
                $isPaid = in_array($b->id, $paidIds, true);
                $started = $b->scheduled_start->isPast();
                $open = in_array($b->status->value, ['pending', 'confirmed'], true);
            @endphp
            <div wire:key="bk-{{ $b->id }}" class="space-y-3 px-5 py-4">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="font-medium">{{ $b->service?->title }}</p>
                        <p class="text-sm text-gray-600">{{ $b->scheduled_start->format('d/m/Y H:i') }} – {{ $b->scheduled_end->format('H:i') }} · {{ $b->meeting_type->value === 'online' ? 'Online' : 'Di lokasi' }}</p>
                        <p class="text-xs text-gray-400">{{ $b->code }}</p>
                    </div>
                    <div class="text-right">
                        <x-status-badge :status="$b->status" context="booking" />
                        <p class="mt-1 text-sm font-semibold text-blue-700"><x-rupiah :value="$b->price" /></p>
                        <p class="text-xs {{ $isPaid ? 'text-green-700' : 'text-gray-400' }}">{{ $isPaid ? 'Sudah dibayar' : 'Belum dibayar' }}</p>
                    </div>
                </div>

                <div class="rounded-xl bg-gray-50 p-3 text-sm">
                    <p><span class="text-gray-500">Klien:</span> <span class="font-medium">{{ $b->client?->name }}</span> @if ($b->client?->phone) · {{ $b->client->phone }}@endif</p>
                    @if ($b->location)<p><span class="text-gray-500">Lokasi:</span> {{ $b->location }}</p>@endif
                    @if ($b->notes)<p class="whitespace-pre-line"><span class="text-gray-500">Catatan:</span> {{ $b->notes }}</p>@endif
                    @if ($b->cancel_reason)<p><span class="text-gray-500">Alasan:</span> {{ $b->cancel_reason }}</p>@endif
                </div>

                @if ($open)
                    <div class="flex flex-wrap gap-2 text-sm">
                        @if ($b->status->value === 'pending')
                            <button type="button" wire:click="confirm({{ $b->id }})" class="rounded-lg bg-blue-600 px-3 py-1.5 font-medium text-white hover:bg-blue-700">Konfirmasi</button>
                            <button type="button" wire:click="startAction({{ $b->id }}, 'reject')" class="rounded-lg border border-red-300 px-3 py-1.5 font-medium text-red-700 hover:bg-red-50">Tolak</button>
                        @endif
                        @if ($b->status->value === 'confirmed')
                            @if ($started)
                                <button type="button" wire:click="complete({{ $b->id }})" wire:confirm="Tandai sesi ini selesai?" class="rounded-lg bg-green-600 px-3 py-1.5 font-medium text-white hover:bg-green-700">Selesaikan</button>
                            @else
                                <span class="rounded-lg bg-gray-100 px-3 py-1.5 text-gray-500">Bisa diselesaikan setelah sesi dimulai</span>
                            @endif
                            <button type="button" wire:click="startAction({{ $b->id }}, 'cancel')" class="rounded-lg border border-gray-300 px-3 py-1.5 font-medium hover:bg-gray-50">Batalkan</button>
                        @endif
                    </div>
                @endif

                @if ($actionId === $b->id)
                    <form wire:submit="submitAction" class="space-y-2 rounded-xl border border-gray-200 p-3">
                        <label class="block text-sm font-medium">{{ $actionType === 'reject' ? 'Alasan penolakan' : 'Alasan pembatalan (opsional)' }}</label>
                        <textarea rows="2" wire:model="reason" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
                        @error('reason')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                        @if ($actionType === 'cancel' && $isPaid)
                            <p class="text-xs text-red-700">Pesanan ini sudah dibayar. Pengembalian dana diproses manual oleh administrator.</p>
                        @endif
                        <div class="flex gap-2">
                            <button type="submit" class="rounded-lg bg-red-600 px-4 py-1.5 text-sm font-medium text-white hover:bg-red-700">{{ $actionType === 'reject' ? 'Tolak pesanan' : 'Batalkan pesanan' }}</button>
                            <button type="button" wire:click="dismissAction" class="rounded-lg border border-gray-300 px-4 py-1.5 text-sm font-medium hover:bg-gray-50">Tutup</button>
                        </div>
                    </form>
                @endif
            </div>
        @empty
            <div class="p-10 text-center text-gray-500">Belum ada pesanan masuk.</div>
        @endforelse
    </div>

    <div>{{ $bookings->links() }}</div>
</div>
