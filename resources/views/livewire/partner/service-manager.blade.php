@php
    $types = ['consultation' => 'Konsultasi', 'design' => 'Desain', 'supervision' => 'Pengawasan'];
    $input = 'mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none';
@endphp
<div class="space-y-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold">Layanan saya</h1>
        <button type="button" wire:click="create" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">Tambah layanan</button>
    </div>

    @if ($notice)
        <p class="rounded-lg bg-green-50 px-3 py-2 text-sm text-green-800">{{ $notice }}</p>
    @endif
    @error('list')
        <p class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700">{{ $message }}</p>
    @enderror

    @if ($showForm)
        <form wire:submit="save" class="space-y-4 rounded-2xl border border-blue-200 bg-white p-5">
            <h2 class="font-semibold">{{ $editingId ? 'Ubah layanan' : 'Layanan baru' }}</h2>
            @error('form')<p class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700">{{ $message }}</p>@enderror

            <div>
                <label class="block text-sm font-medium">Nama layanan</label>
                <input type="text" wire:model="title" class="{{ $input }}" placeholder="mis. Konsultasi Desain Rumah (1 jam)">
                @error('title')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label class="block text-sm font-medium">Jenis</label>
                    <select wire:model="service_type" class="{{ $input }}">
                        @foreach ($types as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach
                    </select>
                    @error('service_type')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium">Harga (Rp)</label>
                    <input type="number" min="1" step="any" wire:model="price" class="{{ $input }}">
                    @error('price')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium">Durasi sesi</label>
                    <select wire:model="duration_minutes" class="{{ $input }}">
                        @foreach ([30, 45, 60, 90, 120, 180, 240] as $m)<option value="{{ $m }}">{{ $m }} menit</option>@endforeach
                    </select>
                    @error('duration_minutes')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium">Deskripsi</label>
                <textarea rows="4" wire:model="description" class="{{ $input }}" placeholder="Apa yang didapat klien dari layanan ini?"></textarea>
                @error('description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" wire:model="is_active" class="rounded border-gray-300"> Aktif (tampil di katalog)</label>

            <div class="flex gap-3">
                <button type="submit" class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-medium text-white hover:bg-blue-700">Simpan</button>
                <button type="button" wire:click="cancelForm" class="rounded-lg border border-gray-300 px-5 py-2 text-sm font-medium hover:bg-gray-50">Batal</button>
            </div>
        </form>
    @endif

    <div class="divide-y divide-gray-100 overflow-hidden rounded-2xl border border-gray-200 bg-white">
        @forelse ($services as $s)
            <div wire:key="svc-{{ $s->id }}" class="flex flex-wrap items-start justify-between gap-4 px-5 py-4">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700">{{ $types[$s->service_type->value] ?? $s->service_type->value }}</span>
                        <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $s->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">{{ $s->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                    </div>
                    <p class="mt-1 font-medium">{{ $s->title }}</p>
                    <p class="text-sm text-gray-500"><x-rupiah :value="$s->price" /> · {{ $s->duration_minutes }} menit</p>
                </div>
                <div class="flex flex-wrap gap-2 text-sm">
                    <button type="button" wire:click="edit({{ $s->id }})" class="rounded-lg border border-gray-300 px-3 py-1.5 font-medium hover:bg-gray-50">Ubah</button>
                    <button type="button" wire:click="toggle({{ $s->id }})" class="rounded-lg border border-gray-300 px-3 py-1.5 font-medium hover:bg-gray-50">{{ $s->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                    <button type="button" wire:click="delete({{ $s->id }})" wire:confirm="Hapus layanan ini?" class="rounded-lg border border-red-300 px-3 py-1.5 font-medium text-red-700 hover:bg-red-50">Hapus</button>
                </div>
            </div>
        @empty
            <div class="p-10 text-center text-gray-500">Belum ada layanan. Buat layanan pertama Anda agar tampil di katalog arsitek.</div>
        @endforelse
    </div>

    <div>{{ $services->links() }}</div>
</div>
