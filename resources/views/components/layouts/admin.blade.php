@props(['title' => 'Dashboard Admin'])
@php
    $admin = auth()->user();
    $badges = app(\App\Services\AdminStatsService::class)->badges();

    $items = [['Ringkasan', route('admin.dashboard'), ['admin.dashboard'], 0]];

    if ($admin->can('partners.verify')) {
        $items[] = ['Verifikasi mitra', route('admin.partners'), ['admin.partners'], $badges['partners']];
    }
    if ($admin->can('properties.verify')) {
        $items[] = ['Moderasi properti', route('admin.properties'), ['admin.properties'], $badges['properties']];
    }
    if ($admin->can('users.manage')) {
        $items[] = ['Pengguna', route('admin.users'), ['admin.users'], 0];
    }
    if ($admin->can('bookings.view')) {
        $items[] = ['Pesanan konsultasi', route('admin.bookings'), ['admin.bookings'], 0];
    }
    if ($admin->can('transactions.view')) {
        $items[] = ['Transaksi', route('admin.transactions'), ['admin.transactions'], $badges['transactions']];
    }
@endphp
<x-layouts.app :title="$title">
    <div class="mx-auto max-w-7xl gap-8 px-4 py-8 lg:grid lg:grid-cols-[230px_1fr]">
        <aside class="mb-6 lg:mb-0">
            <p class="text-xs uppercase tracking-wide text-gray-400">Administrator</p>
            <nav class="mt-2 flex gap-2 overflow-x-auto lg:flex-col lg:gap-1">
                @foreach ($items as [$label, $url, $patterns, $count])
                    <a href="{{ $url }}" class="flex items-center justify-between gap-2 whitespace-nowrap rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs(...$patterns) ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-100' }}">
                        <span>{{ $label }}</span>
                        @if ($count > 0)
                            <span class="rounded-full bg-red-100 px-2 py-0.5 text-xs font-semibold text-red-700">{{ $count }}</span>
                        @endif
                    </a>
                @endforeach
            </nav>
        </aside>
        <div class="min-w-0">{{ $slot }}</div>
    </div>
</x-layouts.app>
