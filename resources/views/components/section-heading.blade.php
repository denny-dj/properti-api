@props(['title', 'subtitle' => null, 'href' => null, 'link' => 'Lihat semua'])
<div class="flex flex-wrap items-end justify-between gap-3">
    <div>
        <h2 class="text-2xl font-bold tracking-tight text-slate-900 md:text-3xl">{{ $title }}</h2>
        @if ($subtitle)<p class="mt-1 max-w-2xl text-sm text-slate-500 md:text-base">{{ $subtitle }}</p>@endif
    </div>
    @if ($href)
        <a href="{{ $href }}" class="inline-flex items-center gap-1 text-sm font-semibold text-blue-600 hover:text-blue-700">
            {{ $link }} <x-icon name="arrow-right" class="h-4 w-4" />
        </a>
    @endif
</div>
