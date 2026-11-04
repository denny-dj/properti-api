@php
    $input = 'mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none';
    $isProperty = $type === 'property_booking_fee';
@endphp
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold">Invoice cepat</h1>
        <p class="text-sm text-gray-500">Buat tautan pembayaran resmi saat pembeli sudah sepakat di WhatsApp, lalu kirim pesannya.</p>
    </div>

    @error('form')<p class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700">{{ $message }}</p>@enderror

    @if ($result)
        <div class="space-y-4 rounded-2xl border border-green-200 bg-green-50 p-5">
            <div>
                <p class="font-semibold text-green-900">{{ $result['reused'] ? 'Tagihan aktif sebelumnya digunakan kembali' : 'Tagihan dibuat' }}</p>
                <p class="text-sm text-green-800">{{ $result['description'] }} · {{ $result['code'] }}</p>
            </div>

            <dl class="grid gap-3 text-sm sm:grid-cols-2">
                <div><dt class="text-gray-500">Pembeli</dt><dd class="font-medium">{{ $result['buyer_name'] }}</dd></div>
                <div><dt class="text-gray-500">Mitra</dt><dd class="font-medium">{{ $result['partner_name'] }}</dd></div>
                <div><dt class="text-gray-500">Nominal ditagih</dt><dd class="font-semibold"><x-rupiah :value="$result['amount']" /></dd></div>
                <div><dt class="text-gray-500">Berlaku hingga</dt><dd class="font-medium">{{ $result['expires_at'] }}</dd></div>
                <div><dt class="text-gray-500">Komisi platform</dt><dd class="font-medium"><x-rupiah :value="$result['platform_fee']" /> ({{ $result['commission_percent'] }}%{{ $result['fee_basis'] === 'property_price' ? ' dari harga properti' : '' }})</dd></div>
                <div><dt class="text-gray-500">Hak bersih mitra</dt><dd class="font-medium"><x-rupiah :value="$result['net_partner_amount']" /></dd></div>
            </dl>

            <div x-data="{ copied: false }">
                <label class="block text-xs font-medium text-gray-600">Pesan siap kirim</label>
                <textarea x-ref="msg" readonly rows="8" class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">{{ $result['wa_message'] }}</textarea>
                <div class="mt-3 flex flex-wrap gap-2">
                    <button type="button" x-on:click="navigator.clipboard.writeText($refs.msg.value); copied = true; setTimeout(() => copied = false, 2000)"
                            class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium hover:bg-gray-50">
                        <span x-show="!copied">Salin pesan</span><span x-show="copied" x-cloak>Tersalin</span>
                    </button>
                    <a href="{{ $result['wa_url'] }}" target="_blank" rel="noopener" class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">Kirim via WhatsApp</a>
                    <a href="{{ $result['payment_url'] }}" target="_blank" rel="noopener" class="rounded-lg border border-blue-300 bg-white px-4 py-2 text-sm font-medium text-blue-700 hover:bg-blue-50">Buka halaman bayar</a>
                    <button type="button" wire:click="createAnother" class="rounded-lg px-4 py-2 text-sm font-medium text-gray-600 hover:bg-white">Buat invoice lain</button>
                </div>
                <p class="mt-2 text-xs text-gray-500">Tombol WhatsApp membuka chat ke nomor pembeli bila tersimpan di akunnya; jika tidak, pilih kontak secara manual.</p>
            </div>
        </div>
    @else
        <form wire:submit="submit" class="space-y-5 rounded-2xl border border-gray-200 bg-white p-5">
            @if ($leadInfo)
                <p class="rounded-lg bg-blue-50 px-3 py-2 text-sm text-blue-900">Prospek <span class="font-mono font-semibold">{{ $leadCode }}</span> · {{ $leadInfo }}</p>
            @endif

            <div>
                <label class="block text-sm font-medium">Jenis tagihan</label>
                <select wire:model.live="type" class="{{ $input }}">
                    <option value="property_booking_fee">Booking fee properti</option>
                    <option value="architect_blueprint">Arsitek: paket desain siap bangun (Jalur A)</option>
                    <option value="architect_custom">Arsitek: tanda jadi custom/survei (Jalur B)</option>
                </select>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium">Email pembeli (akun terdaftar)</label>
                    <input type="email" wire:model="buyerEmail" class="{{ $input }}" placeholder="pembeli@email.com">
                    @error('buyerEmail')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium">{{ $isProperty ? 'ID properti' : 'ID portofolio' }}</label>
                    <input type="text" inputmode="numeric" wire:model.live.debounce.500ms="itemId" class="{{ $input }}">
                    @error('itemId')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                @if ($type !== 'architect_blueprint')
                    <div>
                        <label class="block text-sm font-medium">{{ $isProperty ? 'Nominal booking fee (opsional)' : 'Nominal tanda jadi' }}</label>
                        <input type="text" inputmode="numeric" wire:model.live.debounce.500ms="amount" class="{{ $input }}" placeholder="{{ $isProperty ? 'kosong = aturan bawaan' : '3000000' }}">
                        @error('amount')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                @endif
                @unless ($isProperty)
                    <div>
                        <label class="block text-sm font-medium">Komisi arsitek (%)</label>
                        <input type="text" inputmode="decimal" wire:model.live.debounce.500ms="commission" class="{{ $input }}" placeholder="{{ config('concierge.fees.architect_default_percent') }}">
                        <p class="mt-1 text-xs text-gray-500">Rentang {{ config('concierge.fees.architect_min_percent') }}-{{ config('concierge.fees.architect_max_percent') }}%.</p>
                        @error('commission')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                @endunless
                <div>
                    <label class="block text-sm font-medium">Masa berlaku (jam)</label>
                    <input type="text" inputmode="numeric" wire:model="expiresHours" class="{{ $input }}">
                    @error('expiresHours')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium">Kode prospek (opsional)</label>
                <input type="text" wire:model="leadCode" class="{{ $input }} sm:max-w-xs" placeholder="LEAD-2026-XXXX">
                @error('leadCode')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            @if ($preview)
                <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 text-sm">
                    <p class="font-medium">{{ $preview['title'] }}</p>
                    <p class="text-xs text-gray-500">Milik {{ $preview['owner'] }} · status {{ $preview['status'] }}</p>
                    <dl class="mt-3 space-y-1">
                        @foreach ($preview['lines'] as [$label, $value])
                            <div class="flex justify-between gap-3"><dt class="text-gray-600">{{ $label }}</dt><dd class="font-medium"><x-rupiah :value="$value" /></dd></div>
                        @endforeach
                    </dl>
                    @if ($preview['warning'])<p class="mt-3 rounded-lg bg-amber-50 px-3 py-2 text-xs text-amber-800">{{ $preview['warning'] }}</p>@endif
                </div>
            @endif

            <p class="text-xs text-gray-500">Pembeli harus sudah memiliki akun. Invoice dari admin tidak mewajibkan email terverifikasi karena identitas sudah dikonfirmasi lewat WhatsApp.</p>

            <button type="submit" wire:loading.attr="disabled" wire:target="submit" class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-700">
                <span wire:loading.remove wire:target="submit">Buat tagihan</span>
                <span wire:loading wire:target="submit">Membuat...</span>
            </button>
        </form>
    @endif
</div>
