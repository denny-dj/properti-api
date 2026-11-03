<x-layouts.app title="Cara Kerja">
<div class="mx-auto max-w-4xl px-4 py-10">
    <h1 class="text-3xl font-bold">{{ $content['title'] }}</h1>
    <p class="mt-2 text-gray-500">{{ $content['subtitle'] }}</p>

    <ol class="mt-10 space-y-8">
        @foreach ($content['steps'] as $step)
            <li class="relative flex gap-5">
                <div class="flex flex-col items-center">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-600 font-bold text-white">{{ $step['step'] }}</span>
                    @if (! $loop->last)<span class="mt-2 w-px flex-1 bg-blue-200"></span>@endif
                </div>
                <div class="pb-2">
                    <h2 class="text-xl font-semibold text-gray-900">{{ $step['title'] }}</h2>
                    <p class="mt-1 text-gray-600">{{ $step['description'] }}</p>

                    @if (! empty($step['options']))
                        <div class="mt-4 grid gap-3 md:grid-cols-3">
                            @foreach ($step['options'] as $option)
                                <div class="rounded-xl border border-gray-200 bg-white p-4">
                                    <p class="text-xs font-bold text-blue-700">Pilihan {{ $option['code'] }}</p>
                                    <p class="mt-1 font-medium text-gray-900">{{ $option['title'] }}</p>
                                    <p class="mt-1 text-sm text-gray-500">{{ $option['description'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </li>
        @endforeach
    </ol>

    <div class="mt-10 rounded-2xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-900">{{ $content['payment_note'] }}</div>

    <div class="mt-8 flex flex-wrap gap-3">
        <a href="{{ route('portfolios.index') }}" class="rounded-lg bg-blue-600 px-5 py-2.5 font-medium text-white hover:bg-blue-700">Lihat portofolio</a>
        <a href="{{ route('portfolios.index', ['ready' => 1]) }}" class="rounded-lg border border-blue-600 px-5 py-2.5 font-medium text-blue-700 hover:bg-blue-50">Desain siap bangun</a>
        <a href="{{ route('kpr') }}" class="rounded-lg border border-gray-300 px-5 py-2.5 font-medium text-gray-700 hover:bg-gray-50">Simulasi KPR</a>
    </div>
</div>
</x-layouts.app>
