<div class="mx-auto max-w-7xl px-4 py-8">
    <h1 class="text-2xl font-bold">Jasa arsitek</h1>
    <p class="mt-1 text-gray-500">Konsultasi, desain, dan pengawasan pembangunan dari arsitek terverifikasi.</p>

    <div class="mt-6 grid gap-3 rounded-2xl border border-gray-200 bg-white p-4 sm:grid-cols-2 lg:grid-cols-5">
        <input type="text" wire:model.live.debounce.500ms="q" placeholder="Cari layanan" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
        <select wire:model.live="service_type" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
            <option value="">Semua jenis</option>
            <option value="consultation">Konsultasi</option>
            <option value="design">Desain</option>
            <option value="supervision">Pengawasan</option>
        </select>
        <input type="text" wire:model.live.debounce.500ms="city" placeholder="Kota arsitek" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
        <input type="number" min="0" step="100000" wire:model.live.debounce.600ms="max_price" placeholder="Harga maks (Rp)" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
        <select wire:model.live="sort" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
            <option value="newest">Terbaru</option>
            <option value="price_asc">Harga terendah</option>
            <option value="price_desc">Harga tertinggi</option>
        </select>
    </div>
    <button type="button" wire:click="resetFilters" class="mt-2 text-sm font-medium text-blue-700 hover:underline">Reset filter</button>

    <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3" wire:loading.class="opacity-60">
        @forelse ($services as $service)
            <x-architect-card :service="$service" wire:key="service-{{ $service->id }}" />
        @empty
            <div class="col-span-full rounded-2xl border border-dashed border-gray-300 p-12 text-center text-gray-500">
                Belum ada layanan yang cocok.
            </div>
        @endforelse
    </div>

    <div class="mt-8">{{ $services->links() }}</div>
</div>
