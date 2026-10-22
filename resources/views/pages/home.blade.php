<x-layouts.app title="Beranda">
    <section class="bg-gradient-to-br from-blue-700 to-blue-500 text-white">
        <div class="mx-auto max-w-7xl px-4 py-16 md:py-24">
            <h1 class="max-w-2xl text-3xl font-bold leading-tight md:text-5xl">Temukan rumah &amp; ruko impian, lengkap dengan konsultasi arsitek.</h1>
            <p class="mt-4 max-w-xl text-blue-100">Cari properti terverifikasi, hitung cicilan KPR, dan pesan jadwal konsultasi arsitek dalam satu tempat.</p>

            <form action="{{ route('properties.index') }}" method="GET" class="mt-8 grid max-w-3xl gap-3 rounded-2xl bg-white p-3 shadow-lg md:grid-cols-[1fr_auto_auto]">
                <input type="text" name="q" placeholder="Cari lokasi atau nama properti, mis. Depok" class="rounded-xl border border-gray-200 px-4 py-3 text-gray-800 focus:border-blue-500 focus:outline-none">
                <select name="property_type" class="rounded-xl border border-gray-200 px-4 py-3 text-gray-800">
                    <option value="">Semua tipe</option>
                    <option value="house">Rumah</option>
                    <option value="shop">Ruko</option>
                </select>
                <button type="submit" class="rounded-xl bg-blue-600 px-6 py-3 font-medium text-white hover:bg-blue-700">Cari</button>
            </form>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-12">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([
                ['Cari Properti', 'Filter lokasi, harga, dan fasilitas.', route('properties.index')],
                ['Lihat di Peta', 'Jelajahi properti langsung dari peta.', route('map')],
                ['Jasa Arsitek', 'Konsultasi dan desain dari profesional.', route('architects.index')],
                ['Simulasi KPR', 'Estimasi cicilan bulanan Anda.', route('kpr')],
            ] as [$title, $text, $url])
                <a href="{{ $url }}" class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition hover:shadow-md">
                    <h3 class="font-semibold text-gray-900">{{ $title }}</h3>
                    <p class="mt-1 text-sm text-gray-500">{{ $text }}</p>
                </a>
            @endforeach
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4">
        <div class="flex items-end justify-between">
            <h2 class="text-2xl font-bold">Properti terbaru</h2>
            <a href="{{ route('properties.index') }}" class="text-sm font-medium text-blue-700 hover:underline">Lihat semua &rarr;</a>
        </div>
        <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($latest as $property)
                <x-property-card :property="$property" />
            @empty
                <p class="col-span-full rounded-2xl border border-dashed border-gray-300 p-10 text-center text-gray-500">Belum ada properti yang tayang.</p>
            @endforelse
        </div>
    </section>

    @if (count($services) > 0)
        <section class="mx-auto mt-14 max-w-7xl px-4">
            <div class="flex items-end justify-between">
                <h2 class="text-2xl font-bold">Layanan arsitek</h2>
                <a href="{{ route('architects.index') }}" class="text-sm font-medium text-blue-700 hover:underline">Lihat semua &rarr;</a>
            </div>
            <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($services as $service)
                    <x-architect-card :service="$service" />
                @endforeach
            </div>
        </section>
    @endif
</x-layouts.app>
