<x-layouts.app :title="$portfolio->title">
@php
    $images = $portfolio->images;
    $profile = $portfolio->architect?->partnerProfile;
@endphp
<div class="mx-auto max-w-7xl px-4 py-8">
    <nav class="mb-4 text-sm text-gray-500">
        <a href="{{ route('home') }}" class="hover:underline">Beranda</a> /
        <a href="{{ route('portfolios.index') }}" class="hover:underline">Portofolio</a> /
        <span>{{ $portfolio->category->label() }}</span>
    </nav>

    <div class="grid gap-8 lg:grid-cols-3">
        <div class="space-y-8 lg:col-span-2">
            {{-- Galeri --}}
            <div x-data="{ active: 0 }">
                <div class="relative aspect-[16/10] overflow-hidden rounded-2xl bg-gray-100">
                    @foreach ($images as $i => $image)
                        <img x-show="active === {{ $i }}" @if ($i > 0) x-cloak @endif
                             src="{{ $image->url() }}" alt="{{ $portfolio->title }} - foto {{ $i + 1 }}"
                             class="absolute inset-0 h-full w-full object-cover" onerror="this.remove()">
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
                                <img src="{{ $image->url() }}" alt="" class="h-full w-full object-cover" onerror="this.remove()">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Judul & RAB --}}
            <div>
                <div class="flex flex-wrap gap-2">
                    <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-medium text-blue-700">{{ $portfolio->category->label() }}</span>
                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700">{{ $portfolio->architectural_style->label() }}</span>
                    @if ($portfolio->is_ready_to_build)
                        <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-800">Desain siap bangun</span>
                    @endif
                </div>
                <h1 class="mt-3 text-2xl font-bold text-gray-900 md:text-3xl">{{ $portfolio->title }}</h1>
                <p class="mt-4 text-xs uppercase tracking-wide text-gray-400">Estimasi RAB</p>
                <p class="text-3xl font-bold text-blue-700"><x-rupiah :value="$portfolio->estimated_rab" /></p>
                <p class="mt-1 text-sm text-gray-500">±<x-rupiah :value="$portfolio->cost_per_sqm" /> per m² · luas {{ number_format((float) $portfolio->area_sqm, 0, ',', '.') }} m²</p>
                <p class="mt-3 rounded-lg bg-amber-50 px-3 py-2 text-xs text-amber-800">
                    RAB adalah estimasi dan dapat berubah menurut lokasi, material, dan spesifikasi. Angka final ditetapkan setelah survei dan penyesuaian desain.
                </p>
            </div>

            @if ($portfolio->description)
                <div>
                    <h2 class="text-lg font-semibold">Tentang desain ini</h2>
                    <div class="mt-3 leading-relaxed text-gray-700">{!! nl2br(e($portfolio->description)) !!}</div>
                </div>
            @endif

            {{-- Dua jalur pemesanan --}}
            <div class="grid gap-4 md:grid-cols-2">
                <div class="rounded-2xl border {{ $portfolio->is_ready_to_build ? 'border-green-300 bg-green-50' : 'border-gray-200 bg-gray-50' }} p-5">
                    <p class="text-xs font-semibold uppercase tracking-wide {{ $portfolio->is_ready_to_build ? 'text-green-700' : 'text-gray-400' }}">Jalur A · Desain siap bangun</p>
                    @if ($portfolio->is_ready_to_build)
                        <p class="mt-2 text-2xl font-bold text-gray-900"><x-rupiah :value="$portfolio->blueprint_price" /></p>
                        <p class="mt-2 text-sm font-medium text-gray-700">Yang Anda terima:</p>
                        <ul class="mt-1 list-disc space-y-1 pl-5 text-sm text-gray-700">
                            @foreach ($portfolio->blueprint_deliverables ?? [] as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                        <p class="mt-3 text-xs text-gray-600">Berkas final diserahkan lewat tautan Google Drive. Dana baru dilepas ke arsitek setelah Anda menekan "Konfirmasi Penerimaan Berkas".</p>
                    @else
                        <p class="mt-2 text-sm text-gray-500">Portofolio ini belum menyediakan paket gambar jadi.</p>
                    @endif
                </div>
                <div class="rounded-2xl border border-blue-200 bg-blue-50 p-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-blue-700">Jalur B · Custom / survei</p>
                    <p class="mt-2 text-sm text-gray-700">Butuh penyesuaian denah, material, atau survei lokasi? Admin kami menjadwalkan konsultasi dan menyiapkan penawaran. Pengerjaan dimulai setelah Tanda Jadi / Booking Fee dibayar lewat tautan resmi platform.</p>
                </div>
            </div>
        </div>

        <aside class="space-y-6 lg:sticky lg:top-24 lg:self-start">
            <div class="rounded-2xl border border-gray-200 bg-white p-5">
                <p class="text-xs uppercase tracking-wide text-gray-400">Arsitek</p>
                <p class="mt-1 font-semibold text-gray-900">{{ $profile?->business_name ?? $portfolio->architect?->name }}</p>
                @if ($profile?->city)<p class="text-sm text-gray-500">{{ $profile->city }}</p>@endif
                @if ($profile?->license_number)<p class="mt-1 text-sm text-gray-600">No. izin/keanggotaan: {{ $profile->license_number }}</p>@endif
                @if ($profile?->description)<p class="mt-3 text-sm text-gray-700">{{ $profile->description }}</p>@endif
            </div>

            <livewire:concierge-cta source="architect_portfolio" :reference-id="$portfolio->id" />

            <a href="{{ route('how-it-works') }}" class="block rounded-2xl border border-gray-200 bg-white p-4 text-sm font-medium text-blue-700 hover:bg-gray-50">
                Lihat cara kerja dari konsultasi sampai serah terima &rarr;
            </a>
        </aside>
    </div>

    @if ($related->isNotEmpty())
        <section class="mt-14">
            <h2 class="text-xl font-bold">Desain {{ strtolower($portfolio->category->label()) }} lainnya</h2>
            <div class="mt-5 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($related as $item)
                    <x-portfolio-card :portfolio="$item" />
                @endforeach
            </div>
        </section>
    @endif
</div>
</x-layouts.app>
