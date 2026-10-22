<x-layouts.app :title="$service->title">
@php
    $types = ['consultation' => 'Konsultasi', 'design' => 'Desain', 'supervision' => 'Pengawasan pembangunan'];
    $profile = $service->architect?->partnerProfile;
@endphp
<div class="mx-auto max-w-5xl px-4 py-8">
    <nav class="mb-4 text-sm text-gray-500">
        <a href="{{ route('home') }}" class="hover:underline">Beranda</a> /
        <a href="{{ route('architects.index') }}" class="hover:underline">Arsitek</a>
    </nav>

    <div class="grid gap-8 lg:grid-cols-5">
        <div class="space-y-6 lg:col-span-3">
            <div>
                <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-medium text-blue-700">{{ $types[$service->service_type->value] ?? $service->service_type->value }}</span>
                <h1 class="mt-3 text-2xl font-bold text-gray-900 md:text-3xl">{{ $service->title }}</h1>
                <p class="mt-3 text-3xl font-bold text-blue-700"><x-rupiah :value="$service->price" /></p>
                <p class="text-sm text-gray-500">Durasi {{ $service->duration_minutes }} menit</p>
            </div>

            @if ($service->description)
                <div>
                    <h2 class="text-lg font-semibold">Tentang layanan</h2>
                    <div class="mt-2 leading-relaxed text-gray-700">{!! nl2br(e($service->description)) !!}</div>
                </div>
            @endif

            <div class="rounded-2xl border border-gray-200 bg-white p-5">
                <p class="text-xs uppercase tracking-wide text-gray-400">Arsitek</p>
                <p class="mt-1 font-semibold text-gray-900">{{ $profile?->business_name ?? $service->architect?->name }}</p>
                @if ($profile?->city)<p class="text-sm text-gray-500">{{ $profile->city }}</p>@endif
                @if ($profile?->license_number)<p class="mt-1 text-sm text-gray-600">No. izin/keanggotaan: {{ $profile->license_number }}</p>@endif
                @if ($profile?->description)<p class="mt-3 text-sm text-gray-700">{{ $profile->description }}</p>@endif
                @if ($profile?->portfolio_url)
                    <a href="{{ $profile->portfolio_url }}" target="_blank" rel="noopener nofollow" class="mt-3 inline-block text-sm font-medium text-blue-700 hover:underline">Lihat portofolio &rarr;</a>
                @endif
            </div>
        </div>

        <aside class="lg:col-span-2">
            <livewire:architect-slots :slug="$service->slug" />
        </aside>
    </div>
</div>
</x-layouts.app>
