{{--
    Beranda "Managed Concierge Marketplace". Navbar & footer berasal dari layout (x-layouts.app); halaman ini TIDAK
    boleh memuat <header>/<nav> sendiri.

    Urutan: Hero + 4 highlight -> Properti unggulan -> Portofolio arsitek -> Layanan & Jasa Arsitek -> Cara kerja -> Banner KPR

    Variabel dari HomeController:
      $heroBanner         ?AdBanner   banner top_hero (null -> hero bawaan platform)
      $featuredProperties array       properti unggulan (alias lama: $latest)
      $portfolios         array       portofolio arsitek pilihan
      $promotions         Collection  banner strip featured_developer (strip hanya muncul bila ada isinya)
      $services           array       layanan per jam (kosong bila CONCIERGE_LEGACY_UI=false)
--}}
@php
    $featuredProperties ??= $latest ?? [];
    $portfolios ??= [];
    $promotions ??= collect();
    $services ??= [];
    $heroBanner ??= null;
@endphp
<x-layouts.app title="Beranda">
<div class="bg-slate-50/50 font-sans">

    {{-- 1. Hero + 4 highlight fitur platform --}}
    <x-hero-ad-banner :banner="$heroBanner" />

    <section class="border-b border-slate-100 bg-white">
        <div class="mx-auto grid max-w-7xl gap-4 px-4 py-6 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([
                ['shield', 'Partner terverifikasi', 'Developer & arsitek disaring admin.'],
                ['chat', 'Didampingi admin', 'Konsultasi dan survei lewat WhatsApp.'],
                ['wallet', 'Bayar lewat platform', 'Tautan resmi, tanpa transfer pribadi.'],
                ['blueprint', 'RAB transparan', 'Estimasi biaya jelas sejak awal.'],
            ] as [$icon, $title, $text])
                <div class="flex items-start gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600"><x-icon :name="$icon" class="h-5 w-5" /></span>
                    <div>
                        <p class="text-sm font-semibold text-slate-900">{{ $title }}</p>
                        <p class="text-xs text-slate-500">{{ $text }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Strip iklan featured_developer: hanya dirender bila ada banner aktif (tidak ada blok kosong) --}}
    @if ($promotions->isNotEmpty())
        <x-promo-banners :banners="$promotions" />
    @endif

    {{-- 2. Properti unggulan --}}
    <section class="mx-auto mt-14 max-w-7xl px-4">
        <x-section-heading title="Properti unggulan" subtitle="Unit terverifikasi dengan estimasi cicilan. Tanyakan ketersediaan langsung ke admin." :href="route('properties.index')" link="Lihat semua properti" />
        <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($featuredProperties as $property)
                <x-property-card :property="$property" />
            @empty
                <p class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center text-slate-500">Belum ada properti yang tayang.</p>
            @endforelse
        </div>
    </section>

    {{-- 3. Portofolio arsitek pilihan --}}
    <section class="mx-auto mt-16 max-w-7xl px-4">
        <x-section-heading title="Portofolio arsitek pilihan" subtitle="Lihat estimasi RAB per desain. Beli desain siap bangun atau minta penyesuaian custom lewat admin." :href="route('portfolios.index')" link="Lihat semua portofolio" />
        <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($portfolios as $portfolio)
                <x-portfolio-card :portfolio="$portfolio" />
            @empty
                <p class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center text-slate-500">Portofolio arsitek segera hadir.</p>
            @endforelse
        </div>
    </section>

    {{-- 4. Layanan & Jasa Arsitek (model konsultasi per jam; hanya bila CONCIERGE_LEGACY_UI=true) --}}
    @if (config('concierge.legacy_ui', true) && count($services) > 0)
        <section class="mx-auto mt-16 max-w-7xl px-4">
            <x-section-heading title="Layanan & Jasa Arsitek" subtitle="Konsultasi per sesi dengan arsitek mitra." :href="route('architects.index')" link="Lihat semua layanan" />
            <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($services as $service)
                    <x-architect-card :service="$service" />
                @endforeach
            </div>
        </section>
    @endif

    {{-- 5. Cara kerja concierge (section gelap, 6 langkah) --}}
    <div class="mt-16">
        @include('sections.how-it-works')
    </div>

    {{-- 6. Banner simulasi KPR --}}
    <section class="mx-auto max-w-7xl px-4 py-16">
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-slate-900 via-slate-800 to-blue-700 px-6 py-10 text-white md:px-12 md:py-14">
            <div class="max-w-2xl">
                <h2 class="text-2xl font-bold tracking-tight md:text-3xl">Hitung cicilan, lalu ajukan KPR lewat bank rekanan</h2>
                <p class="mt-2 text-slate-300">Simulasikan DP, bunga, dan tenor. Bila cocok, admin kami meneruskan pengajuan Anda ke bank rekanan.</p>
                <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('kpr') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-blue-500">Hitung Simulasi KPR <x-icon name="arrow-right" class="h-4 w-4" /></a>
                    <a href="{{ route('how-it-works') }}" class="inline-flex items-center justify-center rounded-xl border border-white/30 px-6 py-3 text-sm font-semibold text-white transition hover:bg-white/10">Pelajari alurnya</a>
                </div>
            </div>
        </div>
    </section>

</div>
</x-layouts.app>
