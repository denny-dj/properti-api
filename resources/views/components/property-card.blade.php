@props(['property'])
@php
    $cover = $property->coverImage?->path;
    $isHouse = $property->property_type === \App\Enums\PropertyType::House;
    $partnerName = $property->partner?->partnerProfile?->business_name;
    $monthly = \App\Support\Format::kprMonthly($property->price);
@endphp
<article {{ $attributes->merge(['class' => 'group relative flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg']) }}>
    <div class="relative aspect-[4/3] bg-slate-100">
        @if ($cover)
            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($cover) }}" alt="{{ $property->title }}" loading="lazy"
                 class="h-full w-full object-cover transition duration-300 group-hover:scale-105" onerror="this.remove()">
        @endif
        <div class="absolute inset-x-0 bottom-0 h-1/3 bg-gradient-to-t from-slate-900/50 to-transparent"></div>
        <span class="absolute left-3 top-3 rounded-full bg-slate-900/80 px-2.5 py-1 text-xs font-semibold text-white backdrop-blur">{{ $isHouse ? 'Rumah' : 'Ruko' }}</span>
        <span class="absolute right-3 top-3 inline-flex items-center gap-1 rounded-full bg-emerald-500 px-2.5 py-1 text-xs font-semibold text-white">
            <x-icon name="shield" class="h-3.5 w-3.5" /> Verified
        </span>
    </div>

    <div class="flex flex-1 flex-col p-4">
        <p class="text-xl font-bold text-slate-900"><x-rupiah :value="$property->price" /></p>
        <p class="mt-1 inline-flex w-fit items-center rounded-md bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-700">
            Cicilan ±{{ \App\Support\Format::rupiahShort($monthly) }}/bln*
        </p>

        <h3 class="mt-3 line-clamp-2 font-semibold text-slate-900">
            <a href="{{ route('properties.show', $property->slug) }}" class="after:absolute after:inset-0 after:content-[''] group-hover:text-blue-600">{{ $property->title }}</a>
        </h3>
        <p class="mt-1 flex items-center gap-1 text-sm text-slate-500"><x-icon name="pin" class="h-4 w-4 shrink-0" /> {{ $property->city }}, {{ $property->province }}</p>

        <div class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-sm text-slate-600">
            @if ($property->land_area)<span class="inline-flex items-center gap-1"><x-icon name="area" /> LT {{ number_format((float) $property->land_area, 0, ',', '.') }} m²</span>@endif
            @if ($property->building_area)<span class="inline-flex items-center gap-1"><x-icon name="area" /> LB {{ number_format((float) $property->building_area, 0, ',', '.') }} m²</span>@endif
            @if ($property->bedrooms > 0)<span class="inline-flex items-center gap-1"><x-icon name="bed" /> {{ $property->bedrooms }} KT</span>@endif
            <span class="inline-flex items-center gap-1"><x-icon name="bath" /> {{ $property->bathrooms }} KM</span>
        </div>

        <div class="relative z-10 mt-auto pt-4">
            @if ($partnerName)
                <p class="mb-3 flex items-center gap-1.5 border-t border-slate-100 pt-3 text-xs text-slate-500">
                    <x-icon name="shield" class="h-4 w-4 text-emerald-600" /> Verified Partner · <span class="truncate font-medium text-slate-700">{{ $partnerName }}</span>
                </p>
            @endif
            <x-wa-button source="property_detail" :reference="$property->id" label="Tanya Admin Unit Ini" size="sm" />
            <p class="mt-2 text-[10px] leading-tight text-slate-400">*Simulasi {{ \App\Support\Format::kprAssumption() }}. Bukan penawaran bank.</p>
        </div>
    </div>
</article>
