<div class="mx-auto max-w-7xl px-4 py-8">
    <h1 class="text-2xl font-bold">Cari properti</h1>

    <div class="mt-6 grid gap-6 lg:grid-cols-4">
        {{-- Filter --}}
        <aside class="lg:col-span-1">
            <div class="space-y-4 rounded-2xl border border-gray-200 bg-white p-5">
                <div>
                    <label class="block text-sm font-medium">Kata kunci</label>
                    <input type="text" wire:model.live.debounce.500ms="q" placeholder="Nama, alamat, kota" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium">Tipe</label>
                    <select wire:model.live="property_type" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <option value="">Semua</option>
                        <option value="house">Rumah</option>
                        <option value="shop">Ruko</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium">Kota</label>
                    <input type="text" wire:model.live.debounce.500ms="city" placeholder="mis. Depok" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-sm font-medium">Harga min (Rp)</label>
                        <input type="number" min="0" step="50000000" wire:model.live.debounce.600ms="min_price" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Harga maks (Rp)</label>
                        <input type="number" min="0" step="50000000" wire:model.live.debounce.600ms="max_price" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium">Kamar tidur (min)</label>
                    <select wire:model.live="bedrooms" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <option value="">Semua</option>
                        @foreach ([1, 2, 3, 4, 5] as $n)
                            <option value="{{ $n }}">{{ $n }}+</option>
                        @endforeach
                    </select>
                </div>
                @if ($allFacilities->isNotEmpty())
                    <div>
                        <p class="text-sm font-medium">Fasilitas</p>
                        <div class="mt-2 max-h-48 space-y-1.5 overflow-y-auto">
                            @foreach ($allFacilities as $facility)
                                <label class="flex items-center gap-2 text-sm text-gray-700">
                                    <input type="checkbox" wire:model.live="facilities" value="{{ $facility->slug }}" class="rounded border-gray-300">
                                    {{ $facility->name }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif
                <button type="button" wire:click="resetFilters" class="w-full rounded-lg border border-gray-300 py-2 text-sm font-medium hover:bg-gray-50">Reset filter</button>
            </div>
        </aside>

        {{-- Hasil --}}
        <section class="lg:col-span-3">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <p class="text-sm text-gray-600"><span class="font-semibold">{{ number_format($properties->total(), 0, ',', '.') }}</span> properti ditemukan</p>
                <div class="flex items-center gap-3">
                    <span wire:loading class="text-sm text-gray-400">Memuat...</span>
                    <select wire:model.live="sort" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <option value="newest">Terbaru</option>
                        <option value="price_asc">Harga terendah</option>
                        <option value="price_desc">Harga tertinggi</option>
                    </select>
                </div>
            </div>

            <div class="mt-4 grid gap-5 sm:grid-cols-2 xl:grid-cols-3" wire:loading.class="opacity-60">
                @forelse ($properties as $property)
                    <x-property-card :property="$property" wire:key="property-{{ $property->id }}" />
                @empty
                    <div class="col-span-full rounded-2xl border border-dashed border-gray-300 p-12 text-center text-gray-500">
                        Tidak ada properti yang cocok. Coba ubah atau reset filter.
                    </div>
                @endforelse
            </div>

            <div class="mt-8">{{ $properties->links() }}</div>
        </section>
    </div>
</div>
