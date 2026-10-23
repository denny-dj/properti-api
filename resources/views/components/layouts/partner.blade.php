@props(['title' => 'Dashboard Mitra'])
@php
    $profile = auth()->user()->partnerProfile;
    $type = $profile?->partner_type?->value;
    $approved = (bool) $profile?->isApproved();

    $items = [['Ringkasan', route('partner.dashboard'), ['partner.dashboard']]];

    if ($approved && $type === 'seller') {
        $items[] = ['Properti saya', route('partner.properties.index'), ['partner.properties.index', 'partner.properties.edit']];
        $items[] = ['Tambah properti', route('partner.properties.create'), ['partner.properties.create']];
    }

    if ($approved && $type === 'architect') {
        $items[] = ['Layanan saya', route('partner.services'), ['partner.services']];
        $items[] = ['Jadwal kerja', route('partner.availability'), ['partner.availability']];
        $items[] = ['Pesanan masuk', route('partner.bookings'), ['partner.bookings']];
    }

    $items[] = ['Pembayaran masuk', route('partner.transactions'), ['partner.transactions']];
    $items[] = ['Chat', route('chat.index'), ['chat.*']];
@endphp
<x-layouts.app :title="$title">
    <div class="mx-auto max-w-7xl gap-8 px-4 py-8 lg:grid lg:grid-cols-[220px_1fr]">
        <aside class="mb-6 lg:mb-0">
            <p class="text-xs uppercase tracking-wide text-gray-400">{{ $profile?->business_name ?? 'Mitra' }}</p>
            <nav class="mt-2 flex gap-2 overflow-x-auto lg:flex-col lg:gap-1">
                @foreach ($items as [$label, $url, $patterns])
                    <a href="{{ $url }}" class="whitespace-nowrap rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs(...$patterns) ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-100' }}">{{ $label }}</a>
                @endforeach
            </nav>
        </aside>
        <div class="min-w-0">{{ $slot }}</div>
    </div>
</x-layouts.app>
