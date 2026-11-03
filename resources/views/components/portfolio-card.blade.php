@props(['portfolio'])
@php $cover = $portfolio->coverImage; @endphp
<a href="{{ route('portfolios.show', $portfolio->slug) }}" {{ $attributes->merge(['class' => 'group flex flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition hover:shadow-md']) }}>
    <div class="relative aspect-[16/10] bg-gray-100">
        @if ($cover)
            <img src="{{ $cover->url() }}" alt="{{ $portfolio->title }}" loading="lazy" class="absolute inset-0 h-full w-full object-cover" onerror="this.remove()">
        @else
            <div class="flex h-full items-center justify-center text-sm text-gray-400">Belum ada foto</div>
        @endif
        @if ($portfolio->is_ready_to_build)
            <span class="absolute left-3 top-3 rounded-full bg-green-600 px-2.5 py-1 text-xs font-medium text-white">Siap bangun</span>
        @endif
    </div>
    <div class="flex flex-1 flex-col p-5">
        <div class="flex flex-wrap gap-2">
            <span class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700">{{ $portfolio->category->label() }}</span>
            <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700">{{ $portfolio->architectural_style->label() }}</span>
        </div>
        <h3 class="mt-3 line-clamp-2 font-semibold text-gray-900 group-hover:text-blue-700">{{ $portfolio->title }}</h3>
        <p class="mt-1 text-sm text-gray-500">{{ $portfolio->architect?->partnerProfile?->business_name ?? $portfolio->architect?->name }}</p>
        <div class="mt-auto pt-4">
            <p class="text-xs text-gray-400">Estimasi RAB</p>
            <p class="text-lg font-semibold text-blue-700"><x-rupiah :value="$portfolio->estimated_rab" /></p>
            <p class="text-xs text-gray-500">±<x-rupiah :value="$portfolio->cost_per_sqm" />/m² · {{ number_format((float) $portfolio->area_sqm, 0, ',', '.') }} m²</p>
            @if ($portfolio->is_ready_to_build && $portfolio->blueprint_price)
                <p class="mt-2 text-sm text-green-700">Paket desain: <x-rupiah :value="$portfolio->blueprint_price" /></p>
            @endif
        </div>
    </div>
</a>
