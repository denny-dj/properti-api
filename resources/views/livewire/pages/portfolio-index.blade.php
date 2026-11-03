<div class="mx-auto max-w-7xl px-4 py-8">
    <h1 class="text-2xl font-bold">Portofolio arsitek</h1>
    <p class="mt-1 text-gray-500">Pilih desain berdasarkan estimasi RAB. Beli desain siap bangun, atau minta penyesuaian custom lewat admin kami.</p>

    <div class="mt-6 grid gap-3 rounded-2xl border border-gray-200 bg-white p-4 sm:grid-cols-2 lg:grid-cols-6">
        <input type="text" wire:model.live.debounce.500ms="q" placeholder="Cari desain" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
        <select wire:model.live="category" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
            <option value="">Semua kategori</option>
            @foreach ($categories as $option)
                <option value="{{ $option->value }}">{{ $option->label() }}</option>
            @endforeach
        </select>
        <select wire:model.live="style" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
            <option value="">Semua gaya</option>
            @foreach ($styles as $option)
                <option value="{{ $option->value }}">{{ $option->label() }}</option>
            @endforeach
        </select>
        <select wire:model.live="rab" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
            <option value="">Semua rentang RAB</option>
            @foreach ($rabRanges as $key => [$min, $max, $label])
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </select>
        <select wire:model.live="ready" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
            <option value="">Semua paket</option>
            <option value="1">Desain siap bangun</option>
        </select>
        <select wire:model.live="sort" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
            <option value="newest">Terbaru</option>
            <option value="rab_asc">RAB terendah</option>
            <option value="rab_desc">RAB tertinggi</option>
        </select>
    </div>
    <button type="button" wire:click="resetFilters" class="mt-2 text-sm font-medium text-blue-700 hover:underline">Reset filter</button>

    <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3" wire:loading.class="opacity-60">
        @forelse ($portfolios as $portfolio)
            <x-portfolio-card :portfolio="$portfolio" wire:key="portfolio-{{ $portfolio->id }}" />
        @empty
            <div class="col-span-full rounded-2xl border border-dashed border-gray-300 p-12 text-center text-gray-500">
                Belum ada portofolio yang cocok dengan filter Anda.
            </div>
        @endforelse
    </div>

    <div class="mt-8">{{ $portfolios->links() }}</div>
</div>
