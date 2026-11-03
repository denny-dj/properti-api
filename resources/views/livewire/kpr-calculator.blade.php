<div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
    <h2 class="text-lg font-semibold">Simulasi KPR</h2>

    <div class="mt-4 space-y-4">
        <div>
            <label class="block text-sm font-medium">Harga properti (Rp)</label>
            <input type="text" inputmode="numeric" wire:model.live.debounce.400ms="price" @if ($lockPrice) readonly @endif
                   class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 {{ $lockPrice ? 'bg-gray-50' : '' }}" placeholder="mis. 850000000">
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-sm font-medium">Uang muka (%)</label>
                <input type="text" inputmode="decimal" wire:model.live.debounce.400ms="dpPercent" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium">Tenor (tahun)</label>
                <input type="text" inputmode="numeric" wire:model.live.debounce.400ms="tenorYears" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2">
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium">Suku bunga (% per tahun)</label>
            <input type="text" inputmode="decimal" wire:model.live.debounce.400ms="interest" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2">
        </div>

        <label class="flex items-center gap-2 text-sm text-gray-700">
            <input type="checkbox" wire:model.live="useFixed" class="rounded border-gray-300">
            Bunga tetap beberapa tahun, lalu floating
        </label>

        @if ($useFixed)
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium">Masa bunga tetap (tahun)</label>
                    <input type="text" inputmode="numeric" wire:model.live.debounce.400ms="fixedYears" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium">Bunga floating (%)</label>
                    <input type="text" inputmode="decimal" wire:model.live.debounce.400ms="floatingRate" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2">
                </div>
            </div>
        @endif
    </div>

    <div class="mt-5" wire:loading.class="opacity-50">
        @if ($error)
            <p class="rounded-lg bg-amber-50 px-3 py-2 text-sm text-amber-800">{{ $error }}</p>
        @elseif ($result)
            <div class="rounded-xl bg-blue-50 p-4">
                <p class="text-sm text-blue-800">Estimasi cicilan per bulan</p>
                <p class="mt-1 text-3xl font-bold text-blue-700">Rp {{ number_format($result['monthly_payment'], 0, ',', '.') }}</p>
                @if (count($result['phases']) > 1)
                    <p class="mt-1 text-xs text-blue-800">
                        Setelah {{ $result['phases'][0]['months'] / 12 }} tahun: Rp {{ number_format($result['phases'][1]['monthly_payment'], 0, ',', '.') }} per bulan (bunga {{ $result['phases'][1]['annual_rate'] }}%)
                    </p>
                @endif
            </div>
            <dl class="mt-4 space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Uang muka ({{ $result['down_payment_percent'] }}%)</dt><dd class="font-medium">Rp {{ number_format($result['down_payment'], 0, ',', '.') }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Jumlah pinjaman</dt><dd class="font-medium">Rp {{ number_format($result['loan_amount'], 0, ',', '.') }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Total bunga</dt><dd class="font-medium">Rp {{ number_format($result['total_interest'], 0, ',', '.') }}</dd></div>
                <div class="flex justify-between border-t border-gray-100 pt-2"><dt class="text-gray-500">Total pembayaran (pokok + bunga)</dt><dd class="font-medium">Rp {{ number_format($result['total_payment'], 0, ',', '.') }}</dd></div>
            </dl>
            <p class="mt-3 text-xs text-gray-400">Perhitungan metode anuitas; hanya estimasi dan bukan penawaran resmi bank. Biaya provisi, asuransi, dan administrasi belum termasuk.</p>

            {{-- Ajukan KPR via bank rekanan --}}
            <div class="mt-5 border-t border-gray-100 pt-4">
                @if ($kprLead)
                    <div class="rounded-xl bg-green-50 p-4 text-sm text-green-900">
                        <p class="font-semibold">Pengajuan tercatat: {{ $kprLead['code'] }}</p>
                        <p class="mt-1">Tim kami akan menghubungi Anda untuk meneruskan data ke bank rekanan. Anda juga dapat lanjut langsung lewat WhatsApp.</p>
                        <a href="{{ $kprLead['wa_url'] }}" target="_blank" rel="noopener" class="mt-3 inline-block rounded-lg bg-green-600 px-4 py-2 font-medium text-white hover:bg-green-700">Lanjut via WhatsApp</a>
                    </div>
                @elseif (! $showInterest)
                    <button type="button" wire:click="$set('showInterest', true)" class="w-full rounded-lg border border-blue-600 py-2.5 font-medium text-blue-700 hover:bg-blue-50">
                        Ajukan KPR via bank rekanan
                    </button>
                @else
                    <div class="space-y-3">
                        <p class="text-sm font-medium text-gray-900">Data pengajuan</p>
                        <p class="text-xs text-gray-500">Plafon dan tenor diambil dari simulasi di atas.</p>

                        <div>
                            <label class="block text-xs font-medium text-gray-600">Nama lengkap</label>
                            <input type="text" wire:model="leadName" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            @error('leadName')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600">Nomor WhatsApp</label>
                            <input type="text" inputmode="tel" wire:model="leadPhone" placeholder="081234567890" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            @error('leadPhone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600">Penghasilan per bulan (Rp)</label>
                            <input type="text" inputmode="numeric" wire:model="monthlyIncome" placeholder="25000000" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            @error('monthlyIncome')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <label class="flex items-start gap-2 text-xs text-gray-600">
                            <input type="checkbox" wire:model="consent" class="mt-0.5 rounded border-gray-300">
                            <span>Saya menyetujui data di atas diteruskan kepada bank rekanan untuk keperluan pengajuan KPR.</span>
                        </label>
                        @error('consent')<p class="text-xs text-red-600">{{ $message }}</p>@enderror

                        <button type="button" wire:click="submitInterest" wire:loading.attr="disabled" class="w-full rounded-lg bg-blue-600 py-2.5 font-medium text-white hover:bg-blue-700">
                            <span wire:loading.remove wire:target="submitInterest">Kirim pengajuan</span>
                            <span wire:loading wire:target="submitInterest">Mengirim...</span>
                        </button>
                    </div>
                @endif
            </div>
        @else
            <p class="text-sm text-gray-500">Isi harga properti untuk melihat estimasi cicilan.</p>
        @endif
    </div>
</div>
