@props(['placement' => null, 'banners' => null])
@php
    $banners ??= app(\App\Services\PublicContentService::class)->promotions((string) $placement);
@endphp
@if ($banners->isNotEmpty())
    <section class="mx-auto max-w-7xl px-4 pt-8">
        <div class="grid gap-4 {{ $banners->count() > 1 ? 'md:grid-cols-2' : '' }}">
            @foreach ($banners as $banner)
                <a href="{{ route('promo.click', $banner->id) }}" target="_blank" rel="sponsored noopener"
                   class="group relative block overflow-hidden rounded-2xl border border-slate-200 bg-slate-900 shadow-sm transition hover:shadow-md">
                    <img src="{{ $banner->imageUrl() }}" alt="{{ $banner->title }}" loading="lazy"
                         class="h-40 w-full object-cover transition duration-300 group-hover:scale-105 md:h-48" onerror="this.closest('a').remove()">
                    <span class="absolute right-2 top-2 rounded bg-slate-900/70 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-white">Iklan</span>
                </a>
            @endforeach
        </div>
    </section>
@endif