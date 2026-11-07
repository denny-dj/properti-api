<div class="bg-slate-50/50 min-h-screen pb-20">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        
        {{-- Header Editorial --}}
        <div class="mb-8 flex flex-col justify-between gap-4 border-b border-slate-200/80 pb-6 sm:flex-row sm:items-end">
            <div>
                <nav class="mb-2 flex items-center gap-2 text-xs font-medium text-slate-400">
                    <a href="{{ route('home') }}" class="hover:text-blue-600 transition-colors">Beranda</a>
                    <span>/</span>
                    <span class="text-slate-700">Katalog Properti</span>
                </nav>
                <h1 class="text-2xl sm:text-4xl font-extrabold tracking-tight text-slate-900">Eksplorasi Properti Pilihan</h1>
                <p class="mt-1 text-xs sm:text-sm text-slate-500">Unit hunian terkurasi dengan kepastian legalitas dan pendampingan transaksi concierge.</p>
            </div>

            {{-- Ringkasan Hasil & Sortir --}}
            <div class="flex flex-wrap items-center justify-between gap-3 sm:justify-end">
                <div class="flex items-center gap-2 text-xs text-slate-500">
                    <span class="inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                    <span><strong class="font-bold text-slate-900">{{ number_format($properties->total(), 0, ',', '.') }}</strong> unit ditemukan</span>
                </div>

                <div class="flex items-center gap-2">
                    {{-- Loading Indicator Livewire --}}
                    <div wire:loading class="text-xs font-medium text-slate-400 flex items-center gap-1.5 bg-white px-2.5 py-1.5 rounded-lg border border-slate-200 shadow-sm">
                        <svg class="h-3.5 w-3.5 animate-spin text-slate-600" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <span>Memperbarui...</span>
                    </div>

                    {{-- Dropdown Pengurutan --}}
                    <div class="relative">
                        <select wire:model.live="sort" class="appearance-none rounded-xl border border-slate-200 bg-white py-2 pl-3.5 pr-8 text-xs font-semibold text-slate-700 shadow-sm focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900 transition-all cursor-pointer">
                            <option value="newest">Listing Terbaru</option>
                            <option value="price_asc">Harga: Terendah ke Tertinggi</option>
                            <option value="price_desc">Harga: Tertinggi ke Terendah</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-slate-400">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid gap-8 lg:grid-cols-4">
            
            {{-- Sidebar Filter (1 Kolom Sticky) --}}
            <aside class="lg:col-span-1">
                <div class="rounded-3xl border border-slate-200/80 bg-white p-5 sm:p-6 shadow-sm lg:sticky lg:top-24 space-y-5">
                    
                    <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                        <h2 class="text-sm font-bold uppercase tracking-wider text-slate-900 flex items-center gap-2">
                            <svg class="h-4 w-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                            Filter Pencarian
                        </h2>
                        <button type="button" wire:click="resetFilters" class="text-xs font-semibold text-slate-400 hover:text-rose-600 transition-colors">
                            Reset
                        </button>
                    </div>

                    {{-- Kata Kunci --}}
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500">Kata Kunci</label>
                        <div class="relative">
                            <input type="text" wire:model.live.debounce.500ms="q" placeholder="Cluster, jalan, perumahan..." 
                                   class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 focus:bg-white focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900 transition-all">
                        </div>
                    </div>

                    {{-- Tipe Properti --}}
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500">Tipe Unit</label>
                        <select wire:model.live="property_type" 
                                class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2.5 text-xs text-slate-800 focus:bg-white focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900 transition-all cursor-pointer">
                            <option value="">Semua Tipe Properti</option>
                            <option value="house">Rumah Tapak</option>
                            <option value="shop">Ruko / Komersial</option>
                        </select>
                    </div>

                    {{-- Kota / Lokasi --}}
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500">Kota / Wilayah</label>
                        <input type="text" wire:model.live.debounce.500ms="city" placeholder="mis. Depok, Bogor, Tangerang" 
                               class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 focus:bg-white focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900 transition-all">
                    </div>

                    {{-- Rentang Harga --}}
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500">Rentang Harga (Rp)</label>
                        <div class="grid grid-cols-2 gap-2">
                            <input type="number" min="0" step="50000000" wire:model.live.debounce.600ms="min_price" placeholder="Min" 
                                   class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs text-slate-800 placeholder-slate-400 focus:bg-white focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900 transition-all">
                            <input type="number" min="0" step="50000000" wire:model.live.debounce.600ms="max_price" placeholder="Maks" 
                                   class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs text-slate-800 placeholder-slate-400 focus:bg-white focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900 transition-all">
                        </div>
                    </div>

                    {{-- Kamar Tidur Minimal --}}
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500">Kamar Tidur Minimum</label>
                        <select wire:model.live="bedrooms" 
                                class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2.5 text-xs text-slate-800 focus:bg-white focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900 transition-all cursor-pointer">
                            <option value="">Berapa Saja</option>
                            @foreach ([1, 2, 3, 4, 5] as $n)
                                <option value="{{ $n }}">{{ $n }}+ Kamar Tidur</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Fasilitas Tersedia --}}
                    @if ($allFacilities->isNotEmpty())
                        <div class="space-y-2 pt-2 border-t border-slate-100">
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Fasilitas Properti</p>
                            <div class="max-h-48 space-y-2 overflow-y-auto pr-1">
                                @foreach ($allFacilities as $facility)
                                    <label class="flex items-center gap-2.5 text-xs text-slate-700 cursor-pointer select-none hover:text-slate-900">
                                        <input type="checkbox" wire:model.live="facilities" value="{{ $facility->slug }}" 
                                               class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900/20">
                                        <span>{{ $facility->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Tombol Reset --}}
                    <button type="button" wire:click="resetFilters" 
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 text-xs font-semibold text-slate-700 hover:bg-slate-100 hover:text-slate-900 transition-all">
                        Hapus Semua Filter
                    </button>
                </div>
            </aside>

            {{-- Grid Hasil Pencarian (3 Kolom) --}}
            <section class="lg:col-span-3">
                <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3 transition-opacity duration-200" wire:loading.class="opacity-60">
                    @forelse ($properties as $property)
                        <x-property-card :property="$property" wire:key="property-{{ $property->id }}" />
                    @empty
                        <div class="col-span-full rounded-3xl border border-dashed border-slate-300 bg-white p-12 text-center shadow-sm">
                            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21l-7-7m0 0l-7-7m7 7H3"/></svg>
                            </div>
                            <h3 class="mt-4 text-sm font-bold text-slate-900">Tidak ada properti yang cocok</h3>
                            <p class="mt-1 text-xs text-slate-500 max-w-sm mx-auto">Kriteria pencarian Anda belum menemukan hasil. Coba turunkan batasan harga atau hapus beberapa filter.</p>
                            <button type="button" wire:click="resetFilters" class="mt-4 inline-flex items-center gap-1.5 px-4 py-2 bg-slate-900 text-white rounded-xl text-xs font-semibold hover:bg-slate-800 transition-colors">
                                Reset Filter Pencarian
                            </button>
                        </div>
                    @endforelse
                </div>

                {{-- Pagination --}}
                <div class="mt-10 border-t border-slate-200/80 pt-6">
                    {{ $properties->links() }}
                </div>
            </section>

        </div>
    </div>
</div>