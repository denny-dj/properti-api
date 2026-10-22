<div class="rounded-2xl border border-gray-200 bg-white p-5 lg:sticky lg:top-24">
    <h2 class="text-lg font-semibold">Jadwal tersedia</h2>

    <label class="mt-3 block text-sm font-medium" for="slot-date">Pilih tanggal</label>
    <input id="slot-date" type="date" wire:model.live="date" min="{{ $min }}" max="{{ $max }}" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2">

    <div class="mt-4" wire:loading.class="opacity-50" wire:target="date">
        @if ($error)
            <p class="text-sm text-amber-700">{{ $error }}</p>
        @elseif ($data && count($data['slots']) > 0)
            <p class="text-sm text-gray-500">{{ count($data['slots']) }} slot ({{ $data['duration_minutes'] }} menit, zona waktu {{ $data['timezone'] }}). Pilih satu untuk memesan.</p>
            <div class="mt-3 grid grid-cols-2 gap-2">
                @foreach ($data['slots'] as $slot)
                    <button type="button" wire:key="slot-{{ $slot['start'] }}" wire:click="selectSlot('{{ $slot['start'] }}')"
                            class="rounded-lg border px-3 py-2 text-center text-sm font-medium {{ $selected === $slot['start'] ? 'border-blue-600 bg-blue-600 text-white' : 'border-blue-200 bg-blue-50 text-blue-800 hover:bg-blue-100' }}">
                        {{ substr($slot['start'], 11, 5) }} – {{ substr($slot['end'], 11, 5) }}
                    </button>
                @endforeach
            </div>
        @else
            <p class="text-sm text-gray-500">Tidak ada slot kosong pada tanggal ini. Coba tanggal lain.</p>
        @endif
    </div>

    @error('booking')
        <p class="mt-3 rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700">{{ $message }}</p>
    @enderror

    @if ($selected)
        <div class="mt-5 space-y-3 border-t border-gray-100 pt-4">
            <p class="text-sm">Jadwal dipilih: <strong>{{ substr($selected, 0, 10) }}, {{ substr($selected, 11, 5) }}</strong></p>

            @if ($isGuest)
                <button type="button" wire:click="goLogin" class="w-full rounded-lg bg-blue-600 py-2.5 font-medium text-white hover:bg-blue-700">Masuk untuk memesan</button>
            @elseif ($isBuyer)
                <form wire:submit="book" class="space-y-3">
                    <div>
                        <p class="text-sm font-medium">Jenis pertemuan</p>
                        <div class="mt-1 grid grid-cols-2 gap-2">
                            <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-gray-300 p-2 text-sm">
                                <input type="radio" wire:model.live="meeting_type" value="online"> Online
                            </label>
                            <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-gray-300 p-2 text-sm">
                                <input type="radio" wire:model.live="meeting_type" value="onsite"> Di lokasi
                            </label>
                        </div>
                    </div>
                    @if ($meeting_type === 'onsite')
                        <div>
                            <label class="block text-sm font-medium">Alamat lokasi</label>
                            <input type="text" wire:model="location" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            @error('location')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    @endif
                    <div>
                        <label class="block text-sm font-medium">Catatan untuk arsitek (opsional)</label>
                        <textarea rows="3" wire:model="notes" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" placeholder="Ceritakan kebutuhan Anda"></textarea>
                        @error('notes')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <button type="submit" wire:loading.attr="disabled" class="w-full rounded-lg bg-blue-600 py-2.5 font-medium text-white hover:bg-blue-700">
                        <span wire:loading.remove wire:target="book">Pesan jadwal ini</span>
                        <span wire:loading wire:target="book">Memproses...</span>
                    </button>
                    <p class="text-xs text-gray-500">Pembayaran dilakukan setelah arsitek mengonfirmasi jadwal.</p>
                </form>
            @else
                <p class="text-sm text-gray-500">Pemesanan tersedia untuk akun pembeli.</p>
            @endif
        </div>
    @endif

    @if ($isBuyer)
        <button type="button" wire:click="chat" class="mt-4 w-full rounded-lg border border-gray-300 py-2 text-sm font-medium hover:bg-gray-50">Tanya arsitek lewat chat</button>
    @endif
</div>
