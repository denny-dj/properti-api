@props(['portfolio'])
@php
    $cover = $portfolio->coverImage;
    $profile = $portfolio->architect?->partnerProfile;
    $architectName = $profile?->business_name ?? $portfolio->architect?->name;
@endphp
<article {{ $attributes->merge(['class' => 'group relative flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg']) }}>
    <div class="relative aspect-[16/10] bg-slate-100">
        @if ($cover)
            <img src="{{ $cover->url() }}" alt="{{ $portfolio->title }}" loading="lazy"
                 class="absolute inset-0 h-full w-full object-cover transition duration-300 group-hover:scale-105" onerror="this.remove()">
        @else
            <div class="flex h-full items-center justify-center text-sm text-slate-400">Belum ada foto</div>
        @endif
        <div class="absolute inset-x-0 bottom-0 h-1/3 bg-gradient-to-t from-slate-900/50 to-transparent"></div>
        <span class="absolute left-3 top-3 rounded-full bg-slate-900/80 px-2.5 py-1 text-xs font-semibold text-white backdrop-blur">{{ $portfolio->architectural_style->label() }}</span>
        @if ($portfolio->is_ready_to_build)
            <span class="absolute right-3 top-3 inline-flex items-center gap-1 rounded-full bg-emerald-500 px-2.5 py-1 text-xs font-semibold text-white">
                <x-icon name="blueprint" class="h-3.5 w-3.5" /> Blueprint siap bangun
            </span>
        @endif
    </div>

    <div class="flex flex-1 flex-col p-4">
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

        {{-- Badge RAB --}}
        <div class="mt-3 rounded-xl bg-slate-900 px-4 py-3 text-white">
            <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Estimasi RAB Fisik</p>
            <p class="mt-0.5 text-lg font-bold leading-tight">{{ \App\Support\Format::rabRange($portfolio->estimated_rab) }}</p>
            <p class="mt-0.5 text-xs text-slate-400">±<x-rupiah :value="$portfolio->cost_per_sqm" />/m² · {{ number_format((float) $portfolio->area_sqm, 0, ',', '.') }} m²</p>
        </div>

        @if ($portfolio->is_ready_to_build && $portfolio->blueprint_price)
            <p class="mt-3 flex items-center gap-2 rounded-lg bg-emerald-50 px-3 py-2 text-sm font-medium text-emerald-800">
                <x-icon name="blueprint" class="h-4 w-4 shrink-0" /> Paket Gambar Jadi · <x-rupiah :value="$portfolio->blueprint_price" />
            </p>
        @else
            <p class="mt-3 flex items-center gap-2 rounded-lg bg-slate-50 px-3 py-2 text-sm text-slate-600">
                <x-icon name="tools" class="h-4 w-4 shrink-0" /> Custom desain &amp; survei lokasi
            </p>
        @endif

        <div class="relative z-10 mt-auto pt-4">
            <x-wa-button source="architect_portfolio" :reference="$portfolio->id" label="Konsultasi via Admin" size="sm" />
        </div>
    </div>
</article>
