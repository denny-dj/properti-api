@php
    $statusLabel = ['draft' => ['Draft', 'bg-gray-100 text-gray-700'], 'published' => ['Tayang', 'bg-green-100 text-green-800'], 'archived' => ['Diarsipkan', 'bg-amber-100 text-amber-800']];
@endphp
<div class="space-y-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold">Portofolio saya</h1>
            <p class="text-sm text-gray-500">Tampilkan karya lengkap dengan estimasi RAB. Admin kami yang mendampingi calon klien.</p>
        </div>
        <a href="{{ route('partner.portfolios.create') }}" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">Tambah portofolio</a>
    </div>

    @if ($notice)
        <p class="rounded-lg bg-green-50 px-3 py-2 text-sm text-green-800">{{ $notice }}</p>
    @endif
    @error('list')
        <p class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700">{{ $message }}</p>
    @enderror

    <div class="divide-y divide-gray-100 overflow-hidden rounded-2xl border border-gray-200 bg-white">
        @forelse ($portfolios as $p)
            @php [$text, $classes] = $statusLabel[$p->status->value] ?? [$p->status->value, 'bg-gray-100 text-gray-700']; @endphp
            <div wire:key="pf-{{ $p->id }}" class="flex flex-wrap items-start gap-4 px-5 py-4">
                <div class="h-20 w-28 shrink-0 overflow-hidden rounded-lg bg-gray-100">
                    @if ($p->coverImage)
                        <img src="{{ $p->coverImage->url() }}" alt="" class="h-full w-full object-cover" onerror="this.remove()">
                    @endif
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $classes }}">{{ $text }}</span>
                        <span class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700">{{ $p->category->label() }}</span>
                        <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700">{{ $p->architectural_style->label() }}</span>
                        @if ($p->is_ready_to_build)<span class="rounded-full bg-green-50 px-2.5 py-1 text-xs font-medium text-green-700">Siap bangun</span>@endif
                    </div>
                    <p class="mt-1 truncate font-medium">{{ $p->title }}</p>
                    <p class="text-sm text-gray-500">RAB <x-rupiah :value="$p->estimated_rab" /> · {{ number_format((float) $p->area_sqm, 0, ',', '.') }} m²@if ($p->is_ready_to_build && $p->blueprint_price) · paket <x-rupiah :value="$p->blueprint_price" />@endif</p>
                </div>
                <div class="flex flex-wrap gap-2 text-sm">
                    @if ($p->status->value === 'published')
                        <a href="{{ route('portfolios.show', $p->slug) }}" target="_blank" class="rounded-lg border border-gray-300 px-3 py-1.5 font-medium hover:bg-gray-50">Lihat</a>
                    @endif
                    <a href="{{ route('partner.portfolios.edit', $p->id) }}" class="rounded-lg border border-gray-300 px-3 py-1.5 font-medium hover:bg-gray-50">Ubah</a>
                    @if ($p->status->value !== 'published')
                        <button type="button" wire:click="setStatus({{ $p->id }}, 'published')" class="rounded-lg border border-green-300 px-3 py-1.5 font-medium text-green-700 hover:bg-green-50">Tayangkan</button>
                    @else
                        <button type="button" wire:click="setStatus({{ $p->id }}, 'archived')" wire:confirm="Arsipkan portofolio ini? Tidak akan tampil di etalase." class="rounded-lg border border-amber-300 px-3 py-1.5 font-medium text-amber-700 hover:bg-amber-50">Arsipkan</button>
                    @endif
                    <button type="button" wire:click="delete({{ $p->id }})" wire:confirm="Hapus portofolio ini?" class="rounded-lg border border-red-300 px-3 py-1.5 font-medium text-red-700 hover:bg-red-50">Hapus</button>
                </div>
            </div>
        @empty
            <div class="p-10 text-center text-gray-500">Belum ada portofolio. Tambahkan karya pertama Anda agar calon klien dapat menemukannya.</div>
        @endforelse
    </div>

    <div>{{ $portfolios->links() }}</div>
</div>
