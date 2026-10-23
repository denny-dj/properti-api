<div class="space-y-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold">Properti saya</h1>
        <a href="{{ route('partner.properties.create') }}" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">Tambah properti</a>
    </div>

    <div class="flex flex-wrap gap-3">
        <input type="text" wire:model.live.debounce.500ms="q" placeholder="Cari judul, alamat, kota" class="w-64 rounded-lg border border-gray-300 px-3 py-2 text-sm">
        <select wire:model.live="status" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
            <option value="">Semua status</option>
            <option value="draft">Draft</option>
            <option value="pending_review">Menunggu verifikasi</option>
            <option value="published">Tayang</option>
            <option value="reserved">Dipesan</option>
            <option value="rejected">Ditolak</option>
            <option value="sold">Terjual</option>
        </select>
    </div>

    @if ($notice)
        <p class="rounded-lg bg-green-50 px-3 py-2 text-sm text-green-800">{{ $notice }}</p>
    @endif
    @error('list')
        <p class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700">{{ $message }}</p>
    @enderror

    <div class="divide-y divide-gray-100 overflow-hidden rounded-2xl border border-gray-200 bg-white" wire:loading.class="opacity-60">
        @forelse ($properties as $p)
            @php $locked = in_array($p->status->value, ['reserved', 'sold'], true); @endphp
            <div wire:key="prop-{{ $p->id }}" class="flex flex-wrap items-start justify-between gap-4 px-5 py-4">
                <div class="flex min-w-0 flex-1 gap-4">
                    <div class="h-16 w-24 shrink-0 overflow-hidden rounded-lg bg-gray-100">
                        @if ($p->coverImage)
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($p->coverImage->path) }}" alt="" class="h-full w-full object-cover" onerror="this.remove()">
                        @endif
                    </div>
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <x-status-badge :status="$p->status" context="property" />
                            <p class="truncate font-medium">{{ $p->title }}</p>
                        </div>
                        <p class="mt-1 text-sm text-gray-500">{{ $p->city }} · <x-rupiah :value="$p->price" /> · dilihat {{ $p->views_count }}×</p>
                        @if ($p->status->value === 'rejected' && $p->rejection_reason)
                            <p class="mt-1 text-sm text-red-700">Ditolak: {{ $p->rejection_reason }}</p>
                        @endif
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2 text-sm">
                    @if (! $locked)
                        <a href="{{ route('partner.properties.edit', $p->id) }}" class="rounded-lg border border-gray-300 px-3 py-1.5 font-medium hover:bg-gray-50">Ubah</a>
                    @endif
                    @if (in_array($p->status->value, ['draft', 'rejected'], true))
                        <button type="button" wire:click="submit({{ $p->id }})" class="rounded-lg bg-blue-600 px-3 py-1.5 font-medium text-white hover:bg-blue-700">Ajukan</button>
                    @endif
                    @if ($p->status->value === 'published')
                        <a href="{{ route('properties.show', $p->slug) }}" class="rounded-lg border border-gray-300 px-3 py-1.5 font-medium hover:bg-gray-50">Lihat</a>
                    @endif
                    @if ($p->status->value === 'reserved')
                        <button type="button" wire:click="markSold({{ $p->id }})" wire:confirm="Tandai properti ini terjual?" class="rounded-lg bg-green-600 px-3 py-1.5 font-medium text-white hover:bg-green-700">Tandai terjual</button>
                        <button type="button" wire:click="release({{ $p->id }})" wire:confirm="Lepas reservasi? Properti akan tayang kembali dan booking fee perlu dikembalikan oleh administrator." class="rounded-lg border border-gray-300 px-3 py-1.5 font-medium hover:bg-gray-50">Lepas reservasi</button>
                    @endif
                    @if (! $locked)
                        <button type="button" wire:click="delete({{ $p->id }})" wire:confirm="Hapus properti ini?" class="rounded-lg border border-red-300 px-3 py-1.5 font-medium text-red-700 hover:bg-red-50">Hapus</button>
                    @endif
                </div>
            </div>
        @empty
            <div class="p-10 text-center text-gray-500">Belum ada properti. <a href="{{ route('partner.properties.create') }}" class="font-medium text-blue-700 hover:underline">Tambah yang pertama</a>.</div>
        @endforelse
    </div>

    <div>{{ $properties->links() }}</div>
</div>
