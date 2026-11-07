<div class="bg-slate-50/50 min-h-screen pb-20">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        
        {{-- Header Editorial --}}
        <div class="mb-8 flex flex-col justify-between gap-4 border-b border-slate-200/80 pb-6 sm:flex-row sm:items-end">
            <div>
                <nav class="mb-2 flex items-center gap-2 text-xs font-medium text-slate-400">
                    <a href="{{ route('home') }}" class="hover:text-blue-600 transition-colors">Beranda</a>
                    <span>/</span>
                    <span class="text-slate-700">Portofolio Arsitek</span>
                </nav>
                <h1 class="text-2xl sm:text-4xl font-extrabold tracking-tight text-slate-900">Katalog Desain & Karya Arsitek</h1>
                <p class="mt-1 text-xs sm:text-sm text-slate-500">Pilih inspirasi denah siap bangun dengan estimasi RAB transparan, atau konsultasikan penyesuaian custom.</p>
            </div>

            {{-- Ringkasan Hasil & Indikator Loading --}}
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2 text-xs text-slate-500">
                    <span class="inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                    <span><strong class="font-bold text-slate-900">{{ number_format($portfolios->total(), 0, ',', '.') }}</strong> desain tersedia</span>
                </div>

                {{-- Loading Indicator Livewire --}}
                <div wire:loading class="text-xs font-medium text-slate-400 flex items-center gap-1.5 bg-white px-2.5 py-1.5 rounded-lg border border-slate-200 shadow-sm">
                    <svg class="h-3.5 w-3.5 animate-spin text-slate-600" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <span>Memperbarui...</span>
                </div>
            </div>
        </div>

        {{-- Filter Bar Modular (Horizontal Control Center) --}}
        <div class="mb-8 rounded-3xl border border-slate-200/80 bg-white p-4 sm:p-5 shadow-sm">
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-6">
                
                {{-- Input Pencarian --}}
                <div class="relative">
                    <input type="text" wire:model.live.debounce.500ms="q" placeholder="Cari nama desain / arsitek..." 
                           class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 focus:bg-white focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900 transition-all">
                </div>

                {{-- Kategori --}}
                <div class="relative">
                    <select wire:model.live="category" 
                            class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2.5 text-xs text-slate-800 focus:bg-white focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900 transition-all cursor-pointer">
                        <option value="">Semua Kategori</option>
                        @foreach ($categories as $option)
                            <option value="{{ $option->value }}">{{ $option->label() }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Gaya Arsitektur --}}
                <div class="relative">
                    <select wire:model.live="style" 
                            class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2.5 text-xs text-slate-800 focus:bg-white focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900 transition-all cursor-pointer">
                        <option value="">Semua Gaya</option>
                        @foreach ($styles as $option)
                            <option value="{{ $option->value }}">{{ $option->label() }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Rentang RAB --}}
                <div class="relative">
                    <select wire:model.live="rab" 
                            class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2.5 text-xs text-slate-800 focus:bg-white focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900 transition-all cursor-pointer">
                        <option value="">Semua Rentang RAB</option>
                        @foreach ($rabRanges as $key => [$min, $max, $label])
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Ketersediaan Blueprint --}}
                <div class="relative">
                    <select wire:model.live="ready" 
                            class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2.5 text-xs text-slate-800 focus:bg-white focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900 transition-all cursor-pointer">
                        <option value="">Semua Paket Desain</option>
                        <option value="1">Blueprint Siap Bangun</option>
                    </select>
                </div>

                {{-- Urutan Pengurutan --}}
                <div class="relative">
                    <select wire:model.live="sort" 
                            class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2.5 text-xs text-slate-800 focus:bg-white focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900 transition-all cursor-pointer">
                        <option value="newest">Karya Terbaru</option>
                        <option value="rab_asc">Estimasi RAB: Terendah</option>
                        <option value="rab_desc">Estimasi RAB: Tertinggi</option>
                    </select>
                </div>

            </div>

            {{-- Reset Filter Toolbar --}}
            <div class="mt-3 flex items-center justify-between border-t border-slate-100 pt-3 text-xs">
                <span class="text-slate-400">Gunakan filter di atas untuk mempersempit acuan budget konstruksi Anda.</span>
                <button type="button" wire:click="resetFilters" class="font-semibold text-slate-600 hover:text-rose-600 transition-colors">
                    Reset Filter
                </button>
            </div>
        </div>

        {{-- Grid Hasil Karya Arsitek (3 Kolom) --}}
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 transition-opacity duration-200" wire:loading.class="opacity-60">
            @forelse ($portfolios as $portfolio)
                <x-portfolio-card :portfolio="$portfolio" wire:key="portfolio-{{ $portfolio->id }}" />
            @empty
                <div class="col-span-full rounded-3xl border border-dashed border-slate-300 bg-white p-12 text-center shadow-sm">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21l-7-7m0 0l-7-7m7 7H3"/></svg>
                    </div>
                    <h3 class="mt-4 text-sm font-bold text-slate-900">Belum ada karya yang cocok</h3>
                    <p class="mt-1 text-xs text-slate-500 max-w-sm mx-auto">Tidak ditemukan portofolio yang sesuai dengan kombinasi filter dan rentang RAB yang Anda pilih.</p>
                    <button type="button" wire:click="resetFilters" class="mt-4 inline-flex items-center gap-1.5 px-4 py-2 bg-slate-900 text-white rounded-xl text-xs font-semibold hover:bg-slate-800 transition-colors">
                        Tampilkan Semua Portofolio
                    </button>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        <div class="mt-10 border-t border-slate-200/80 pt-6">
            {{ $portfolios->links() }}
        </div>

    </div>
</div>