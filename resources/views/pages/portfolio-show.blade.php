<x-layouts.app :title="$portfolio->title">
@php
    $images = $portfolio->images;
    $profile = $portfolio->architect?->partnerProfile;
@endphp

<div class="bg-slate-50/50 pb-20">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        
        {{-- Breadcrumb --}}
        <nav class="mb-6 flex items-center gap-2 text-xs font-medium text-slate-400">
            <a href="{{ route('home') }}" class="hover:text-blue-600 transition-colors">Beranda</a>
            <span>/</span>
            <a href="{{ route('portfolios.index') }}" class="hover:text-blue-600 transition-colors">Portofolio</a>
            <span>/</span>
            <span class="text-slate-700">{{ $portfolio->category->label() }}</span>
        </nav>

        {{-- Header Desain --}}
        <div class="mb-8 flex flex-col justify-between gap-4 md:flex-row md:items-end">
            <div class="space-y-2">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-md bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">
                        {{ $portfolio->category->label() }}
                    </span>
                    <span class="rounded-md bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">
                        {{ $portfolio->architectural_style->label() }}
                    </span>
                    @if ($portfolio->is_ready_to_build)
                        <span class="inline-flex items-center gap-1 rounded-md bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                            <x-icon name="blueprint" class="h-3.5 w-3.5 text-emerald-600" /> Blueprint Ready
                        </span>
                    @endif
                </div>
                <h1 class="text-2xl sm:text-4xl font-extrabold tracking-tight text-slate-900">{{ $portfolio->title }}</h1>
            </div>
            
            <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm md:text-right shrink-0">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Estimasi RAB Fisik</p>
                <p class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight"><x-rupiah :value="$portfolio->estimated_rab" /></p>
                <p class="text-xs text-slate-500 mt-0.5">±<x-rupiah :value="$portfolio->cost_per_sqm" />/m² · Luas Bangunan {{ number_format((float) $portfolio->area_sqm, 0, ',', '.') }} m²</p>
            </div>
        </div>

        <div class="grid gap-10 lg:grid-cols-12">
            
            {{-- Konten Utama (8 Kolom) --}}
            <div class="space-y-10 lg:col-span-8">
                
                {{-- Galeri Foto Portofolio --}}
                <div x-data="{ active: 0 }" class="overflow-hidden rounded-3xl border border-slate-200/80 bg-white p-2 shadow-sm">
                    <div class="relative aspect-[16/10] overflow-hidden rounded-2xl bg-slate-100">
                        @foreach ($images as $i => $image)
                            <img x-show="active === {{ $i }}" @if ($i > 0) x-cloak @endif
                                 src="{{ $image->url() }}" alt="{{ $portfolio->title }} - foto {{ $i + 1 }}"
                                 class="absolute inset-0 h-full w-full object-cover transition-all duration-300" onerror="this.remove()">
                        @endforeach
                        @if ($images->isEmpty())
                            <div class="flex h-full items-center justify-center text-slate-400 font-medium">Belum ada foto portofolio</div>
                        @endif
                    </div>
                    @if ($images->count() > 1)
                        <div class="mt-2.5 flex gap-2.5 overflow-x-auto pb-1">
                            @foreach ($images as $i => $image)
                                <button type="button" x-on:click="active = {{ $i }}" 
                                        class="h-20 w-28 shrink-0 overflow-hidden rounded-xl border-2 transition-all"
                                        x-bind:class="active === {{ $i }} ? 'border-slate-900 opacity-100 shadow-md' : 'border-transparent opacity-60 hover:opacity-100'">
                                    <img src="{{ $image->url() }}" alt="" class="h-full w-full object-cover" onerror="this.remove()">
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Catatan RAB Transparan --}}
                <div class="rounded-2xl border border-amber-200/80 bg-amber-50/70 p-4 text-xs text-amber-900 flex items-start gap-3">
                    <svg class="h-5 w-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="leading-relaxed">
                        <strong>Catatan RAB:</strong> Estimasi di atas merupakan perkiraan acuan awal. Angka realisasi konstruksi dapat disesuaikan menurut kontur tanah lokasi Anda, pilihan material akhir, dan detail spesifikasi saat survei lapangan.
                    </p>
                </div>

                {{-- Deskripsi Karya Arsitektur --}}
                @if ($portfolio->description)
                    <div>
                        <h2 class="text-lg font-bold text-slate-900 mb-3">Tentang Konsep Desain</h2>
                        <div class="rounded-3xl border border-slate-200/80 bg-white p-6 sm:p-8 shadow-sm">
                            <div class="leading-relaxed text-slate-600 text-sm sm:text-base space-y-4">
                                {!! nl2br(e($portfolio->description)) !!}
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Dua Opsi Eksekusi: Jalur A (Blueprint) & Jalur B (Custom) --}}
                <div>
                    <h2 class="text-lg font-bold text-slate-900 mb-3">Opsi Pengadaan Proyek</h2>
                    <div class="grid gap-6 md:grid-cols-2">
                        
                        {{-- Jalur A --}}
                        <div class="rounded-3xl border {{ $portfolio->is_ready_to_build ? 'border-emerald-200 bg-emerald-50/40' : 'border-slate-200 bg-slate-50/50' }} p-6 flex flex-col justify-between shadow-sm">
                            <div>
                                <span class="text-[11px] font-bold uppercase tracking-wider {{ $portfolio->is_ready_to_build ? 'text-emerald-700' : 'text-slate-400' }}">
                                    Jalur A · Cetak Biru Siap Bangun
                                </span>
                                @if ($portfolio->is_ready_to_build)
                                    <div class="mt-3">
                                        <p class="text-2xl font-extrabold text-slate-900"><x-rupiah :value="$portfolio->blueprint_price" /></p>
                                        <p class="text-xs text-slate-500 mt-1">Paket gambar kerja lengkap siap konstruksi</p>
                                    </div>
                                    <div class="mt-4 pt-4 border-t border-emerald-200/60">
                                        <p class="text-xs font-bold text-slate-800 mb-2">Dokumen yang Anda dapatkan:</p>
                                        <ul class="space-y-1.5 text-xs text-slate-600">
                                            @foreach ($portfolio->blueprint_deliverables ?? [] as $item)
                                                <li class="flex items-start gap-2">
                                                    <svg class="h-4 w-4 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                    <span>{{ $item }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    <p class="mt-4 text-[11px] text-slate-500 bg-white/80 p-3 rounded-xl border border-emerald-100">
                                        Berkas diserahkan digital. Dana baru dicairkan ke arsitek setelah Anda menekan tombol konfirmasi penerimaan berkas.
                                    </p>
                                @else
                                    <p class="mt-3 text-sm text-slate-500">Karya ini dipamerkan sebagai inspirasi custom dan belum menyediakan paket cetak biru siap pakai.</p>
                                @endif
                            </div>
                        </div>

                        {{-- Jalur B --}}
                        <div class="rounded-3xl border border-blue-200 bg-blue-50/40 p-6 flex flex-col justify-between shadow-sm">
                            <div>
                                <span class="text-[11px] font-bold uppercase tracking-wider text-blue-700">
                                    Jalur B · Modifikasi Custom & Survei
                                </span>
                                <h3 class="mt-3 text-lg font-bold text-slate-900">Penyesuaian Denah & Lahan</h3>
                                <p class="mt-2 text-xs text-slate-600 leading-relaxed">
                                    Ingin menerapkan gaya arsitektur ini ke lahan Anda dengan jumlah kamar atau orientasi berbeda? Tim admin concierge memfasilitasi sesi konsultasi awal, survei koordinat, dan perumusan penawaran SPK resmi.
                                </p>
                            </div>
                            <div class="mt-4 pt-4 border-t border-blue-200/60 text-xs text-blue-900 font-medium">
                                Pengerjaan diawali komitmen tanda jadi aman via escrow platform.
                            </div>
                        </div>

                    </div>
                </div>

            </div>

            {{-- Sidebar Arsitek (4 Kolom) --}}
            <aside class="space-y-6 lg:col-span-4 lg:sticky lg:top-24 lg:self-start">
                
                <div class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Studio / Arsitek Perancang</p>
                    <div class="mt-3 flex items-center gap-3">
                        <div class="h-12 w-12 rounded-2xl bg-slate-900 flex items-center justify-center font-bold text-white text-lg">
                            {{ substr($profile?->business_name ?? $portfolio->architect?->name ?? 'A', 0, 1) }}
                        </div>
                        <div>
                            <p class="font-bold text-slate-900">{{ $profile?->business_name ?? $portfolio->architect?->name }}</p>
                            @if ($profile?->city)<p class="text-xs text-slate-500">{{ $profile->city }}</p>@endif
                        </div>
                    </div>
                    @if ($profile?->license_number)
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center gap-1.5 text-xs text-slate-600">
                            <x-icon name="shield" class="h-3.5 w-3.5 text-blue-600" />
                            <span>Izin / IAI: <strong>{{ $profile->license_number }}</strong></span>
                        </div>
                    @endif
                    @if ($profile?->description)
                        <p class="mt-3 text-xs text-slate-500 leading-relaxed border-t border-slate-100 pt-3">{{ $profile->description }}</p>
                    @endif
                </div>

                {{-- Livewire CTA Konsultasi Portofolio --}}
                <div class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm">
                    <livewire:concierge-cta source="architect_portfolio" :reference-id="$portfolio->id" />
                </div>

                <a href="{{ route('how-it-works') }}" class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white p-4 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:border-blue-200 transition-colors shadow-sm">
                    <span>Pelajari Alur Transaksi & Escrow Platform</span>
                    <span>&rarr;</span>
                </a>

            </aside>
        </div>

        {{-- Desain Serupa --}}
        @if ($related->isNotEmpty())
            <section class="mt-20 border-t border-slate-200 pt-12">
                <div class="mb-6">
                    <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Karya {{ $portfolio->category->label() }} Lainnya</h2>
                    <p class="text-sm text-slate-500 mt-1">Eksplorasi portofolio desain terverifikasi dalam kategori yang sama.</p>
                </div>
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($related as $item)
                        <x-portfolio-card :portfolio="$item" />
                    @endforeach
                </div>
            </section>
        @endif

    </div>
</div>
</x-layouts.app>