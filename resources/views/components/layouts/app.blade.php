<!DOCTYPE html>
<html lang="id" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title.' · ' : '' }}{{ config('app.name') }}</title>

    {{-- Tailwind via CDN: cukup untuk pengembangan. Ganti dengan build Vite sebelum produksi. --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <style>[x-cloak]{display:none!important}</style>

    {{-- Pemuat Google Maps JavaScript API (sekali saja, hanya bila key tersedia) --}}
    <script>
        window.GMAPS_KEY = @js(config('services.google_maps.browser_key'));
        window.loadGoogleMaps = function () {
            if (window.google && window.google.maps) { return Promise.resolve(); }
            if (window._gmapsPromise) { return window._gmapsPromise; }
            window._gmapsPromise = new Promise(function (resolve, reject) {
                if (!window.GMAPS_KEY) { reject(new Error('no-key')); return; }
                window.__gmapsReady = resolve;
                var s = document.createElement('script');
                s.src = 'https://maps.googleapis.com/maps/api/js?key=' + encodeURIComponent(window.GMAPS_KEY) + '&callback=__gmapsReady&loading=async';
                s.async = true;
                s.onerror = reject;
                document.head.appendChild(s);
            });
            return window._gmapsPromise;
        };
    </script>
    @livewireStyles
</head>
<body class="flex min-h-full flex-col text-gray-800 antialiased">
@php
    $nav = [
        ['Beranda', route('home'), request()->routeIs('home')],
        ['Cari Properti', route('properties.index'), request()->routeIs('properties.*')],
        ['Peta', route('map'), request()->routeIs('map')],
        ['Arsitek', route('architects.index'), request()->routeIs('architects.*')],
        ['Simulasi KPR', route('kpr'), request()->routeIs('kpr')],
    ];
@endphp

<header class="sticky top-0 z-40 border-b border-gray-200 bg-white/90 backdrop-blur" x-data="{ open: false }">
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4">
        <a href="{{ route('home') }}" class="text-xl font-bold text-blue-700">{{ config('app.name') }}</a>

        <nav class="hidden items-center gap-6 md:flex">
            @foreach ($nav as [$label, $url, $active])
                <a href="{{ $url }}" class="text-sm font-medium {{ $active ? 'text-blue-700' : 'text-gray-600 hover:text-blue-700' }}">{{ $label }}</a>
            @endforeach
        </nav>

        <div class="hidden items-center gap-3 md:flex">
            @auth
                @php $me = auth()->user(); @endphp
                <div class="relative" x-data="{ menu: false }" x-on:click.outside="menu = false">
                    <button type="button" x-on:click="menu = !menu" class="flex items-center gap-1 text-sm font-medium text-gray-700 hover:text-blue-700">
                        {{ $me->name }}
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="menu" x-cloak class="absolute right-0 mt-2 w-52 rounded-xl border border-gray-200 bg-white py-1 shadow-lg">
                        @if ($me->hasAnyRole(['user', 'partner']))
                            <a href="{{ route('chat.index') }}" class="block px-4 py-2 text-sm hover:bg-gray-50">Chat</a>
                        @endif
                        @if ($me->hasRole('user'))
                            <a href="{{ route('orders.index') }}" class="block px-4 py-2 text-sm hover:bg-gray-50">Pesanan konsultasi</a>
                            <a href="{{ route('transactions.index') }}" class="block px-4 py-2 text-sm hover:bg-gray-50">Transaksi</a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block w-full px-4 py-2 text-left text-sm hover:bg-gray-50">Keluar</button>
                        </form>
                    </div>
                </div>
            @else
                <a href="{{ route('login') }}" class="text-sm font-medium text-gray-700 hover:text-blue-700">Masuk</a>
                <a href="{{ route('register') }}" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">Daftar</a>
            @endauth
        </div>

        <button type="button" class="md:hidden" x-on:click="open = !open" aria-label="Menu">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
    </div>

    <div class="border-t border-gray-100 px-4 pb-4 md:hidden" x-show="open" x-cloak>
        @foreach ($nav as [$label, $url, $active])
            <a href="{{ $url }}" class="block py-2 text-sm font-medium {{ $active ? 'text-blue-700' : 'text-gray-700' }}">{{ $label }}</a>
        @endforeach
        <div class="mt-2 border-t border-gray-100 pt-3">
            @auth
                @if (auth()->user()->hasAnyRole(['user', 'partner']))
                    <a href="{{ route('chat.index') }}" class="block py-2 text-sm font-medium text-gray-700">Chat</a>
                @endif
                @if (auth()->user()->hasRole('user'))
                    <a href="{{ route('orders.index') }}" class="block py-2 text-sm font-medium text-gray-700">Pesanan konsultasi</a>
                    <a href="{{ route('transactions.index') }}" class="block py-2 text-sm font-medium text-gray-700">Transaksi</a>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="py-2 text-sm font-medium text-gray-700">Keluar ({{ auth()->user()->name }})</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="mr-4 text-sm font-medium text-gray-700">Masuk</a>
                <a href="{{ route('register') }}" class="text-sm font-medium text-blue-700">Daftar</a>
            @endauth
        </div>
    </div>
</header>

@if (session('status'))
    <div class="border-b border-green-200 bg-green-50 px-4 py-3 text-center text-sm text-green-800">{{ session('status') }}</div>
@endif

<main class="flex-1">{{ $slot }}</main>

<footer class="mt-16 border-t border-gray-200 bg-white">
    <div class="mx-auto flex max-w-7xl flex-col gap-2 px-4 py-8 text-sm text-gray-500 md:flex-row md:items-center md:justify-between">
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Jual beli properti &amp; konsultasi arsitektur.</p>
        <p><a href="{{ route('register.partner') }}" class="font-medium text-blue-700 hover:underline">Daftar sebagai mitra (developer, agen, arsitek)</a></p>
    </div>
</footer>

@livewireScripts
</body>
</html>
