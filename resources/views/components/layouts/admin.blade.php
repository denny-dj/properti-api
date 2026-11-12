@props(['title' => 'Dashboard Admin'])
@php
    $admin = auth()->user();
    $badges = app(\App\Services\AdminStatsService::class)->badges();

    $items = [['Ringkasan', route('admin.dashboard'), ['admin.dashboard'], 0, 'grid']];

    if ($admin->can('partners.verify')) {
        $items[] = ['Verifikasi mitra', route('admin.partners'), ['admin.partners'], $badges['partners'], 'badge-check'];
    }
    if ($admin->can('properties.verify')) {
        $items[] = ['Moderasi properti', route('admin.properties'), ['admin.properties'], $badges['properties'], 'building'];
    }
    if ($admin->can('users.manage')) {
        $items[] = ['Pengguna', route('admin.users'), ['admin.users'], 0, 'users'];
    }
    if (config('concierge.legacy_ui', true) && $admin->can('bookings.view')) {
        $items[] = ['Pesanan konsultasi', route('admin.bookings'), ['admin.bookings'], 0, 'calendar'];
    }
    if ($admin->can('transactions.view')) {
        $items[] = ['Transaksi', route('admin.transactions'), ['admin.transactions'], $badges['transactions'], 'receipt'];
        $items[] = ['Pendapatan', route('admin.revenue'), ['admin.revenue'], 0, 'chart'];
    }
    if ($admin->can('concierge.manage')) {
        $items[] = ['Prospek', route('admin.leads'), ['admin.leads'], $badges['leads'] ?? 0, 'inbox'];
        $items[] = ['Invoice cepat', route('admin.invoices'), ['admin.invoices'], 0, 'bolt'];
    }
    if ($admin->can('payouts.manage')) {
        $items[] = ['Pencairan mitra', route('admin.payouts'), ['admin.payouts'], 0, 'banknotes'];
    }

    $items[] = ['Profil saya', route('profile'), ['profile'], 0, 'user'];
@endphp
<x-layouts.app :title="$title">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8 lg:flex lg:items-start lg:gap-8">
        <aside class="mb-6 w-full shrink-0 lg:mb-0 lg:w-64">
            <div class="sticky top-24 rounded-3xl border border-slate-200/80 bg-white p-4 shadow-sm space-y-4">
                {{-- Header Kartu Sidebar Admin --}}
                <div class="flex items-center gap-3 border-b border-slate-100 pb-3.5 px-1">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-slate-900 text-white shadow-2xs">
                        <svg class="h-4 w-4 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Tingkat Sistem</p>
                        <p class="truncate text-xs font-extrabold text-slate-900">Administrator</p>
                    </div>
                </div>

                {{-- Navigasi Menu Admin --}}
                <nav class="flex gap-1.5 overflow-x-auto pb-1 lg:flex-col lg:pb-0">
                    @foreach ($items as [$label, $url, $patterns, $count, $icon])
                        @php $isActive = request()->routeIs(...$patterns); @endphp
                        <a href="{{ $url }}"
                           class="group flex items-center justify-between gap-2.5 whitespace-nowrap rounded-xl px-3 py-2.5 text-xs font-bold transition-all {{ $isActive ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                            <span class="flex items-center gap-2.5 min-w-0">
                                <svg class="h-4 w-4 shrink-0 {{ $isActive ? 'text-emerald-400' : 'text-slate-400 group-hover:text-slate-700' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    @switch($icon)
                                        @case('grid')
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                                            @break
                                        @case('badge-check')
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                                            @break
                                        @case('building')
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                            @break
                                        @case('users')
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                            @break
                                        @case('calendar')
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            @break
                                        @case('receipt')
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                                            @break
                                        @case('chart')
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                            @break
                                        @case('inbox')
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                            @break
                                        @case('bolt')
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                            @break
                                        @case('banknotes')
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                            @break
                                        @default
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    @endswitch
                                </svg>
                                <span class="truncate">{{ $label }}</span>
                            </span>
                            @if ($count > 0)
                                <span class="rounded-lg px-2 py-0.5 text-[10px] font-extrabold {{ $isActive ? 'bg-white/20 text-white' : 'bg-rose-100 text-rose-700' }}">{{ $count }}</span>
                            @endif
                        </a>
                    @endforeach
                </nav>

                {{-- Footer Sidebar: Kembali ke Halaman Publik --}}
                <div class="hidden border-t border-slate-100 pt-3 lg:block">
                    <a href="{{ route('home') }}"
                       class="flex items-center gap-2 rounded-xl px-3 py-2 text-xs font-semibold text-slate-500 hover:bg-slate-50 hover:text-slate-900 transition-all">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        <span>Ke halaman publik</span>
                    </a>
                </div>
            </div>
        </aside>

        <div class="min-w-0 flex-1">{{ $slot }}</div>
    </div>
</x-layouts.app>