@php
    $propertyLabels = ['draft' => 'Draft', 'pending_review' => 'Menunggu verifikasi', 'published' => 'Tayang', 'reserved' => 'Dipesan', 'rejected' => 'Ditolak', 'sold' => 'Terjual'];
    $bookingLabels = ['pending' => 'Menunggu', 'confirmed' => 'Dikonfirmasi', 'completed' => 'Selesai', 'cancelled' => 'Dibatalkan', 'rejected' => 'Ditolak'];
    $roleLabels = ['user' => 'Pembeli', 'partner' => 'Mitra', 'admin' => 'Admin'];
    $pendingBookings = $overview['bookings']['pending'] ?? 0;
@endphp
<div class="space-y-6">
    <h1 class="text-2xl font-bold">Ringkasan</h1>

    {{-- Antrean yang menunggu tindakan --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <a href="{{ route('admin.partners') }}" class="rounded-2xl border p-5 {{ $badges['partners'] > 0 ? 'border-amber-300 bg-amber-50' : 'border-gray-200 bg-white' }}">
            <p class="text-3xl font-bold">{{ $badges['partners'] }}</p>
            <p class="text-sm text-gray-600">Mitra menunggu verifikasi</p>
        </a>
        <a href="{{ route('admin.properties') }}" class="rounded-2xl border p-5 {{ $badges['properties'] > 0 ? 'border-amber-300 bg-amber-50' : 'border-gray-200 bg-white' }}">
            <p class="text-3xl font-bold">{{ $badges['properties'] }}</p>
            <p class="text-sm text-gray-600">Properti menunggu moderasi</p>
        </a>
        <a href="{{ route('admin.transactions', ['needsReview' => 'true']) }}" class="rounded-2xl border p-5 {{ $badges['transactions'] > 0 ? 'border-red-300 bg-red-50' : 'border-gray-200 bg-white' }}">
            <p class="text-3xl font-bold">{{ $badges['transactions'] }}</p>
            <p class="text-sm text-gray-600">Transaksi perlu ditinjau</p>
        </a>
        <a href="{{ route('admin.bookings', ['status' => 'pending']) }}" class="rounded-2xl border border-gray-200 bg-white p-5">
            <p class="text-3xl font-bold">{{ $pendingBookings }}</p>
            <p class="text-sm text-gray-600">Pesanan menunggu arsitek</p>
        </a>
    </div>

    {{-- Pendapatan --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-gray-200 bg-white p-5"><p class="text-sm text-gray-500">Total pembayaran lunas</p><p class="mt-1 text-xl font-bold text-green-700"><x-rupiah :value="$overview['revenue']['paid_total']" /></p></div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5"><p class="text-sm text-gray-500">Bulan ini</p><p class="mt-1 text-xl font-bold"><x-rupiah :value="$overview['revenue']['month_total']" /></p></div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5"><p class="text-sm text-gray-500">Menunggu pembayaran</p><p class="mt-1 text-xl font-bold text-amber-700"><x-rupiah :value="$overview['revenue']['pending_total']" /></p></div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5"><p class="text-sm text-gray-500">Transaksi lunas</p><p class="mt-1 text-xl font-bold">{{ number_format($overview['revenue']['paid_count'], 0, ',', '.') }}</p></div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="rounded-2xl border border-gray-200 bg-white p-5">
            <h2 class="font-semibold">Pengguna</h2>
            <p class="mt-2 text-3xl font-bold">{{ number_format($overview['users']['total'], 0, ',', '.') }}</p>
            <ul class="mt-2 space-y-1 text-sm text-gray-600">
                @foreach ($roleLabels as $key => $label)
                    <li class="flex justify-between"><span>{{ $label }}</span><span class="font-medium">{{ $overview['users']['by_role'][$key] ?? 0 }}</span></li>
                @endforeach
                <li class="flex justify-between text-red-700"><span>Disuspend</span><span class="font-medium">{{ $overview['users']['suspended'] }}</span></li>
            </ul>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5">
            <h2 class="font-semibold">Properti</h2>
            <ul class="mt-3 space-y-1 text-sm text-gray-600">
                @foreach ($propertyLabels as $key => $label)
                    <li class="flex justify-between"><span>{{ $label }}</span><span class="font-medium">{{ $overview['properties'][$key] ?? 0 }}</span></li>
                @endforeach
            </ul>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5">
            <h2 class="font-semibold">Pesanan konsultasi</h2>
            <ul class="mt-3 space-y-1 text-sm text-gray-600">
                @foreach ($bookingLabels as $key => $label)
                    <li class="flex justify-between"><span>{{ $label }}</span><span class="font-medium">{{ $overview['bookings'][$key] ?? 0 }}</span></li>
                @endforeach
            </ul>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div>
            <div class="flex items-end justify-between"><h2 class="font-semibold">Mitra menunggu verifikasi</h2><a href="{{ route('admin.partners') }}" class="text-sm font-medium text-blue-700 hover:underline">Kelola</a></div>
            <div class="mt-2 divide-y divide-gray-100 overflow-hidden rounded-2xl border border-gray-200 bg-white">
                @forelse ($pendingPartners as $p)
                    <div class="px-5 py-3 text-sm"><p class="font-medium">{{ $p->business_name }}</p><p class="text-gray-500">{{ $p->user?->name }} · {{ $p->partner_type->value === 'architect' ? 'Arsitek' : 'Developer / Agen' }}</p></div>
                @empty
                    <p class="p-5 text-sm text-gray-400">Tidak ada antrean.</p>
                @endforelse
            </div>
        </div>
        <div>
            <div class="flex items-end justify-between"><h2 class="font-semibold">Properti menunggu moderasi</h2><a href="{{ route('admin.properties') }}" class="text-sm font-medium text-blue-700 hover:underline">Kelola</a></div>
            <div class="mt-2 divide-y divide-gray-100 overflow-hidden rounded-2xl border border-gray-200 bg-white">
                @forelse ($pendingProperties as $p)
                    <div class="px-5 py-3 text-sm"><p class="truncate font-medium">{{ $p->title }}</p><p class="text-gray-500">{{ $p->partner?->name }} · {{ $p->city }}</p></div>
                @empty
                    <p class="p-5 text-sm text-gray-400">Tidak ada antrean.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
