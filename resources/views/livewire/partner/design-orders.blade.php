@php
    $input = 'w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none';
@endphp
<div class="space-y-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold">Pesanan desain</h1>
            <p class="text-sm text-gray-500">Desain siap bangun dan tanda jadi custom yang dibuat admin untuk klien Anda.</p>
        </div>
        <select wire:model.live="stage" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
            <option value="">Semua pesanan</option>
            <option value="awaiting_link">Perlu kirim berkas</option>
            <option value="awaiting_confirmation">Menunggu konfirmasi pembeli</option>
            <option value="released">Dana dilepas</option>
            <option value="unpaid">Menunggu pembayaran</option>
        </select>
    </div>

    <div class="grid gap-3 sm:grid-cols-3">
        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5">
            <p class="text-sm text-amber-800">Perlu kirim berkas</p>
            <p class="mt-1 text-2xl font-bold text-amber-800">{{ $awaiting }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5">
            <p class="text-sm text-gray-500">Menunggu dilepas</p>
            <p class="mt-1 text-2xl font-bold text-amber-700"><x-rupiah :value="$balance['pending_release']" /></p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5">
            <p class="text-sm text-gray-500">Siap dicairkan</p>
            <p class="mt-1 text-2xl font-bold text-green-700"><x-rupiah :value="$balance['available']" /></p>
        </div>
    </div>
    <p class="text-xs text-gray-400">Nilai di atas adalah hak bersih Anda setelah komisi platform. "Siap dicairkan" = dana yang sudah dikonfirmasi pembeli dikurangi pencairan yang telah ditransfer administrator.</p>

    @if ($payouts->isNotEmpty())
        <div class="rounded-2xl border border-gray-200 bg-white p-4">
            <p class="text-sm font-semibold">Pencairan terakhir (total <x-rupiah :value="$balance['paid_out']" />)</p>
            <ul class="mt-2 divide-y divide-gray-100 text-sm">
                @foreach ($payouts as $payout)
                    <li class="flex flex-wrap items-center justify-between gap-2 py-2">
                        <span class="text-gray-600">{{ $payout->paid_at->format('d/m/Y H:i') }}@if ($payout->reference) · ref {{ $payout->reference }}@endif</span>
                        <span class="font-semibold text-green-700"><x-rupiah :value="$payout->amount" /></span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="rounded-2xl border border-blue-100 bg-blue-50 p-4 text-sm text-blue-900">
        <p class="font-medium">Cara menyerahkan berkas</p>
        <ol class="mt-1 list-decimal space-y-0.5 pl-5">
            <li>Unggah berkas final (DED, 3D, RAB) ke folder Google Drive Anda.</li>
            <li>Bagikan folder dengan akses "Siapa saja yang memiliki link: Pelihat".</li>
            <li>Tempel tautan folder di pesanan yang sudah lunas, lalu kirim. Dana dilepas setelah pembeli mengonfirmasi.</li>
        </ol>
    </div>

    @if ($notice)<p class="rounded-lg bg-green-50 px-3 py-2 text-sm text-green-800">{{ $notice }}</p>@endif
    @error('action')<p class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700">{{ $message }}</p>@enderror

    <div class="divide-y divide-gray-100 overflow-hidden rounded-2xl border border-gray-200 bg-white" wire:loading.class="opacity-60">
        @forelse ($orders as $t)
            @php
                $status = $t->status->value;

                if ($status === 'paid' && $t->client_confirmed_at) {
                    [$label, $classes] = ['Dana dilepas', 'bg-green-100 text-green-800'];
                } elseif ($status === 'paid' && $t->deliverable_link) {
                    [$label, $classes] = ['Menunggu konfirmasi pembeli', 'bg-blue-100 text-blue-800'];
                } elseif ($status === 'paid') {
                    [$label, $classes] = ['Perlu kirim berkas', 'bg-amber-100 text-amber-800'];
                } elseif ($status === 'pending') {
                    [$label, $classes] = ['Menunggu pembayaran', 'bg-gray-100 text-gray-700'];
                } else {
                    [$label, $classes] = [ucfirst($status), 'bg-gray-100 text-gray-700'];
                }
            @endphp
            <div wire:key="order-{{ $t->id }}" class="space-y-3 px-5 py-4">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="font-medium">{{ $t->description }}</p>
                        <p class="text-sm text-gray-500">Klien: {{ $t->payer?->name }} · {{ $t->created_at->format('d/m/Y H:i') }}</p>
                        <p class="text-xs text-gray-400">{{ $t->code }}</p>
                    </div>
                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $classes }}">{{ $label }}</span>
                </div>

                <p class="text-sm text-gray-600">
                    Nilai <x-rupiah :value="$t->amount" /> · komisi platform <x-rupiah :value="$t->platform_fee" /> ·
                    <span class="font-semibold text-green-700">hak bersih Anda <x-rupiah :value="$t->net_partner_amount" /></span>
                </p>

                @if ($status === 'pending')
                    <p class="text-sm text-gray-500">Admin sudah mengirim tautan pembayaran ke klien{{ $t->expires_at ? ', berlaku hingga '.$t->expires_at->format('d/m/Y H:i') : '' }}. Berkas dapat dikirim setelah lunas.</p>
                @elseif ($status === 'paid' && $t->client_confirmed_at)
                    <p class="text-sm text-green-800">Pembeli mengonfirmasi pada {{ $t->client_confirmed_at->format('d/m/Y H:i') }}. Dana masuk saldo siap dicairkan.</p>
                    <a href="{{ $t->deliverable_link }}" target="_blank" rel="noopener noreferrer nofollow" class="block break-all text-sm text-blue-700 underline">{{ $t->deliverable_link }}</a>
                @elseif ($status === 'paid')
                    @if ($t->deliverable_link)
                        <p class="text-sm text-gray-600">Tautan terkirim {{ $t->deliverable_submitted_at?->format('d/m/Y H:i') }}:
                            <a href="{{ $t->deliverable_link }}" target="_blank" rel="noopener noreferrer nofollow" class="break-all text-blue-700 underline">{{ $t->deliverable_link }}</a>
                        </p>
                    @endif
                    <div class="flex flex-wrap items-start gap-2">
                        <div class="min-w-0 flex-1 sm:max-w-xl">
                            <input type="url" wire:model="links.{{ $t->id }}" placeholder="https://drive.google.com/drive/folders/..." class="{{ $input }}">
                            @error('links.'.$t->id)<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <button type="button" wire:click="submitLink({{ $t->id }})" wire:loading.attr="disabled" wire:target="submitLink({{ $t->id }})"
                                class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                            {{ $t->deliverable_link ? 'Ganti tautan' : 'Kirim tautan' }}
                        </button>
                    </div>
                @endif
            </div>
        @empty
            <div class="p-10 text-center text-gray-500">Belum ada pesanan desain pada kategori ini.</div>
        @endforelse
    </div>

    <div>{{ $orders->links() }}</div>
</div>
