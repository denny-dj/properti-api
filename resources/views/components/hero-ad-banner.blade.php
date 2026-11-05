{{--
    Hero beranda. $banner = AdBanner placement top_hero (nullable).
      - ada banner  -> slot iklan premium (Sponsored) dengan 2 CTA
      - tidak ada   -> hero bawaan platform dengan pencarian ringkas
    Materi tambahan (subtitle, price_label, badge_label) opsional; dibaca aman meski kolomnya belum dimigrasi.
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
    <section class="relative isolate overflow-hidden bg-slate-900 text-white" aria-label="Promosi mitra">
        <img src="{{ $banner->imageUrl() }}" alt="" class="absolute inset-0 -z-20 h-full w-full object-cover" onerror="this.remove()">
        {{-- gradasi gelap agar teks tetap tajam di atas foto apa pun --}}
        <div class="absolute inset-0 -z-10 bg-gradient-to-r from-slate-900 via-slate-900/85 to-slate-900/25"></div>
        <div class="absolute inset-0 -z-10 bg-gradient-to-t from-slate-900/80 via-transparent to-slate-900/30"></div>

        <div class="mx-auto flex min-h-[26rem] max-w-7xl flex-col justify-center px-4 py-14 md:min-h-[32rem] md:py-20">
            <div class="max-w-2xl">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center rounded-full bg-amber-400 px-3 py-1 text-[11px] font-bold uppercase tracking-wider text-slate-900">{{ $badge }}</span>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1 text-xs font-medium text-emerald-300 ring-1 ring-white/20">
                        <x-icon name="shield" class="h-4 w-4" /> Mitra terverifikasi
                    </span>
                </div>

                <h1 class="mt-5 text-3xl font-bold leading-tight tracking-tight drop-shadow-sm md:text-5xl">{{ $banner->title }}</h1>

                @if ($subtitle)
                    <p class="mt-4 max-w-xl text-base text-slate-200 md:text-lg">{{ $subtitle }}</p>
                @endif

                @if ($priceLabel)
                    <p class="mt-5 inline-flex items-center rounded-xl bg-white/10 px-4 py-2 text-sm font-semibold text-white ring-1 ring-white/20 backdrop-blur">{{ $priceLabel }}</p>
                @endif

                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <x-wa-button source="promotion" :reference="$banner->id" label="Konsultasi Unit Ini via Admin" size="lg" class="sm:w-auto" />

                    {{-- Klik dicatat server-side (PublicContentService::trackClick, sama dengan POST /api/v1/content/promotions/{id}/click) lalu diteruskan ke target_url --}}
                    <a href="{{ route('promo.click', $banner->id) }}" target="_blank" rel="sponsored noopener"
                       data-click-endpoint="{{ url('/api/v1/content/promotions/'.$banner->id.'/click') }}"
                       class="inline-flex items-center justify-center gap-2 rounded-xl border border-white/40 px-6 py-3.5 text-base font-semibold text-white transition hover:bg-white/10">
                        Lihat Brosur / Detail Proyek <x-icon name="arrow-right" class="h-4 w-4" />
                    </a>
                </div>

                <p class="mt-4 text-xs text-slate-300">Pembayaran hanya melalui tautan resmi platform. Admin kami mendampingi dari konsultasi sampai serah terima.</p>
            </div>
        </div>
    </section>
@endif
