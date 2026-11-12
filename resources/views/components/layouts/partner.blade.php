@props(['title' => 'Dashboard Mitra'])
@php
    $profile = auth()->user()->partnerProfile;
    $type = $profile?->partner_type?->value;
    $approved = (bool) $profile?->isApproved();

    $items = [['Ringkasan', route('partner.dashboard'), ['partner.dashboard'], 'grid']];

    if ($approved && $type === 'seller') {
        $items[] = ['Properti saya', route('partner.properties.index'), ['partner.properties.index', 'partner.properties.edit'], 'building'];
        $items[] = ['Tambah properti', route('partner.properties.create'), ['partner.properties.create'], 'plus'];
    }

    if ($approved && $type === 'architect') {
        $items[] = ['Portofolio saya', route('partner.portfolios.index'), ['partner.portfolios.*'], 'layers'];
        $items[] = ['Pesanan desain', route('partner.design-orders'), ['partner.design-orders'], 'folder'];

        if (config('concierge.legacy_ui', true)) {
            $items[] = ['Layanan saya', route('partner.services'), ['partner.services'], 'briefcase'];
            $items[] = ['Jadwal kerja', route('partner.availability'), ['partner.availability'], 'clock'];
            $items[] = ['Pesanan masuk', route('partner.bookings'), ['partner.bookings'], 'calendar'];
        }
    }

    $items[] = ['Pembayaran masuk', route('partner.transactions'), ['partner.transactions'], 'banknotes'];
    $items[] = ['Profil saya', route('profile'), ['profile'], 'user'];
    if (config('concierge.legacy_ui', true)) {
        $items[] = ['Chat', route('chat.index'), ['chat.*'], 'chat'];
    }
@endphp
<x-layouts.app :title="$title">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8 lg:flex lg:items-start lg:gap-8">
        <aside class="mb-6 w-full shrink-0 lg:mb-0 lg:w-64">
            <div class="sticky top-24 rounded-3xl border border-slate-200/80 bg-white p-4 shadow-sm space-y-4">
                {{-- Header Kartu Sidebar Mitra --}}
                <div class="flex items-center gap-3 border-b border-slate-100 pb-3.5 px-1">
                    @if ($profile)
                        <img src="{{ $profile->logo_url }}" alt="" class="h-9 w-9 shrink-0 rounded-xl bg-slate-50 object-contain p-1 ring-1 ring-slate-200">
                    @else
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-slate-900 text-white">
                            <svg class="h-4 w-4 text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16"/></svg>
                        </div>
                    @endif
                    <div class="min-w-0">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">{{ $type === 'architect' ? 'Mitra Arsitek' : 'Mitra Bisnis' }}</p>
                        <p class="truncate text-xs font-extrabold text-slate-900">{{ $profile?->business_name ?? 'Mitra' }}</p>
                    </div>
                </div>

                {{-- Navigasi Menu Mitra --}}
                <nav class="flex gap-1.5 overflow-x-auto pb-1 lg:flex-col lg:pb-0">
                    @foreach ($items as [$label, $url, $patterns, $icon])
                        @php $isActive = request()->routeIs(...$patterns); @endphp
                        <a href="{{ $url }}"
                           class="group flex items-center gap-2.5 whitespace-nowrap rounded-xl px-3 py-2.5 text-xs font-bold transition-all {{ $isActive ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                            <svg class="h-4 w-4 shrink-0 {{ $isActive ? 'text-blue-400' : 'text-slate-400 group-hover:text-slate-700' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                @switch($icon)
                                    @case('grid')
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                                        @break
                                    @case('building')
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                        @break
                                    @case('plus')
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                        @break
                                    @case('layers')
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                        @break
                                    @case('folder')
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                                        @break
                                    @case('briefcase')
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        @break
                                    @case('clock')
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        @break
                                    @case('calendar')
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        @break
                                    @case('banknotes')
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        @break
                                    @case('chat')
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                        @break
                                    @default
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                @endswitch
                            </svg>
                            <span class="truncate">{{ $label }}</span>
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