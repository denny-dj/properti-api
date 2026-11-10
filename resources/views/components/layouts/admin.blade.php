@props(['title' => 'Dashboard Admin'])

@php
    $admin = auth()->user();
    $badges = app(\App\Services\AdminStatsService::class)->badges();

    $items = [
        [
            'label'    => 'Ringkasan', 
            'url'      => route('admin.dashboard'), 
            'patterns' => ['admin.dashboard'], 
            'count'    => 0,
            'icon'     => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'
        ]
    ];

    if ($admin->can('partners.verify')) {
        $items[] = [
            'label'    => 'Verifikasi mitra', 
            'url'      => route('admin.partners'), 
            'patterns' => ['admin.partners*'], 
            'count'    => $badges['partners'] ?? 0,
            'icon'     => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'
        ];
    }
    if ($admin->can('properties.verify')) {
        $items[] = [
            'label'    => 'Moderasi properti', 
            'url'      => route('admin.properties'), 
            'patterns' => ['admin.properties*'], 
            'count'    => $badges['properties'] ?? 0,
            'icon'     => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'
        ];
    }
    if ($admin->can('users.manage')) {
        $items[] = [
            'label'    => 'Pengguna', 
            'url'      => route('admin.users'), 
            'patterns' => ['admin.users*'], 
            'count'    => 0,
            'icon'     => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'
        ];
    }
    if (config('concierge.legacy_ui', true) && $admin->can('bookings.view')) {
        $items[] = [
            'label'    => 'Pesanan konsultasi', 
            'url'      => route('admin.bookings'), 
            'patterns' => ['admin.bookings*'], 
            'count'    => 0,
            'icon'     => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'
        ];
    }
    if ($admin->can('transactions.view')) {
        $items[] = [
            'label'    => 'Transaksi', 
            'url'      => route('admin.transactions'), 
            'patterns' => ['admin.transactions*'], 
            'count'    => $badges['transactions'] ?? 0,
            'icon'     => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'
        ];
        $items[] = [
            'label'    => 'Pendapatan', 
            'url'      => route('admin.revenue'), 
            'patterns' => ['admin.revenue*'], 
            'count'    => 0,
            'icon'     => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6'
        ];
    }
    if ($admin->can('concierge.manage')) {
        $items[] = [
            'label'    => 'Prospek', 
            'url'      => route('admin.leads'), 
            'patterns' => ['admin.leads*'], 
            'count'    => $badges['leads'] ?? 0,
            'icon'     => 'M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z'
        ];
        $items[] = [
            'label'    => 'Invoice cepat', 
            'url'      => route('admin.invoices'), 
            'patterns' => ['admin.invoices*'], 
            'count'    => 0,
            'icon'     => 'M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1'
        ];
    }
    if ($admin->can('payouts.manage')) {
        $items[] = [
            'label'    => 'Pencairan mitra', 
            'url'      => route('admin.payouts'), 
            'patterns' => ['admin.payouts*'], 
            'count'    => 0,
            'icon'     => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z'
        ];
    }
@endphp

<x-layouts.app :title="$title">
    {{-- Menggunakan class 230px bawaan yang sudah terdaftar di Tailwind build --}}
    <div class="mx-auto max-w-7xl gap-8 px-4 py-8 lg:grid lg:grid-cols-[230px_1fr] lg:items-start">
        
        {{-- Sidebar Kiri: Sticky Mengikuti Scroll --}}
        <aside class="mb-6 lg:mb-0 lg:sticky lg:top-24">
            <div class="rounded-3xl border border-slate-200/80 bg-white p-3.5 shadow-sm">
                
                {{-- Header Admin --}}
                <div class="border-b border-slate-100 px-2 pb-3">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Pusat Kendali</span>
                    <p class="truncate text-xs font-bold text-slate-900 mt-0.5">
                        Administrator
                    </p>
                </div>

                {{-- Navigasi Menu --}}
                <nav class="mt-2.5 flex gap-1 overflow-x-auto lg:flex-col lg:overflow-visible">
                    @foreach ($items as $item)
                        @php 
                            $active = request()->routeIs(...$item['patterns']); 
                        @endphp
                        <a href="{{ $item['url'] }}" 
                           class="group flex items-center justify-between gap-2 whitespace-nowrap rounded-xl px-3 py-2 text-xs font-semibold transition-all {{ $active ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <svg class="h-4 w-4 shrink-0 {{ $active ? 'text-emerald-400' : 'text-slate-400 group-hover:text-slate-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"/>
                                </svg>
                                <span class="truncate">{{ $item['label'] }}</span>
                            </div>
                            
                            @if ($item['count'] > 0)
                                <span class="inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full px-1.5 font-mono text-[10px] font-extrabold transition-colors {{ $active ? 'bg-white/20 text-white' : 'bg-amber-100 text-amber-900 border border-amber-200/60' }}">
                                    {{ $item['count'] }}
                                </span>
                            @endif
                        </a>
                    @endforeach
                </nav>

            </div>
        </aside>

        {{-- Konten Utama --}}
        <div class="min-w-0 w-full">
            {{ $slot }}
        </div>
    </div>
</x-layouts.app>