@props(['title' => 'Dashboard Mitra'])

@php
    $profile = auth()->user()->partnerProfile;
    $type = $profile?->partner_type?->value;
    $approved = (bool) $profile?->isApproved();

    $items = [
        [
            'label'    => 'Ringkasan', 
            'url'      => route('partner.dashboard'), 
            'patterns' => ['partner.dashboard'],
            'icon'     => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'
        ]
    ];

    if ($approved && $type === 'seller') {
        $items[] = [
            'label'    => 'Properti saya', 
            'url'      => route('partner.properties.index'), 
            'patterns' => ['partner.properties.index', 'partner.properties.edit'],
            'icon'     => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'
        ];
        $items[] = [
            'label'    => 'Tambah properti', 
            'url'      => route('partner.properties.create'), 
            'patterns' => ['partner.properties.create'],
            'icon'     => 'M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z'
        ];
    }

    if ($approved && $type === 'architect') {
        $items[] = [
            'label'    => 'Portofolio saya', 
            'url'      => route('partner.portfolios.index'), 
            'patterns' => ['partner.portfolios.*'],
            'icon'     => 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z'
        ];
        $items[] = [
            'label'    => 'Pesanan desain', 
            'url'      => route('partner.design-orders'), 
            'patterns' => ['partner.design-orders'],
            'icon'     => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'
        ];

        if (config('concierge.legacy_ui', true)) {
            $items[] = [
                'label'    => 'Layanan saya', 
                'url'      => route('partner.services'), 
                'patterns' => ['partner.services'],
                'icon'     => 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'
            ];
            $items[] = [
                'label'    => 'Jadwal kerja', 
                'url'      => route('partner.availability'), 
                'patterns' => ['partner.availability'],
                'icon'     => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'
            ];
            $items[] = [
                'label'    => 'Pesanan masuk', 
                'url'      => route('partner.bookings'), 
                'patterns' => ['partner.bookings'],
                'icon'     => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'
            ];
        }
    }

    $items[] = [
        'label'    => 'Pembayaran masuk', 
        'url'      => route('partner.transactions'), 
        'patterns' => ['partner.transactions*'],
        'icon'     => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'
    ];

    if (config('concierge.legacy_ui', true)) {
        $items[] = [
            'label'    => 'Chat', 
            'url'      => route('chat.index'), 
            'patterns' => ['chat.*'],
            'icon'     => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z'
        ];
    }
@endphp

<x-layouts.app :title="$title">
    {{-- Grid 220px yang terdaftar di CSS Tailwind --}}
    <div class="mx-auto max-w-7xl gap-8 px-4 py-6 sm:px-6 lg:px-8 lg:grid lg:grid-cols-[220px_1fr] lg:items-start">
        
        {{-- Sidebar Kiri: Sticky Mengikuti Scroll --}}
        <aside class="mb-6 lg:mb-0 lg:sticky lg:top-20 z-10">
            <div class="max-h-[calc(100vh-6rem)] overflow-y-auto rounded-3xl border border-slate-200/80 bg-white p-3.5 shadow-sm scrollbar-thin">
                
                {{-- Header Identitas Usaha Mitra --}}
                <div class="border-b border-slate-100 px-2 pb-3">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Portal Kemitraan</span>
                    <p class="truncate text-xs font-bold text-slate-900 mt-0.5">
                        {{ $profile?->business_name ?? 'Mitra Platform' }}
                    </p>
                </div>

                {{-- Navigasi Menu Mitra --}}
                <nav class="mt-2.5 flex gap-1 overflow-x-auto lg:flex-col lg:overflow-visible">
                    @foreach ($items as $item)
                        @php 
                            $active = request()->routeIs(...$item['patterns']); 
                        @endphp
                        <a href="{{ $item['url'] }}" 
                           class="group flex items-center gap-2.5 whitespace-nowrap rounded-xl px-3 py-2 text-xs font-semibold transition-all {{ $active ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                            <svg class="h-4 w-4 shrink-0 {{ $active ? 'text-emerald-400' : 'text-slate-400 group-hover:text-slate-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"/>
                            </svg>
                            <span class="truncate">{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                </nav>

            </div>
        </aside>

        {{-- Kolom Konten Utama --}}
        <div class="min-w-0 w-full relative z-0">
            {{ $slot }}
        </div>

    </div>
</x-layouts.app>