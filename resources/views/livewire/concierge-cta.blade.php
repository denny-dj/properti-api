<div class="space-y-3 rounded-2xl border border-green-200 bg-green-50 p-5">
    <p class="font-semibold text-green-900">Konsultasi dengan admin kami</p>
    <p class="text-sm text-green-800">Admin mendampingi Anda dari cek ketersediaan, survei, negosiasi, hingga pembayaran yang aman lewat platform.</p>

    @error('cta')
        <p class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700">{{ $message }}</p>
    @enderror

    @if (! $open)
        <button type="button" wire:click="$set('open', true)" class="w-full rounded-lg bg-green-600 py-2.5 font-medium text-white hover:bg-green-700">
            Konsultasi via WhatsApp Admin
        </button>
    @else
        <div class="space-y-3 rounded-xl bg-white p-3">
            <p class="text-xs text-gray-500">Data berikut opsional dan membantu admin menyiapkan jawaban. Boleh dikosongkan.</p>

            <div>
                <label class="block text-xs font-medium text-gray-600">Nama</label>
                <input type="text" wire:model="name" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600">Nomor WhatsApp</label>
                <input type="text" inputmode="tel" wire:model="phone" placeholder="081234567890" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                @error('phone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600">Anggaran (Rp)</label>
                    <input type="text" inputmode="numeric" wire:model="budget" placeholder="500000000" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    @error('budget')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600">Rencana bayar</label>
                    <select wire:model="preference" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <option value="">Belum tahu</option>
                        @foreach ($preferences as $option)
                            <option value="{{ $option->value }}">{{ $option->label() }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <button type="button" wire:click="openWhatsApp" wire:loading.attr="disabled" class="w-full rounded-lg bg-green-600 py-2.5 font-medium text-white hover:bg-green-700">
                <span wire:loading.remove wire:target="openWhatsApp">Buka WhatsApp</span>
                <span wire:loading wire:target="openWhatsApp">Menyiapkan...</span>
            </button>
        </div>
    @endif

    <p class="text-xs text-green-800">Lakukan pembayaran hanya lewat tautan resmi dari platform agar transaksi Anda terlindungi.</p>
</div>
