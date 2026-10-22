@props(['property'])
@php
    $cover = $property->coverImage?->path;
    $label = $property->property_type === \App\Enums\PropertyType::House ? 'Rumah' : 'Ruko';
@endphp
<a href="{{ route('properties.show', $property->slug) }}" class="group block overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition hover:shadow-md">
    <div class="relative aspect-[4/3] bg-gray-100">
        @if ($cover)
            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($cover) }}" alt="{{ $property->title }}" loading="lazy" class="h-full w-full object-cover" onerror="this.remove()">
        @endif
        <span class="absolute left-3 top-3 rounded-full bg-white/90 px-2.5 py-1 text-xs font-medium text-gray-700">{{ $label }}</span>
    </div>
    <div class="p-4">
        <p class="text-lg font-semibold text-blue-700"><x-rupiah :value="$property->price" /></p>
        <h3 class="mt-1 line-clamp-2 font-medium text-gray-900 group-hover:text-blue-700">{{ $property->title }}</h3>
        <p class="mt-1 text-sm text-gray-500">{{ $property->city }}, {{ $property->province }}</p>
        <div class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-sm text-gray-600">
            @if ($property->bedrooms > 0)<span>{{ $property->bedrooms }} KT</span>@endif
            <span>{{ $property->bathrooms }} KM</span>
            @if ($property->land_area)<span>LT {{ number_format((float) $property->land_area, 0, ',', '.') }} m²</span>@endif
            @if ($property->building_area)<span>LB {{ number_format((float) $property->building_area, 0, ',', '.') }} m²</span>@endif
        </div>
    </div>
</a>
