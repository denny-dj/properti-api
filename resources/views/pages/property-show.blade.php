<x-layouts.app :title="$property->title">
@php
    $label = $property->property_type === \App\Enums\PropertyType::House ? 'Rumah' : 'Ruko';
    $images = $property->images;
    $hasCoords = $property->latitude !== null && $property->longitude !== null;
    $profile = $property->partner?->partnerProfile;
@endphp
<div class="mx-auto max-w-7xl px-4 py-8">
    <nav class="mb-4 text-sm text-gray-500">
        <a href="{{ route('home') }}" class="hover:underline">Beranda</a> /
        <a href="{{ route('properties.index') }}" class="hover:underline">Properti</a> /
        <span>{{ $property->city }}</span>
    </nav>

    <div class="grid gap-8 lg:grid-cols-3">
        <div class="space-y-8 lg:col-span-2">
            {{-- Galeri --}}
            <div x-data="{ active: 0 }">
                <div class="relative aspect-[16/10] overflow-hidden rounded-2xl bg-gray-100">
                    @foreach ($images as $i => $image)
                        <img x-show="active === {{ $i }}" @if ($i > 0) x-cloak @endif
                             src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($image->path) }}"
                             alt="{{ $property->title }} - foto {{ $i + 1 }}" class="absolute inset-0 h-full w-full object-cover" onerror="this.remove()">
                    @endforeach
                    @if ($images->isEmpty())
                        <div class="flex h-full items-center justify-center text-gray-400">Belum ada foto</div>
                    @endif
                </div>
                @if ($images->count() > 1)
                    <div class="mt-3 flex gap-2 overflow-x-auto">
                        @foreach ($images as $i => $image)
                            <button type="button" x-on:click="active = {{ $i }}" class="h-16 w-24 shrink-0 overflow-hidden rounded-lg border-2 bg-gray-100"
                                    x-bind:class="active === {{ $i }} ? 'border-blue-600' : 'border-transparent'">
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($image->path) }}" alt="" class="h-full w-full object-cover" onerror="this.remove()">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Judul & harga --}}
            <div>
                <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-medium text-blue-700">{{ $label }} · {{ $property->listing_type->value === 'sale' ? 'Dijual' : 'Disewa' }}</span>
                <h1 class="mt-3 text-2xl font-bold text-gray-900 md:text-3xl">{{ $property->title }}</h1>
                <p class="mt-1 text-gray-500">{{ $property->address }}, {{ $property->district ? $property->district.', ' : '' }}{{ $property->city }}, {{ $property->province }}</p>
                <p class="mt-4 text-3xl font-bold text-blue-700"><x-rupiah :value="$property->price" /></p>
                <p class="mt-1 text-xs text-gray-400">Dilihat {{ number_format($property->views_count, 0, ',', '.') }} kali</p>
            </div>

            {{-- Spesifikasi --}}
            <div class="grid grid-cols-2 gap-4 rounded-2xl border border-gray-200 bg-white p-5 sm:grid-cols-3">
                @if ($property->bedrooms > 0)
                    <div><p class="text-xs text-gray-500">Kamar tidur</p><p class="font-semibold">{{ $property->bedrooms }}</p></div>
                @endif
                <div><p class="text-xs text-gray-500">Kamar mandi</p><p class="font-semibold">{{ $property->bathrooms }}</p></div>
                <div><p class="text-xs text-gray-500">Jumlah lantai</p><p class="font-semibold">{{ $property->floors }}</p></div>
                @if ($property->land_area)
                    <div><p class="text-xs text-gray-500">Luas tanah</p><p class="font-semibold">{{ number_format((float) $property->land_area, 0, ',', '.') }} m²</p></div>
                @endif
                @if ($property->building_area)
                    <div><p class="text-xs text-gray-500">Luas bangunan</p><p class="font-semibold">{{ number_format((float) $property->building_area, 0, ',', '.') }} m²</p></div>
                @endif
                @if ($property->certificate)
                    <div><p class="text-xs text-gray-500">Sertifikat</p><p class="font-semibold">{{ $property->certificate }}</p></div>
                @endif
            </div>

            @if ($property->facilities->isNotEmpty())
                <div>
                    <h2 class="text-lg font-semibold">Fasilitas</h2>
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach ($property->facilities as $facility)
                            <span class="rounded-full border border-gray-200 bg-white px-3 py-1 text-sm text-gray-700">{{ $facility->name }}</span>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($property->description)
                <div>
                    <h2 class="text-lg font-semibold">Deskripsi</h2>
                    <div class="mt-3 leading-relaxed text-gray-700">{!! nl2br(e($property->description)) !!}</div>
                </div>
            @endif

            @if ($hasCoords)
                <div>
                    <h2 class="mb-3 text-lg font-semibold">Lokasi</h2>
                    <x-map-single :lat="$property->latitude" :lng="$property->longitude" :title="$property->title" />
                </div>
            @endif
        </div>

        <aside class="space-y-6 lg:sticky lg:top-24 lg:self-start">
            <div class="rounded-2xl border border-gray-200 bg-white p-5">
                <p class="text-xs uppercase tracking-wide text-gray-400">Ditawarkan oleh</p>
                <p class="mt-1 font-semibold text-gray-900">{{ $profile?->business_name ?? $property->partner?->name }}</p>
                <p class="text-sm text-gray-500">{{ $profile?->partner_type?->value === 'architect' ? 'Arsitek' : 'Developer / Agen properti' }}@if ($profile?->city) · {{ $profile->city }}@endif</p>
            </div>

            <livewire:kpr-calculator :price="$property->price" />
        </aside>
    </div>

    @if ($related->isNotEmpty())
        <section class="mt-14">
            <h2 class="text-xl font-bold">Properti serupa di {{ $property->city }}</h2>
            <div class="mt-5 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($related as $item)
                    <x-property-card :property="$item" />
                @endforeach
            </div>
        </section>
    @endif
</div>
</x-layouts.app>
