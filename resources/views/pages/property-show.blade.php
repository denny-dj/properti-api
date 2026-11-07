<x-layouts.app :title="$property->title">
@php
    $label = $property->property_type === \App\Enums\PropertyType::House ? 'Rumah' : 'Ruko';
    $images = $property->images;
    $hasCoords = $property->latitude !== null && $property->longitude !== null;
    $profile = $property->partner?->partnerProfile;
@endphp

<div class="bg-slate-50/50 pb-20">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        
        {{-- Breadcrumb Editorial --}}
        <nav class="mb-6 flex items-center gap-2 text-xs font-medium text-slate-400">
            <a href="{{ route('home') }}" class="hover:text-blue-600 transition-colors">Beranda</a>
            <span>/</span>
            <a href="{{ route('properties.index') }}" class="hover:text-blue-600 transition-colors">Properti</a>
            <span>/</span>
            <span class="text-slate-700">{{ $property->city }}</span>
        </nav>

        {{-- Header Ringkas: Judul & Alamat --}}
        <div class="mb-8 flex flex-col justify-between gap-4 md:flex-row md:items-end">
            <div class="space-y-2">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center rounded-md bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">
                        {{ $label }} · {{ $property->listing_type->value === 'sale' ? 'Dijual' : 'Disewa' }}
                    </span>
                    <span class="inline-flex items-center gap-1 rounded-md bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                        <x-icon name="shield" class="h-3.5 w-3.5 text-emerald-600" /> Terverifikasi Fisik & Legalitas
                    </span>
                </div>
                <h1 class="text-2xl sm:text-4xl font-extrabold tracking-tight text-slate-900">{{ $property->title }}</h1>
                <p class="text-sm text-slate-500 flex items-center gap-1.5">
                    <svg class="h-4 w-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    {{ $property->address }}, {{ $property->district ? $property->district.', ' : '' }}{{ $property->city }}, {{ $property->province }}
                </p>
            </div>
            <div class="md:text-right shrink-0">
                <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Harga Penawaran</p>
                <p class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight"><x-rupiah :value="$property->price" /></p>
                <p class="mt-1 text-xs text-slate-400">Dilihat {{ number_format($property->views_count, 0, ',', '.') }} kali</p>
            </div>
        </div>

        <div class="grid gap-10 lg:grid-cols-12">
            
            {{-- Konten Utama (Kiri - 8 Kolom) --}}
            <div class="space-y-10 lg:col-span-8">
                
                {{-- Galeri Gambar Modern --}}
                <div x-data="{ active: 0 }" class="overflow-hidden rounded-3xl border border-slate-200/80 bg-white p-2 shadow-sm">
                    <div class="relative aspect-[16/10] overflow-hidden rounded-2xl bg-slate-100">
                        @foreach ($images as $i => $image)
                            <img x-show="active === {{ $i }}" @if ($i > 0) x-cloak @endif
                                 src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($image->path) }}"
                                 alt="{{ $property->title }} - foto {{ $i + 1 }}" 
                                 class="absolute inset-0 h-full w-full object-cover transition-all duration-300" onerror="this.remove()">
                        @endforeach
                        @if ($images->isEmpty())
                            <div class="flex h-full items-center justify-center text-slate-400 font-medium">Belum ada dokumentasi foto</div>
                        @endif
                    </div>
                    @if ($images->count() > 1)
                        <div class="mt-2.5 flex gap-2.5 overflow-x-auto pb-1">
                            @foreach ($images as $i => $image)
                                <button type="button" x-on:click="active = {{ $i }}" 
                                        class="h-20 w-28 shrink-0 overflow-hidden rounded-xl border-2 transition-all"
                                        x-bind:class="active === {{ $i }} ? 'border-slate-900 opacity-100 shadow-md' : 'border-transparent opacity-60 hover:opacity-100'">
                                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($image->path) }}" alt="" class="h-full w-full object-cover" onerror="this.remove()">
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Spesifikasi Utama (6 Kotak Minimalis) --}}
                <div>
                    <h2 class="text-sm font-bold uppercase tracking-wider text-slate-400 mb-4">Spesifikasi Bangunan</h2>
                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
                        <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
                            <span class="text-xs text-slate-400">Luas Tanah</span>
                            <p class="mt-1 text-lg font-bold text-slate-900">{{ $property->land_area ? number_format((float) $property->land_area, 0, ',', '.').' m²' : '-' }}</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
                            <span class="text-xs text-slate-400">Luas Bangunan</span>
                            <p class="mt-1 text-lg font-bold text-slate-900">{{ $property->building_area ? number_format((float) $property->building_area, 0, ',', '.').' m²' : '-' }}</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
                            <span class="text-xs text-slate-400">Kamar Tidur / Mandi</span>
                            <p class="mt-1 text-lg font-bold text-slate-900">{{ $property->bedrooms ?? 0 }} KT · {{ $property->bathrooms ?? 0 }} KM</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
                            <span class="text-xs text-slate-400">Jumlah Lantai</span>
                            <p class="mt-1 text-lg font-bold text-slate-900">{{ $property->floors ?? 1 }} Lantai</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
                            <span class="text-xs text-slate-400">Legalitas</span>
                            <p class="mt-1 text-lg font-bold text-slate-900">{{ $property->certificate ?? 'SHM' }}</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
                            <span class="text-xs text-slate-400">Status Listing</span>
                            <p class="mt-1 text-lg font-bold text-slate-900">{{ $property->listing_type->value === 'sale' ? 'Siap Huni' : 'Disewakan' }}</p>
                        </div>
                    </div>
                </div>

                {{-- Fasilitas --}}
                @if ($property->facilities->isNotEmpty())
                    <div>
                        <h2 class="text-lg font-bold text-slate-900 mb-3">Fasilitas Properti</h2>
                        <div class="flex flex-wrap gap-2.5">
                            @foreach ($property->facilities as $facility)
                                <span class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-sm">
                                    <svg class="h-3.5 w-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                    {{ $facility->name }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Deskripsi Unit --}}
                @if ($property->description)
                    <div>
                        <h2 class="text-lg font-bold text-slate-900 mb-3">Deskripsi Lengkap</h2>
                        <div class="rounded-3xl border border-slate-200/80 bg-white p-6 sm:p-8 shadow-sm">
                            <div class="leading-relaxed text-slate-600 text-sm sm:text-base space-y-4">
                                {!! nl2br(e($property->description)) !!}
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Lokasi & Peta --}}
                @if ($hasCoords)
                    <div>
                        <h2 class="text-lg font-bold text-slate-900 mb-3">Lokasi Unit</h2>
                        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white p-3 shadow-sm">
                            <x-map-single :lat="$property->latitude" :lng="$property->longitude" :title="$property->title" />
                        </div>
                    </div>
                @endif

                {{-- Escrow Trust Banner --}}
                <div class="rounded-3xl bg-slate-900 p-6 sm:p-8 text-white shadow-xl flex items-start gap-5">
                    <div class="hidden sm:flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                        <x-icon name="wallet" class="h-6 w-6" />
                    </div>
                    <div class="space-y-1">
                        <h3 class="text-base font-bold text-white">Transaksi Aman Bergaransi Concierge</h3>
                        <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">
                            Jangan pernah melakukan transfer dana tanda jadi ke rekening pribadi penjual. Seluruh proses pembayaran transaksi divalidasi dan ditampung melalui rekening resmi platform demi jaminan keamanan legalitas Anda.
                        </p>
                    </div>
                </div>

            </div>

            {{-- Sidebar Kanan Sticky (4 Kolom) --}}
            <aside class="space-y-6 lg:col-span-4 lg:sticky lg:top-24 lg:self-start">
                
                {{-- Developer / Agen Card --}}
                <div class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Listing Ditawarkan Oleh</p>
                    <div class="mt-3 flex items-center gap-3">
                        <div class="h-12 w-12 rounded-2xl bg-slate-100 flex items-center justify-center font-bold text-slate-700 text-lg border border-slate-200">
                            {{ substr($profile?->business_name ?? $property->partner?->name ?? 'M', 0, 1) }}
                        </div>
                        <div>
                            <p class="font-bold text-slate-900 leading-snug">{{ $profile?->business_name ?? $property->partner?->name }}</p>
                            <p class="text-xs text-slate-500 mt-0.5">{{ $profile?->partner_type?->value === 'architect' ? 'Mitra Arsitek' : 'Developer / Agen Resmi' }}@if ($profile?->city) · {{ $profile->city }}@endif</p>
                        </div>
                    </div>
                </div>

                {{-- Livewire CTA Konsultasi WhatsApp --}}
                <div class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm">
                    <livewire:concierge-cta source="property_detail" :reference-id="$property->id" />
                </div>

                @if (config('concierge.legacy_ui', true))
                    <livewire:property-actions :property="$property->id" />
                @endif

                {{-- Livewire Kalkulator KPR --}}
                <div class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm">
                    <livewire:kpr-calculator :price="$property->price" :property-id="$property->id" />
                </div>

            </aside>
        </div>

        {{-- Properti Serupa --}}
        @if ($related->isNotEmpty())
            <section class="mt-20 border-t border-slate-200 pt-12">
                <div class="mb-6">
                    <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Properti Serupa di {{ $property->city }}</h2>
                    <p class="text-sm text-slate-500 mt-1">Listing terkurasi lainnya di sekitar kawasan yang sama.</p>
                </div>
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($related as $item)
                        <x-property-card :property="$item" />
                    @endforeach
                </div>
            </section>
        @endif

    </div>
</div>
</x-layouts.app>