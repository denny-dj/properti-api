<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50/60">
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
<body class="flex min-h-full flex-col text-slate-800 antialiased font-sans">
@php
    $inPortal = request()->routeIs('admin.*', 'partner.*');
    $inAdmin = request()->routeIs('admin.*');
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

<header class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/90 backdrop-blur-md" x-data="{ open: false }">
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
        {{-- Identitas Brand & Lencana Mode Portal --}}
        <div class="flex items-center gap-3.5 shrink-0">
            <a href="{{ $inPortal ? ($inAdmin ? route('admin.dashboard') : route('partner.dashboard')) : route('home') }}"
               class="text-xl font-extrabold tracking-tight text-slate-900 hover:text-blue-700 transition-colors">
                {{ config('app.name') }}
            </a>

            @if ($inPortal)
                <span class="hidden h-4 w-px bg-slate-200 sm:inline-block"></span>
                @if ($inAdmin)
                    <span class="hidden sm:inline-flex items-center gap-1.5 rounded-lg border border-emerald-200/80 bg-emerald-50/80 px-2.5 py-1 text-[10px] font-bold uppercase tracking-widest text-emerald-800">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                        <span>Admin Console</span>
                    </span>
                @else
                    <span class="hidden sm:inline-flex items-center gap-1.5 rounded-lg border border-blue-200/80 bg-blue-50/80 px-2.5 py-1 text-[10px] font-bold uppercase tracking-widest text-blue-800">
                        <span class="h-1.5 w-1.5 rounded-full bg-blue-500"></span>
                        <span>Partner Studio</span>
                    </span>
                @endif
            @endif
        </div>

        {{-- Navigasi Publik (hanya tampil di halaman publik, disembunyikan di dalam Portal Admin/Mitra) --}}
        @if (! $inPortal)
            <nav class="hidden items-center gap-5 lg:gap-6 md:flex">
                @foreach ($nav as [$label, $url, $active])
                    <a href="{{ $url }}"
                       class="relative py-1 text-xs font-bold uppercase tracking-wider transition-colors {{ $active ? 'text-slate-900' : 'text-slate-500 hover:text-slate-900' }}">
                        <span>{{ $label }}</span>
                        @if ($active)
                            <span class="absolute inset-x-0 -bottom-[19px] h-0.5 rounded-full bg-slate-900"></span>
                        @endif
                    </a>
                @endforeach
            </nav>
        @endif

        {{-- Area Pintasan Portal & Dropdown Akun Desktop --}}
        <div class="hidden items-center gap-2.5 md:flex shrink-0">
            @auth
                @php $me = auth()->user(); @endphp

                {{-- Pintasan Pindah Mode (Halaman Publik <-> Panel Admin / Portal Mitra) --}}
                @if ($inPortal)
                    <a href="{{ route('home') }}"
                       class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-slate-50/80 px-3.5 py-1.5 text-xs font-bold text-slate-700 hover:border-slate-900 hover:bg-slate-900 hover:text-white transition-all">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        <span>Halaman Publik</span>
                    </a>
                @elseif ($me->hasRole('admin'))
                    <a href="{{ route('admin.dashboard') }}"
                       class="inline-flex items-center gap-1.5 rounded-xl border border-emerald-200 bg-emerald-50/70 px-3.5 py-1.5 text-xs font-bold text-emerald-800 hover:bg-emerald-100 transition-all">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                        <span>Panel Admin</span>
                    </a>
                @elseif ($me->hasRole('partner'))
                    <a href="{{ route('partner.dashboard') }}"
                       class="inline-flex items-center gap-1.5 rounded-xl border border-blue-200 bg-blue-50/70 px-3.5 py-1.5 text-xs font-bold text-blue-800 hover:bg-blue-100 transition-all">
                        <span class="h-1.5 w-1.5 rounded-full bg-blue-500"></span>
                        <span>Portal Mitra</span>
                    </a>
                @endif

                {{-- Dropdown Profil Pengguna --}}
                <div class="relative" x-data="{ menu: false }" x-on:click.outside="menu = false">
                    <button type="button" x-on:click="menu = !menu"
                            class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white py-1.5 pl-2 pr-3 text-xs font-bold text-slate-700 shadow-2xs hover:bg-slate-50 transition-all cursor-pointer">
                        <img src="{{ $me->avatar_url }}" alt="" class="h-6 w-6 rounded-lg object-cover ring-1 ring-slate-200/70">
                        <span class="max-w-[130px] truncate">{{ $me->name }}</span>
                        <svg class="h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </button>

                    <div x-show="menu" x-cloak
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-60 origin-top-right rounded-2xl border border-slate-200/80 bg-white p-1.5 shadow-xl shadow-slate-900/5">

                        <div class="px-3.5 py-2 border-b border-slate-100 mb-1">
                            <p class="truncate text-xs font-bold text-slate-900">{{ $me->name }}</p>
                            <p class="truncate text-[11px] text-slate-400">{{ $me->email }}</p>
                        </div>

                        @if ($me->hasRole('admin'))
                            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 rounded-xl px-3.5 py-2 text-xs font-bold text-slate-900 hover:bg-slate-50 transition-colors">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                <span>Dashboard admin</span>
                            </a>
                        @endif
                        @if ($me->hasRole('partner'))
                            <a href="{{ route('partner.dashboard') }}" class="flex items-center gap-2 rounded-xl px-3.5 py-2 text-xs font-bold text-slate-900 hover:bg-slate-50 transition-colors">
                                <span class="h-1.5 w-1.5 rounded-full bg-blue-500"></span>
                                <span>Dashboard mitra</span>
                            </a>
                        @endif

                        @if ($me->hasAnyRole(['admin', 'partner']))
                            <a href="{{ route('home') }}" class="flex items-center gap-2 rounded-xl px-3.5 py-2 text-xs font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-colors">
                                <span class="h-1.5 w-1.5 rounded-full bg-slate-300"></span>
                                <span>Lihat halaman publik</span>
                            </a>
                            <div class="my-1 border-t border-slate-100"></div>
                        @endif

                        @if (config('concierge.legacy_ui', true) && $me->hasAnyRole(['user', 'partner']))
                            <a href="{{ route('chat.index') }}" class="block rounded-xl px-3.5 py-2 text-xs font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-colors">Chat</a>
                        @endif
                        @if ($me->hasRole('user'))
                            @if (config('concierge.legacy_ui', true))
                                <a href="{{ route('orders.index') }}" class="block rounded-xl px-3.5 py-2 text-xs font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-colors">Pesanan konsultasi</a>
                            @endif
                            <a href="{{ route('transactions.index') }}" class="block rounded-xl px-3.5 py-2 text-xs font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-colors">Transaksi</a>
                        @endif

                        <a href="{{ route('profile') }}" class="block rounded-xl px-3.5 py-2 text-xs font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-colors">Pengaturan Profil</a>

                        <div class="my-1 border-t border-slate-100"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block w-full rounded-xl px-3.5 py-2 text-left text-xs font-bold text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer">Keluar</button>
                        </form>
                    </div>
                </div>
            @else
                <a href="{{ route('login') }}" class="rounded-xl px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition-colors">Masuk</a>
                <a href="{{ route('register') }}" class="rounded-xl bg-slate-900 px-5 py-2.5 text-xs font-bold text-white shadow-2xs hover:bg-slate-800 transition-all">Daftar</a>
            @endauth
        </div>

        {{-- Tombol Menu Mobile --}}
        <button type="button" class="rounded-xl p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-900 md:hidden transition-colors" x-on:click="open = !open" aria-label="Menu">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
    </div>

    {{-- Drawer Navigasi Mobile --}}
    <div class="border-t border-slate-100 bg-white px-4 pb-4 md:hidden shadow-sm" x-show="open" x-cloak>
        @if (! $inPortal)
            <div class="space-y-1 pt-2">
                @foreach ($nav as [$label, $url, $active])
                    <a href="{{ $url }}" class="block rounded-xl px-3 py-2 text-xs font-bold uppercase tracking-wider {{ $active ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">{{ $label }}</a>
                @endforeach
            </div>
        @endif
        <div class="{{ ! $inPortal ? 'mt-3 border-t border-slate-100 pt-3' : 'pt-2' }} space-y-1">
            @auth
                @if (auth()->user()->hasRole('admin'))
                    <a href="{{ route('admin.dashboard') }}" class="block rounded-xl px-3 py-2 text-xs font-bold text-emerald-700 hover:bg-emerald-50">Dashboard admin</a>
                @endif
                @if (auth()->user()->hasRole('partner'))
                    <a href="{{ route('partner.dashboard') }}" class="block rounded-xl px-3 py-2 text-xs font-bold text-blue-700 hover:bg-blue-50">Dashboard mitra</a>
                @endif
                @if (auth()->user()->hasAnyRole(['admin', 'partner']))
                    <a href="{{ route('home') }}" class="block rounded-xl px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">Lihat halaman publik</a>
                @endif
                @if (config('concierge.legacy_ui', true) && auth()->user()->hasAnyRole(['user', 'partner']))
                    <a href="{{ route('chat.index') }}" class="block rounded-xl px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">Chat</a>
                @endif
                @if (auth()->user()->hasRole('user'))
                    @if (config('concierge.legacy_ui', true))
                        <a href="{{ route('orders.index') }}" class="block rounded-xl px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">Pesanan konsultasi</a>
                    @endif
                    <a href="{{ route('transactions.index') }}" class="block rounded-xl px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">Transaksi</a>
                @endif
                <a href="{{ route('profile') }}" class="block rounded-xl px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">Pengaturan Profil</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="mt-1 block w-full rounded-xl px-3 py-2 text-left text-xs font-bold text-rose-600 hover:bg-rose-50">Keluar ({{ auth()->user()->name }})</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="block rounded-xl px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50">Masuk</a>
                <a href="{{ route('register') }}" class="mt-1 block rounded-xl bg-slate-900 px-3 py-2.5 text-center text-xs font-bold text-white shadow-2xs hover:bg-slate-800">Daftar</a>
            @endauth
        </div>
    </div>
