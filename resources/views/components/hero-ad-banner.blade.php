{{--
    Hero beranda. $banner = AdBanner placement top_hero (nullable).
      - ada banner  -> slot iklan premium (Sponsored) dengan 2 CTA
      - tidak ada   -> hero bawaan platform dengan pencarian ringkas
--}}
@props(['banner' => null])

@if (! $banner)
    <x-hero-default />
@else
    @php
        $attrs = $banner->getAttributes();
        $subtitle = $attrs['subtitle'] ?? null;
        $priceLabel = $attrs['price_label'] ?? null;
        $badge = ! empty($attrs['badge_label']) ? $attrs['badge_label'] : 'Sponsored · Promosi Mitra';
    @endphp

    <section class="relative isolate overflow-hidden bg-slate-950 text-white" aria-label="Promosi mitra">
        {{-- Background Image --}}
        <img src="{{ $banner->imageUrl() }}" alt="{{ $banner->title }}" class="absolute inset-0 -z-20 h-full w-full object-cover object-center" onerror="this.remove()">
        
        {{-- Lapisan Gradasi Editorial: Teks tetap tajam di atas background gambar apa pun --}}
        <div class="absolute inset-0 -z-10 bg-gradient-to-r from-slate-950 via-slate-950/90 to-slate-900/35"></div>
        <div class="absolute inset-0 -z-10 bg-gradient-to-t from-slate-950/90 via-transparent to-slate-950/40"></div>

        <div class="mx-auto flex min-h-[28rem] max-w-7xl flex-col justify-center px-4 py-16 sm:px-6 lg:px-8 md:min-h-[34rem] md:py-24">
            <div class="max-w-2xl space-y-6">
                
                {{-- Badges --}}
                <div class="flex flex-wrap items-center gap-2.5">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-400/15 border border-amber-400/30 px-3.5 py-1 text-[11px] font-bold uppercase tracking-wider text-amber-300 backdrop-blur">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                        {{ $badge }}
                    </span>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-800/80 border border-slate-700/60 px-3 py-1 text-xs font-medium text-emerald-400 backdrop-blur">
                        <x-icon name="shield" class="h-3.5 w-3.5" /> Mitra Terverifikasi Concierge
                    </span>
                </div>

                {{-- Headline --}}
                <h1 class="text-3xl font-extrabold leading-[1.15] tracking-tight text-white drop-shadow-sm sm:text-5xl lg:text-6xl">
                    {{ $banner->title }}
                </h1>

                {{-- Subtitle --}}
                @if ($subtitle)
                    <p class="text-base text-slate-300 leading-relaxed sm:text-lg">
                        {{ $subtitle }}
                    </p>
                @endif

                {{-- Price / Offer Callout --}}
                @if ($priceLabel)
                    <div class="inline-flex items-center gap-2 rounded-xl bg-slate-800/80 border border-slate-700/70 px-4 py-2.5 text-sm font-bold text-emerald-400 backdrop-blur shadow-sm">
                        <span class="text-xs font-medium text-slate-400 uppercase tracking-wider">Penawaran:</span>
                        <span>{{ $priceLabel }}</span>
                    </div>
                @endif

                {{-- Action Buttons --}}
                <div class="flex flex-col gap-3.5 pt-2 sm:flex-row sm:items-center">
                    <x-wa-button source="promotion" :reference="$banner->id" label="Konsultasi Unit Ini via Admin" size="lg" class="sm:w-auto shadow-lg shadow-emerald-600/20" />

                    <a href="{{ route('promo.click', $banner->id) }}" target="_blank" rel="sponsored noopener"
                       data-click-endpoint="{{ url('/api/v1/content/promotions/'.$banner->id.'/click') }}"
                       class="inline-flex items-center justify-center gap-2 rounded-xl border border-white/20 bg-white/5 px-6 py-3.5 text-base font-semibold text-white backdrop-blur transition hover:bg-white/10 hover:border-white/30">
                        Lihat Brosur / Detail Proyek <x-icon name="arrow-right" class="h-4 w-4" />
                    </a>
                </div>

                {{-- Escrow Trust Notice --}}
                <p class="text-xs text-slate-400 flex items-center gap-2 pt-1">
                    <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>Transaksi terproteksi rekening penampung resmi. Bebas biaya konsultasi awal dengan tim concierge.</span>
                </p>
            </div>
        </div>
    </section>
@endif