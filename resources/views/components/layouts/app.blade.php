<!DOCTYPE html>
<html lang="id" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title.' · ' : '' }}{{ config('app.name') }}</title>

    {{-- Font: Plus Jakarta Sans (tailwind.config.js menjadikannya font-sans bawaan) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

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
        ['Portofolio', route('portfolios.index'), request()->routeIs('portfolios.*')],
        ['Cara Kerja', route('how-it-works'), request()->routeIs('how-it-works')],
        ...(config('concierge.legacy_ui', true) ? [['Arsitek (konsultasi)', route('architects.index'), request()->routeIs('architects.*')]] : []),
        ['Simulasi KPR', route('kpr'), request()->routeIs('kpr')],
    ];
@endphp

<header class="sticky top-0 z-40 border-b border-gray-200 bg-white/90 backdrop-blur" x-data="{ open: false }">
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4">
        <a href="{{ route('home') }}" class="text-xl font-bold text-blue-700">{{ config('app.name') }}</a>

        {{-- <nav class="hidden items-center gap-6 md:flex">
            @foreach ($nav as [$label, $url, $active])
                <a href="{{ $url }}" class="text-sm font-medium {{ $active ? 'text-blue-700' : 'text-gray-600 hover:text-blue-700' }}">{{ $label }}</a>
            @endforeach
        </nav> --}}
    {{-- Navigasi Desktop Tengah --}}
        <nav class="hidden items-center gap-6 md:flex">
            @if (request()->routeIs('admin.*'))
                {{-- Mode Khusus Administrator --}}
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-700 border border-slate-200">
                        Panel Administrator
                    </span>
                    <a href="{{ route('home') }}" target="_blank" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-900 transition-colors">
                        <span>Lihat Web Publik</span>
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                </div>
            @elseif (request()->routeIs('partner.*'))
                {{-- Mode Khusus Portal Mitra --}}
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-700 border border-slate-200">
                        Portal Mitra
                    </span>
                    <a href="{{ route('home') }}" target="_blank" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-900 transition-colors">
                        <span>Lihat Web Publik</span>
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                </div>
            @else
                {{-- Mode Pengunjung / Pembeli Properti --}}
                @foreach ($nav as [$label, $url, $active])
                    <a href="{{ $url }}" class="text-sm font-medium {{ $active ? 'text-blue-700' : 'text-gray-600 hover:text-blue-700' }}">{{ $label }}</a>
                @endforeach
            @endif
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
                        @if ($me->hasRole('admin'))
                            <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 text-sm font-medium text-blue-700 hover:bg-gray-50">Dashboard admin</a>
                        @endif
                        @if ($me->hasRole('partner'))
                            <a href="{{ route('partner.dashboard') }}" class="block px-4 py-2 text-sm font-medium text-blue-700 hover:bg-gray-50">Dashboard mitra</a>
                        @endif
                        @if (config('concierge.legacy_ui', true) && $me->hasAnyRole(['user', 'partner']))
                            <a href="{{ route('chat.index') }}" class="block px-4 py-2 text-sm hover:bg-gray-50">Chat</a>
                        @endif
                        @if ($me->hasRole('user'))
                            @if (config('concierge.legacy_ui', true))
                                <a href="{{ route('orders.index') }}" class="block px-4 py-2 text-sm hover:bg-gray-50">Pesanan konsultasi</a>
                            @endif
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
                @if (auth()->user()->hasRole('admin'))
                    <a href="{{ route('admin.dashboard') }}" class="block py-2 text-sm font-medium text-blue-700">Dashboard admin</a>
                @endif
                @if (auth()->user()->hasRole('partner'))
                    <a href="{{ route('partner.dashboard') }}" class="block py-2 text-sm font-medium text-blue-700">Dashboard mitra</a>
                @endif
                @if (config('concierge.legacy_ui', true) && auth()->user()->hasAnyRole(['user', 'partner']))
                    <a href="{{ route('chat.index') }}" class="block py-2 text-sm font-medium text-gray-700">Chat</a>
                @endif
                @if (auth()->user()->hasRole('user'))
                    @if (config('concierge.legacy_ui', true))
                        <a href="{{ route('orders.index') }}" class="block py-2 text-sm font-medium text-gray-700">Pesanan konsultasi</a>
                    @endif
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

@auth
    @if (! auth()->user()->hasVerifiedEmail() && ! request()->routeIs('verification.*'))
        <div class="border-b border-amber-200 bg-amber-50 px-4 py-2 text-center text-sm text-amber-900">
            Email Anda belum diverifikasi. <a href="{{ route('verification.notice') }}" class="font-medium underline">Verifikasi sekarang</a> agar bisa memesan, chat, dan membayar.
        </div>
    @endif
@endauth

@if (session('status'))
    <div class="border-b border-green-200 bg-green-50 px-4 py-3 text-center text-sm text-green-800">{{ session('status') }}</div>
@endif

<main class="flex-1">{{ $slot }}</main>

{{-- FOOTER CONCIERGE PLATFORM --}}
<footer class="mt-20 bg-slate-950 pt-16 pb-8 border-t border-slate-900 text-slate-400">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 mb-12">
            
            {{-- Kolom 1: Brand & Kredibilitas --}}
            <div class="lg:col-span-1 space-y-4">
                <a href="{{ url('/') }}" class="text-xl font-extrabold text-white flex items-center gap-2">
                    <span class="w-8 h-8 rounded-lg bg-emerald-500 flex items-center justify-center text-white shadow-md shadow-emerald-500/20">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                    </span>
                    {{ config('app.name', 'Concierge') }}<span class="text-emerald-500">.</span>
                </a>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Platform kurasi properti dan katalog cetak biru arsitektur. Seluruh transaksi didampingi perwakilan concierge dengan proteksi rekening bersama resmi.
                </p>
            </div>

            {{-- Kolom 2: Layanan --}}
            <div>
                <h4 class="text-sm font-semibold text-white mb-4 tracking-wide">Layanan Platform</h4>
                <ul class="space-y-2.5 text-xs">
                    <li><a href="{{ route('properties.index') }}" class="hover:text-emerald-400 transition-colors">Cari Unit Properti</a></li>
                    <li><a href="{{ route('portfolios.index') }}" class="hover:text-emerald-400 transition-colors">Katalog Desain Arsitek</a></li>
                    <li><a href="{{ route('how-it-works') }}" class="hover:text-emerald-400 transition-colors">Alur Transaksi & Escrow</a></li>
                    <li><a href="{{ route('kpr') }}" class="hover:text-emerald-400 transition-colors">Simulasi KPR Bank Rekanan</a></li>
                </ul>
            </div>

            {{-- Kolom 3: Kemitraan Resmi --}}
            <div>
                <h4 class="text-sm font-semibold text-white mb-4 tracking-wide">Kemitraan</h4>
                <ul class="space-y-2.5 text-xs">
                    <li>
                        <a href="{{ route('register.partner') }}" class="inline-flex items-center gap-1.5 font-medium text-emerald-400 hover:text-emerald-300 transition-colors">
                            Daftar Mitra (Developer & Arsitek)
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </li>
                    <li class="text-slate-500">Penyaringan ketat legalitas dan portofolio mitra.</li>
                </ul>
            </div>

            {{-- Kolom 4: Hubungi Admin --}}
            <div>
                <h4 class="text-sm font-semibold text-white mb-4 tracking-wide">Bantuan Concierge</h4>
                <div class="space-y-3 text-xs">
                    <p class="text-slate-400 flex items-start gap-2">
                        <svg class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Jakarta, Indonesia
                    </p>
                    <a href="https://wa.me/6281234567890?text={{ urlencode('Halo Admin Concierge, saya butuh pendampingan transaksi properti/desain') }}" target="_blank" class="inline-flex items-center gap-2 px-3.5 py-2 bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 rounded-lg font-medium hover:bg-emerald-500 hover:text-white transition-all">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 00-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                        </svg>
                        Hubungi Concierge
                    </a>
                </div>
            </div>

        </div>

        {{-- Baris Copyright Bawah --}}
        <div class="pt-8 border-t border-slate-900 text-xs text-slate-500">
            &copy; {{ date('Y') }} {{ config('app.name', 'Concierge') }}. Platform Kurasi Properti & Portofolio Arsitektur.
        </div>

    </div>
</footer>

@livewireScripts
</body>
</html>
