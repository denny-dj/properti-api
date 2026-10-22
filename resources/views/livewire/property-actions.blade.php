<div class="space-y-3 rounded-2xl border border-gray-200 bg-white p-5">
    @if ($isOwner)
        <p class="text-sm text-gray-500">Ini adalah properti Anda.</p>
    @else
        <p class="font-semibold text-gray-900">Tertarik dengan properti ini?</p>

        @error('action')
            <p class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700">{{ $message }}</p>
        @enderror

        @guest
            <button type="button" wire:click="goLogin" class="w-full rounded-lg bg-blue-600 py-2.5 font-medium text-white hover:bg-blue-700">
                Masuk untuk chat &amp; memesan
            </button>
        @else
            @if ($isBuyer)
                <button type="button" wire:click="chat" wire:loading.attr="disabled" class="w-full rounded-lg border border-blue-600 py-2.5 font-medium text-blue-700 hover:bg-blue-50">
                    Chat penjual
                </button>
                <div class="rounded-xl bg-gray-50 p-3">
                    <p class="text-xs text-gray-500">Booking fee (tanda jadi)</p>
                    <p class="text-lg font-semibold text-gray-900"><x-rupiah :value="$fee" /></p>
                    <p class="mt-1 text-xs text-gray-500">Bayar via Virtual Account, kartu kredit (cicilan bank), atau e-wallet. Properti ditahan untuk Anda selama 24 jam setelah tagihan dibuat.</p>
                </div>
                <button type="button" wire:click="payFee" wire:loading.attr="disabled" class="w-full rounded-lg bg-blue-600 py-2.5 font-medium text-white hover:bg-blue-700">
                    <span wire:loading.remove wire:target="payFee">Bayar booking fee</span>
                    <span wire:loading wire:target="payFee">Menyiapkan tagihan...</span>
                </button>
            @else
                <p class="text-sm text-gray-500">Chat dan pemesanan tersedia untuk akun pembeli.</p>
            @endif
        @endguest
    @endif
</div>
