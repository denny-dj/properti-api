<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold">Ringkasan</h1>
        <p class="text-sm text-gray-500">{{ $profile?->business_name }}</p>
    </div>

    @if (! $profile)
        <p class="rounded-xl bg-red-50 p-4 text-sm text-red-700">Profil mitra tidak ditemukan. Hubungi administrator.</p>
    @elseif ($profile->verification_status->value === 'pending')
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
            Akun mitra Anda menunggu verifikasi administrator. Setelah disetujui, menu {{ $profile->partner_type->value === 'architect' ? 'layanan, jadwal, dan pesanan' : 'properti' }} akan aktif.
        </div>
    @elseif ($profile->verification_status->value === 'rejected')
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            Verifikasi akun Anda ditolak.@if ($profile->rejection_reason) Alasan: {{ $profile->rejection_reason }}@endif Hubungi administrator untuk bantuan.
        </div>
    @endif

    <div class="grid gap-4 sm:grid-cols-2">
        <div class="rounded-2xl border border-gray-200 bg-white p-5">
            <p class="text-sm text-gray-500">Pembayaran diterima</p>
            <p class="mt-1 text-2xl font-bold text-green-700"><x-rupiah :value="$revenue['paid']" /></p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5">
            <p class="text-sm text-gray-500">Menunggu pembayaran</p>
            <p class="mt-1 text-2xl font-bold text-amber-700"><x-rupiah :value="$revenue['pending']" /></p>
        </div>
    </div>

    @if ($seller)
        <div>
            <div class="flex items-end justify-between">
                <h2 class="text-lg font-semibold">Properti</h2>
                <a href="{{ route('partner.properties.create') }}" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">Tambah properti</a>
            </div>
            <div class="mt-3 grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6">
                @foreach (['draft' => 'Draft', 'pending_review' => 'Menunggu verifikasi', 'published' => 'Tayang', 'reserved' => 'Dipesan', 'rejected' => 'Ditolak', 'sold' => 'Terjual'] as $key => $label)
                    <a href="{{ route('partner.properties.index', ['status' => $key]) }}" class="rounded-xl border border-gray-200 bg-white p-4 hover:shadow-sm">
                        <p class="text-2xl font-bold">{{ $seller['by_status'][$key] ?? 0 }}</p>
                        <p class="text-xs text-gray-500">{{ $label }}</p>
                    </a>
                @endforeach
            </div>
            <p class="mt-2 text-sm text-gray-500">Total dilihat: {{ number_format($seller['views'], 0, ',', '.') }} kali.</p>

            @if ($recentProperties->isNotEmpty())
                <div class="mt-4 divide-y divide-gray-100 overflow-hidden rounded-2xl border border-gray-200 bg-white">
                    @foreach ($recentProperties as $p)
                        <a href="{{ route('partner.properties.edit', $p->id) }}" class="flex items-center justify-between gap-3 px-5 py-3 hover:bg-gray-50">
                            <span class="truncate text-sm font-medium">{{ $p->title }}</span>
                            <x-status-badge :status="$p->status" context="property" />
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    @if ($architect)
        <div>
            <h2 class="text-lg font-semibold">Konsultasi</h2>
            <div class="mt-3 grid grid-cols-2 gap-3 md:grid-cols-4">
                <a href="{{ route('partner.bookings', ['status' => 'pending']) }}" class="rounded-xl border border-amber-200 bg-amber-50 p-4"><p class="text-2xl font-bold text-amber-800">{{ $architect['pending'] }}</p><p class="text-xs text-amber-800">Menunggu konfirmasi</p></a>
                <a href="{{ route('partner.bookings', ['status' => 'confirmed']) }}" class="rounded-xl border border-gray-200 bg-white p-4"><p class="text-2xl font-bold">{{ $architect['upcoming'] }}</p><p class="text-xs text-gray-500">Jadwal mendatang</p></a>
                <div class="rounded-xl border border-gray-200 bg-white p-4"><p class="text-2xl font-bold">{{ $architect['completed'] }}</p><p class="text-xs text-gray-500">Selesai</p></div>
                <a href="{{ route('partner.services') }}" class="rounded-xl border border-gray-200 bg-white p-4"><p class="text-2xl font-bold">{{ $architect['services'] }}</p><p class="text-xs text-gray-500">Layanan</p></a>
            </div>

            @if ($upcoming->isNotEmpty())
                <h3 class="mt-5 text-sm font-semibold text-gray-700">Jadwal terdekat</h3>
                <div class="mt-2 divide-y divide-gray-100 overflow-hidden rounded-2xl border border-gray-200 bg-white">
                    @foreach ($upcoming as $b)
                        <div class="flex items-center justify-between gap-3 px-5 py-3">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium">{{ $b->service?->title }}</p>
                                <p class="text-xs text-gray-500">{{ $b->scheduled_start->format('d/m/Y H:i') }} · {{ $b->client?->name }}</p>
                            </div>
                            <x-status-badge :status="$b->status" context="booking" />
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif
</div>
