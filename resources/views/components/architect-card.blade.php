@props(['service'])
@php
    $types = ['consultation' => 'Konsultasi', 'design' => 'Desain', 'supervision' => 'Pengawasan'];
    $profile = $service->architect?->partnerProfile;
@endphp
<a href="{{ route('architects.show', $service->slug) }}" class="group flex flex-col rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition hover:shadow-md">
    <span class="w-fit rounded-full bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700">{{ $types[$service->service_type->value] ?? $service->service_type->value }}</span>
    <h3 class="mt-3 line-clamp-2 font-semibold text-gray-900 group-hover:text-blue-700">{{ $service->title }}</h3>
    <p class="mt-1 text-sm text-gray-500">{{ $profile?->business_name ?? $service->architect?->name }}@if ($profile?->city) · {{ $profile->city }}@endif</p>
    <div class="mt-auto flex items-end justify-between pt-4">
        <p class="text-lg font-semibold text-blue-700"><x-rupiah :value="$service->price" /></p>
        <p class="text-sm text-gray-500">{{ $service->duration_minutes }} menit</p>
    </div>
</a>