</header>

@auth
    @if (! auth()->user()->hasVerifiedEmail() && ! request()->routeIs('verification.*'))
        <div class="border-b border-amber-200/80 bg-amber-50/90 px-4 py-2.5 text-center text-xs font-semibold text-amber-900 backdrop-blur-sm">
            Email Anda belum diverifikasi. <a href="{{ route('verification.notice') }}" class="font-bold underline hover:text-amber-950">Verifikasi sekarang</a> agar bisa memesan, chat, dan membayar.
        </div>
    @endif
@endauth

@if (session('status'))
    <div class="border-b border-emerald-200/80 bg-emerald-50/90 px-4 py-2.5 text-center text-xs font-bold text-emerald-800 backdrop-blur-sm">{{ session('status') }}</div>
@endif

<main class="flex-1">{{ $slot }}</main>

@if ($inPortal)
    {{-- Footer Minimalis Workspace Admin & Mitra --}}
    <footer class="mt-12 border-t border-slate-200/80 bg-white/80">
        <div class="mx-auto flex max-w-7xl flex-col gap-2 px-4 py-5 sm:px-6 lg:px-8 text-[11px] font-medium text-slate-400 sm:flex-row sm:items-center sm:justify-between">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }} &middot; {{ $inAdmin ? 'Pusat Kendali Administrator' : 'Portal Operasional Mitra' }}</p>
            <a href="{{ route('home') }}" class="font-semibold text-slate-600 hover:text-slate-900 transition-colors">Buka Beranda Publik &rarr;</a>
        </div>
    </footer>
