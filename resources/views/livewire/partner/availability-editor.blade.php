@php
    $days = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
@endphp
<div class="space-y-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold">Jadwal kerja</h1>
            <p class="text-sm text-gray-500">Jam praktik mingguan Anda. Klien hanya bisa memesan slot di dalam rentang ini.</p>
        </div>
        <button type="button" wire:click="weekdays" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium hover:bg-gray-50">Isi Senin–Jumat 09:00–16:00</button>
    </div>

    @if ($notice)
        <p class="rounded-lg bg-green-50 px-3 py-2 text-sm text-green-800">{{ $notice }}</p>
    @endif
    @error('rows')
        <p class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700">{{ $message }}</p>
    @enderror

    <form wire:submit="save" class="space-y-3">
        @foreach ($days as $dayNumber => $dayName)
            <div class="rounded-2xl border border-gray-200 bg-white p-4">
                <div class="flex items-center justify-between">
                    <p class="font-medium">{{ $dayName }}</p>
                    <button type="button" wire:click="addRange({{ $dayNumber }})" class="text-sm font-medium text-blue-700 hover:underline">+ Tambah jam</button>
                </div>

                @php $hasRows = false; @endphp
                @foreach ($rows as $i => $row)
                    @if ((int) $row['day'] === $dayNumber)
                        @php $hasRows = true; @endphp
                        <div wire:key="row-{{ $i }}" class="mt-3 flex flex-wrap items-center gap-2">
                            <input type="time" wire:model="rows.{{ $i }}.start" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm">
                            <span class="text-gray-400">sampai</span>
                            <input type="time" wire:model="rows.{{ $i }}.end" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm">
                            <button type="button" wire:click="removeRange({{ $i }})" class="text-sm font-medium text-red-600 hover:underline">Hapus</button>
                        </div>
                    @endif
                @endforeach

                @if (! $hasRows)
                    <p class="mt-2 text-sm text-gray-400">Libur</p>
                @endif
            </div>
        @endforeach

        <button type="submit" class="rounded-lg bg-blue-600 px-6 py-2.5 font-medium text-white hover:bg-blue-700">Simpan jadwal</button>
    </form>
</div>
