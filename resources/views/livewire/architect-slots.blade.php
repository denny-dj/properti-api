<div class="rounded-2xl border border-gray-200 bg-white p-5 lg:sticky lg:top-24">
    <h2 class="text-lg font-semibold">Jadwal tersedia</h2>
    <label class="mt-3 block text-sm font-medium" for="slot-date">Pilih tanggal</label>
    <input id="slot-date" type="date" wire:model.live="date" min="{{ $min }}" max="{{ $max }}" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2">

    <div class="mt-4" wire:loading.class="opacity-50">
        @if ($error)
            <p class="text-sm text-amber-700">{{ $error }}</p>
        @elseif ($data && count($data['slots']) > 0)
            <p class="text-sm text-gray-500">{{ count($data['slots']) }} slot ({{ $data['duration_minutes'] }} menit, zona waktu {{ $data['timezone'] }})</p>
            <div class="mt-3 grid grid-cols-2 gap-2">
                @foreach ($data['slots'] as $slot)
                    <span class="rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-center text-sm font-medium text-blue-800">
                        {{ substr($slot['start'], 11, 5) }} – {{ substr($slot['end'], 11, 5) }}
                    </span>
                @endforeach
            </div>
        @else
            <p class="text-sm text-gray-500">Tidak ada slot kosong pada tanggal ini. Coba tanggal lain.</p>
        @endif
    </div>
</div>
