@props(['placement'])
@php $banners = app(\App\Services\PublicContentService::class)->promotions($placement); @endphp
@if ($banners->isNotEmpty())
    <section class="mx-auto max-w-7xl px-4 pt-6">
        <div class="grid gap-4 {{ $banners->count() > 1 ? 'md:grid-cols-2' : '' }}">
            @foreach ($banners as $banner)
                <a href="{{ route('promo.click', $banner->id) }}" target="_blank" rel="sponsored noopener"
                   class="relative block overflow-hidden rounded-2xl border border-gray-200 bg-gray-100">
                    <img src="{{ $banner->imageUrl() }}" alt="{{ $banner->title }}" loading="lazy" class="h-40 w-full object-cover md:h-48" onerror="this.closest('a').remove()">
                    <span class="absolute right-2 top-2 rounded bg-black/50 px-2 py-0.5 text-[10px] uppercase tracking-wide text-white">Iklan</span>
                </a>
            @endforeach
        </div>
    </section>
@endif
