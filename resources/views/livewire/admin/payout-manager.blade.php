@php $input = 'mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none'; @endphp
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold">Pencairan mitra</h1>
        <p class="text-sm text-gray-500">Saldo siap cair = dana yang sudah dikonfirmasi pembeli dikurangi pencairan yang tercatat. Transfer dilakukan manual, lalu dicatat di sini.</p>
    </div>

    <p class="rounded-lg bg-amber-50 px-3 py-2 text-xs text-amber-800">Data rekening mitra belum disimpan di aplikasi. Minta nomor rekening lewat WhatsApp, lalu isi bukti atau referensi transfer pada kolom referensi agar dapat dilacak.</p>

    @if ($notice)<p class="rounded-lg bg-green-50 px-3 py-2 text-sm text-green-800">{{ $notice }}</p>@endif
    @error('list')<p class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700">{{ $message }}</p>@enderror

    <div class="divide-y divide-gray-100 overflow-hidden rounded-2xl border border-gray-200 bg-white">
        @forelse ($balances as $row)
            @php $p = $row['partner']; @endphp
            <div wire:key="bal-{{ $p->id }}" class="space-y-3 px-5 py-4">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="font-medium">{{ $p->partnerProfile?->business_name ?? $p->name }}</p>
                        <p class="text-sm text-gray-500">{{ $p->email }}@if ($p->phone) · {{ $p->phone }}@endif</p>
                    </div>
                    <div class="text-right text-sm">
                        <p class="text-xs text-gray-400">Siap dicairkan</p>
                        <p class="text-lg font-bold {{ $row['available'] > 0 ? 'text-green-700' : 'text-gray-400' }}"><x-rupiah :value="$row['available']" /></p>
                    </div>
                </div>
                <p class="text-xs text-gray-500">
                    Dilepas <x-rupiah :value="$row['released']" /> · sudah dicairkan <x-rupiah :value="$row['paid_out']" /> · menunggu konfirmasi pembeli <x-rupiah :value="$row['pending_release']" />
                </p>

                @if ($selected === $p->id)
                    <div class="space-y-3 rounded-xl border border-blue-200 bg-blue-50 p-4">
                        <div class="grid gap-3 sm:grid-cols-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600">Nominal ditransfer (Rp)</label>
                                <input type="text" inputmode="numeric" wire:model="amount" class="{{ $input }}">
                                @error('amount')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-medium text-gray-600">Referensi / bukti transfer</label>
                                <input type="text" wire:model="reference" class="{{ $input }}" placeholder="mis. BCA 05/11 no. ref 1234567">
                                @error('reference')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600">Catatan (opsional)</label>
                            <textarea rows="2" wire:model="note" class="{{ $input }}"></textarea>
                            @error('note')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div class="flex gap-2">
                            <button type="button" wire:click="record" wire:confirm="Catat pencairan ini? Pastikan transfer sudah dilakukan." class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">Catat pencairan</button>
                            <button type="button" wire:click="cancel" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium hover:bg-white">Batal</button>
                        </div>
                    </div>
                @elseif ($row['available'] > 0)
                    <button type="button" wire:click="startPay({{ $p->id }})" class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">Catat pencairan</button>
                @endif
            </div>
        @empty
            <div class="p-10 text-center text-gray-500">Belum ada saldo mitra. Saldo muncul setelah pembeli mengonfirmasi penerimaan berkas desain.</div>
        @endforelse
    </div>

    <div>
        <h2 class="mb-2 text-lg font-semibold">Riwayat pencairan</h2>
        <div class="divide-y divide-gray-100 overflow-hidden rounded-2xl border border-gray-200 bg-white">
            @forelse ($history as $h)
                <div wire:key="hist-{{ $h->id }}" class="flex flex-wrap items-center justify-between gap-3 px-5 py-3 text-sm">
                    <div class="min-w-0">
                        <p class="font-medium">{{ $h->partner?->partnerProfile?->business_name ?? $h->partner?->name }}</p>
                        <p class="text-xs text-gray-500">{{ $h->paid_at->format('d/m/Y H:i') }}@if ($h->reference) · ref {{ $h->reference }}@endif · dicatat {{ $h->recorder?->name ?? '-' }}</p>
                        @if ($h->note)<p class="text-xs text-gray-400">{{ $h->note }}</p>@endif
                    </div>
                    <p class="font-semibold text-green-700"><x-rupiah :value="$h->amount" /></p>
                </div>
            @empty
                <div class="p-8 text-center text-gray-500">Belum ada pencairan tercatat.</div>
            @endforelse
        </div>
        <div class="mt-3">{{ $history->links() }}</div>
    </div>
</div>