@else
    {{-- Footer Publik Architectural Editorial --}}
    <footer class="mt-16 border-t border-slate-200/80 bg-white">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="flex flex-col justify-between gap-8 md:flex-row md:items-center">
                <div class="space-y-1.5 max-w-md">
                    <span class="text-lg font-extrabold tracking-tight text-slate-900">{{ config('app.name') }}</span>
                    <p class="text-xs leading-relaxed text-slate-500">
                        Jual beli properti &amp; konsultasi arsitektur terkurasi dengan sistem pembayaran aman.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('register.partner') }}"
                       class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-xs font-bold text-slate-800 shadow-2xs hover:border-slate-300 hover:bg-white hover:text-slate-900 transition-all">
                        <span>Daftar sebagai mitra (developer, agen, arsitek)</span>
                        <span aria-hidden="true">&rarr;</span>
                    </a>
                </div>
            </div>

            <div class="mt-8 flex flex-col gap-2 border-t border-slate-100 pt-6 text-[11px] font-medium text-slate-400 sm:flex-row sm:items-center sm:justify-between">
                <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Seluruh hak cipta dilindungi.</p>
                <div class="flex flex-wrap items-center gap-4">
                    <a href="{{ route('properties.index') }}" class="hover:text-slate-700 transition-colors">Katalog Properti</a>
                    <a href="{{ route('portfolios.index') }}" class="hover:text-slate-700 transition-colors">Portofolio Arsitek</a>
                    <a href="{{ route('how-it-works') }}" class="hover:text-slate-700 transition-colors">Cara Kerja</a>
                    <a href="{{ route('kpr') }}" class="hover:text-slate-700 transition-colors">Simulasi KPR</a>
                </div>
            </div>
        </div>
    </footer>
@endif

@livewireScripts
</body>
</html>