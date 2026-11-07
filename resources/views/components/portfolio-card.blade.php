@props(['portfolio'])
@php
    $profile = $portfolio->architect?->partnerProfile;
    $architectName = $profile?->business_name ?? $portfolio->architect?->name;

    // Foto cover; bila kosong, pakai foto arsitektur cadangan (config concierge.fallback_portfolio_images)
    $fallbacks = array_values((array) config('concierge.fallback_portfolio_images', []));
    $imageUrl = $portfolio->coverImage?->url() ?? ($fallbacks ? $fallbacks[$portfolio->id % count($fallbacks)] : null);
@endphp
<article {{ $attributes->merge(['class' => 'group relative flex flex-col justify-between overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl']) }}>
    <div>
        <div class="relative aspect-[16/10] overflow-hidden bg-slate-100">
            @if ($imageUrl)
                <img src="{{ $imageUrl }}" alt="{{ $portfolio->title }}" loading="lazy" referrerpolicy="no-referrer"
                     class="aspect-[16/10] w-full object-cover transition duration-500 group-hover:scale-105" onerror="this.remove()">
            @endif
            <div class="pointer-events-none absolute inset-x-0 bottom-0 h-1/3 bg-gradient-to-t from-slate-900/40 to-transparent"></div>
            <span class="absolute left-3 top-3 rounded-full bg-slate-900/80 px-2.5 py-1 text-xs font-semibold text-white backdrop-blur">{{ $portfolio->architectural_style->label() }}</span>
        </div>

        <div class="p-4">
            <span class="w-fit rounded-md bg-blue-50 px-2 py-1 text-xs font-semibold text-blue-700">{{ $portfolio->category->label() }}</span>

            <h3 class="mt-2 line-clamp-2 font-semibold text-slate-900">
                <a href="{{ route('portfolios.show', $portfolio->slug) }}" class="after:absolute after:inset-0 after:content-[''] group-hover:text-blue-600">{{ $portfolio->title }}</a>
            </h3>

            @if ($architectName)
                <p class="mt-1 flex items-center gap-1.5 text-sm text-slate-500">
                    <x-icon name="shield" class="h-4 w-4 shrink-0 text-emerald-600" /> <span class="truncate">{{ $architectName }}</span>
                    <span class="shrink-0 rounded bg-emerald-50 px-1.5 py-0.5 text-[10px] font-semibold text-emerald-700">Verified</span>
                </p>
            @endif

            {{-- Badge RAB (Urutan class & helper dikembalikan presisi sesuai pengetesan) --}}
            <div class="mt-3">
                <span class="inline-flex flex-wrap items-center gap-x-1 rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-medium text-white">
                    Estimasi RAB Fisik: <span class="font-semibold">{{ \App\Support\Format::rabRange($portfolio->estimated_rab) }}</span>
                </span>
                <p class="mt-1.5 text-xs text-slate-500">±<x-rupiah :value="$portfolio->cost_per_sqm" />/m² · {{ number_format((float) $portfolio->area_sqm, 0, ',', '.') }} m²</p>
            </div>

            @if ($portfolio->is_ready_to_build)
                <div class="mt-3 flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1 rounded-lg border border-emerald-500/20 bg-emerald-500/10 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                        <x-icon name="blueprint" class="h-3.5 w-3.5" /> Blueprint Siap Bangun
                    </span>
                    @if ($portfolio->blueprint_price)
                        <span class="text-xs font-medium text-slate-600">Paket Gambar Jadi · <x-rupiah :value="$portfolio->blueprint_price" /></span>
                    @endif
                </div>
            @else
                <p class="mt-3 flex items-center gap-2 text-xs text-slate-500"><x-icon name="tools" class="h-4 w-4 shrink-0 text-slate-400" /> Custom desain &amp; survei lokasi</p>
            @endif
        </div>
    </div>

    <div class="relative z-10 px-4 pb-4">
        <x-wa-button source="architect_portfolio" :reference="$portfolio->id" label="Konsultasi via Admin" variant="card" />
    </div>
</article>