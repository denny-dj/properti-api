<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold">Pendapatan platform</h1>
        <p class="text-sm text-gray-500">Hanya transaksi lunas, berdasarkan tanggal pembayaran.</p>
    </div>

    <div class="flex flex-wrap items-end gap-3">
        <div>
            <label class="block text-xs font-medium text-gray-600">Dari</label>
            <input type="date" wire:model.live="from" class="mt-1 rounded-lg border border-gray-300 px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600">Sampai</label>
            <input type="date" wire:model.live="to" class="mt-1 rounded-lg border border-gray-300 px-3 py-2 text-sm">
        </div>
        <div class="flex flex-wrap gap-2 text-sm">
            <button type="button" wire:click="preset('month')" class="rounded-lg border border-gray-300 px-3 py-2 font-medium hover:bg-gray-50">Bulan ini</button>
            <button type="button" wire:click="preset('last_month')" class="rounded-lg border border-gray-300 px-3 py-2 font-medium hover:bg-gray-50">Bulan lalu</button>
            <button type="button" wire:click="preset('year')" class="rounded-lg border border-gray-300 px-3 py-2 font-medium hover:bg-gray-50">Tahun ini</button>
            <button type="button" wire:click="preset('all')" class="rounded-lg border border-gray-300 px-3 py-2 font-medium hover:bg-gray-50">Semua</button>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" wire:loading.class="opacity-60">
        <div class="rounded-2xl border border-gray-200 bg-white p-5">
            <p class="text-sm text-gray-500">Pembayaran lunas</p>
            <p class="mt-1 text-xl font-bold"><x-rupiah :value="$summary['totals']['gross']" /></p>
            <p class="text-xs text-gray-400">{{ number_format($summary['totals']['transactions'], 0, ',', '.') }} transaksi</p>
        </div>
        <div class="rounded-2xl border border-green-200 bg-green-50 p-5">
            <p class="text-sm text-green-800">Komisi platform</p>
            <p class="mt-1 text-xl font-bold text-green-800"><x-rupiah :value="$summary['totals']['platform_fee']" /></p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5">
            <p class="text-sm text-gray-500">Hak bersih mitra</p>
            <p class="mt-1 text-xl font-bold"><x-rupiah :value="$summary['totals']['net_partner_amount']" /></p>
        </div>
        @if ($outstanding !== null)
            <a href="{{ route('admin.payouts') }}" class="rounded-2xl border border-amber-200 bg-amber-50 p-5">
                <p class="text-sm text-amber-800">Saldo mitra belum dicairkan</p>
                <p class="mt-1 text-xl font-bold text-amber-800"><x-rupiah :value="$outstanding" /></p>
                <p class="text-xs text-amber-700">Posisi saat ini, bukan per periode</p>
            </a>
        @endif
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div class="rounded-2xl border border-gray-200 bg-white p-5">
            <p class="text-sm text-gray-500">Dana desain menunggu konfirmasi pembeli</p>
            <p class="mt-1 text-lg font-bold text-amber-700"><x-rupiah :value="$summary['architect_escrow']['pending_release']" /></p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5">
            <p class="text-sm text-gray-500">Dana desain sudah dilepas ke mitra</p>
            <p class="mt-1 text-lg font-bold text-green-700"><x-rupiah :value="$summary['architect_escrow']['released']" /></p>
        </div>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-gray-200 bg-white">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                <tr>
                    <th class="px-5 py-3">Jenis</th>
                    <th class="px-5 py-3 text-right">Transaksi</th>
                    <th class="px-5 py-3 text-right">Nilai</th>
                    <th class="px-5 py-3 text-right">Komisi platform</th>
                    <th class="px-5 py-3 text-right">Hak mitra</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($summary['by_type'] as $row)
                    <tr>
                        <td class="px-5 py-3">{{ $row['label'] }}</td>
                        <td class="px-5 py-3 text-right">{{ $row['transactions'] }}</td>
                        <td class="px-5 py-3 text-right"><x-rupiah :value="$row['gross']" /></td>
                        <td class="px-5 py-3 text-right font-medium text-green-700"><x-rupiah :value="$row['platform_fee']" /></td>
                        <td class="px-5 py-3 text-right"><x-rupiah :value="$row['net_partner_amount']" /></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-8 text-center text-gray-500">Belum ada transaksi lunas pada periode ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <ul class="list-disc space-y-1 pl-5 text-xs text-gray-500">
        @foreach ($summary['notes'] as $note)<li>{{ $note }}</li>@endforeach
    </ul>
</div>
