@php
    $input = 'w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2.5 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:bg-white focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900 transition-all';
    $isProperty = $type === 'property_booking_fee';
@endphp

{{-- ROOT ELEMENT TUNGGAL LIVEWIRE 3 --}}
<div class="space-y-6 pb-12">
    
    {{-- Header Editorial --}}
    <div class="flex flex-col justify-between gap-4 border-b border-slate-200/80 pb-6 sm:flex-row sm:items-end">
        <div>
            <nav class="mb-2 flex items-center gap-2 text-xs font-medium text-slate-400">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-blue-600 transition-colors">Pusat Kendali</a>
                <span>/</span>
                <span class="text-slate-700">Billing Seketika</span>
            </nav>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">Invoice cepat</h1>
            <p class="mt-1 text-xs sm:text-sm text-slate-500">Buat tautan pembayaran resmi saat pembeli sudah sepakat di WhatsApp, lalu kirim pesannya.</p>
        </div>
    </div>

    {{-- Error Banner --}}
    @error('form')
        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-xs font-semibold text-rose-700 shadow-sm">
            {{ $message }}
        </div>
    @enderror

    @if ($result)
        {{-- KARTU HASIL INVOICE DIBUAT --}}
        <div class="space-y-6 rounded-3xl border border-emerald-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-100 pb-4">
                <div class="space-y-1">
                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-50 border border-emerald-200/80 px-2.5 py-1 text-[11px] font-bold text-emerald-800">
                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                        <span>{{ $result['reused'] ? 'Tagihan aktif sebelumnya digunakan kembali' : 'Tagihan dibuat' }}</span>
                    </span>
                    <h2 class="text-base font-bold text-slate-900">{{ $result['description'] }}</h2>
                </div>
                <div class="text-left sm:text-right">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Kode Tagihan</span>
                    <p class="font-mono text-sm font-extrabold text-slate-900">{{ $result['code'] }}</p>
                </div>
            </div>

            {{-- Ringkasan Parameter Finansial --}}
            <dl class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 text-xs">
                <div class="rounded-2xl border border-slate-200/80 bg-slate-50/60 p-3.5 shadow-2xs">
                    <dt class="font-bold uppercase tracking-wider text-[10px] text-slate-400">Pembeli</dt>
                    <dd class="mt-1 font-semibold text-slate-900">{{ $result['buyer_name'] }}</dd>
                </div>

                <div class="rounded-2xl border border-slate-200/80 bg-slate-50/60 p-3.5 shadow-2xs">
                    <dt class="font-bold uppercase tracking-wider text-[10px] text-slate-400">Mitra</dt>
                    <dd class="mt-1 font-semibold text-slate-900">{{ $result['partner_name'] }}</dd>
                </div>

                <div class="rounded-2xl border border-slate-200/80 bg-slate-50/60 p-3.5 shadow-2xs">
                    <dt class="font-bold uppercase tracking-wider text-[10px] text-slate-400">Nominal ditagih</dt>
                    <dd class="mt-1 font-mono font-bold text-emerald-600"><x-rupiah :value="$result['amount']" /></dd>
                </div>

                <div class="rounded-2xl border border-slate-200/80 bg-slate-50/60 p-3.5 shadow-2xs">
                    <dt class="font-bold uppercase tracking-wider text-[10px] text-slate-400">Berlaku hingga</dt>
                    <dd class="mt-1 font-mono font-semibold text-slate-900">{{ $result['expires_at'] }}</dd>
                </div>

                <div class="rounded-2xl border border-slate-200/80 bg-slate-50/60 p-3.5 shadow-2xs">
                    <dt class="font-bold uppercase tracking-wider text-[10px] text-slate-400">Komisi platform</dt>
                    <dd class="mt-1 font-semibold text-slate-900">
                        <span class="font-mono font-bold text-slate-900"><x-rupiah :value="$result['platform_fee']" /></span>
                        <span class="text-slate-500 font-normal">({{ $result['commission_percent'] }}%{{ $result['fee_basis'] === 'property_price' ? ' dari harga properti' : '' }})</span>
                    </dd>
                </div>

                <div class="rounded-2xl border border-slate-200/80 bg-slate-50/60 p-3.5 shadow-2xs">
                    <dt class="font-bold uppercase tracking-wider text-[10px] text-slate-400">Hak bersih mitra</dt>
                    <dd class="mt-1 font-mono font-bold text-slate-900"><x-rupiah :value="$result['net_partner_amount']" /></dd>
                </div>
            </dl>

            {{-- Pesan Siap Kirim WhatsApp --}}
            <div x-data="{ copied: false }" class="space-y-3 rounded-2xl border border-slate-200/80 bg-slate-50/60 p-4 sm:p-5">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">Pesan siap kirim</label>
                    <span class="text-[11px] text-slate-400">Format teks WhatsApp resmi</span>
                </div>
                
                <textarea x-ref="msg" 
                          readonly 
                          rows="8" 
                          class="w-full rounded-xl border border-slate-200 bg-white p-3.5 font-mono text-xs text-slate-800 shadow-2xs focus:outline-none">{{ $result['wa_message'] }}</textarea>
                
                <div class="flex flex-wrap items-center gap-2 pt-1">
                    <button type="button" 
                            x-on:click="navigator.clipboard.writeText($refs.msg.value); copied = true; setTimeout(() => copied = false, 2000)"
                            class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 hover:border-slate-300 transition-all cursor-pointer">
                        <svg class="h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                        <span x-show="!copied">Salin pesan</span>
                        <span x-show="copied" x-cloak class="text-emerald-600 font-bold">Tersalin!</span>
                    </button>

                    <a href="{{ $result['wa_url'] }}" 
                       target="_blank" 
                       rel="noopener" 
                       class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-4 py-2 text-xs font-bold text-white shadow hover:bg-emerald-700 transition-all">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        <span>Kirim via WhatsApp</span>
                    </a>

                    <a href="{{ $result['payment_url'] }}" 
                       target="_blank" 
                       rel="noopener" 
                       class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 transition-all">
                        <span>Buka halaman bayar</span>
                        <svg class="h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>

                    <button type="button" 
                            wire:click="createAnother" 
                            class="inline-flex items-center rounded-xl px-4 py-2 text-xs font-semibold text-slate-500 hover:text-slate-900 transition-colors cursor-pointer">
                        <span>Buat invoice lain</span>
                    </button>
                </div>

                <p class="text-[11px] text-slate-400 pt-1">
                    Tombol WhatsApp membuka chat ke nomor pembeli bila tersimpan di akunnya; jika tidak, pilih kontak secara manual.
                </p>
            </div>
        </div>
    @else
        {{-- FORMULIR PEMBUATAN INVOICE --}}
        <form wire:submit="submit" class="space-y-5 rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm">
            
            @if ($leadInfo)
                <div class="rounded-2xl border border-blue-200 bg-blue-50/70 px-4 py-3 text-xs text-blue-900 flex items-center gap-2">
                    <svg class="h-4 w-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Prospek <strong class="font-mono font-bold">{{ $leadCode }}</strong> · {{ $leadInfo }}</span>
                </div>
            @endif

            {{-- Jenis Tagihan --}}
            <div class="space-y-1.5">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">Jenis tagihan</label>
                <div class="relative">
                    <select wire:model.live="type" class="{{ $input }} appearance-none pr-8 cursor-pointer">
                        <option value="property_booking_fee">Booking fee properti</option>
                        <option value="architect_blueprint">Arsitek: paket desain siap bangun (Jalur A)</option>
                        <option value="architect_custom">Arsitek: tanda jadi custom/survei (Jalur B)</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
            </div>

            {{-- Email Pembeli & ID Item --}}
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">Email pembeli (akun terdaftar)</label>
                    <input type="email" wire:model="buyerEmail" class="{{ $input }}" placeholder="pembeli@email.com">
                    @error('buyerEmail')<p class="text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                </div>
                
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">{{ $isProperty ? 'ID properti' : 'ID portofolio' }}</label>
                    <input type="text" inputmode="numeric" wire:model.live.debounce.500ms="itemId" class="{{ $input }}" placeholder="mis. 12">
                    @error('itemId')<p class="text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Parameter Nominal, Komisi, Masa Berlaku --}}
            <div class="grid gap-4 sm:grid-cols-3">
                @if ($type !== 'architect_blueprint')
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">{{ $isProperty ? 'Nominal booking fee (opsional)' : 'Nominal tanda jadi' }}</label>
                        <input type="text" inputmode="numeric" wire:model.live.debounce.500ms="amount" class="{{ $input }}" placeholder="{{ $isProperty ? 'kosong = aturan bawaan' : '3000000' }}">
                        @error('amount')<p class="text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                    </div>
                @endif

                @unless ($isProperty)
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">Komisi arsitek (%)</label>
                        <input type="text" inputmode="decimal" wire:model.live.debounce.500ms="commission" class="{{ $input }}" placeholder="{{ config('concierge.fees.architect_default_percent') }}">
                        <p class="text-[10px] text-slate-400">Rentang {{ config('concierge.fees.architect_min_percent') }}-{{ config('concierge.fees.architect_max_percent') }}%.</p>
                        @error('commission')<p class="text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                    </div>
                @endunless

                <div class="space-y-1.5">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">Masa berlaku (jam)</label>
                    <input type="text" inputmode="numeric" wire:model="expiresHours" class="{{ $input }}" placeholder="24">
                    @error('expiresHours')<p class="text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Kode Prospek --}}
            <div class="space-y-1.5">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">Kode prospek (opsional)</label>
                <input type="text" wire:model="leadCode" class="{{ $input }} sm:max-w-xs" placeholder="LEAD-2026-XXXX">
                @error('leadCode')<p class="text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
            </div>

            {{-- LIVE PREVIEW DATA PROPERTI / PORTOFOLIO --}}
            @if ($preview)
                <div class="rounded-2xl border border-slate-200/90 bg-slate-50/70 p-4 sm:p-5 text-xs space-y-3">
                    <div class="flex items-start justify-between gap-3 border-b border-slate-200/60 pb-3">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Pratinjau Objek Terpilih</span>
                            <h3 class="text-sm font-bold text-slate-900 mt-0.5">{{ $preview['title'] }}</h3>
                            <p class="text-slate-500 mt-0.5">Milik {{ $preview['owner'] }} · <span class="font-semibold text-slate-700">status {{ $preview['status'] }}</span></p>
                        </div>
                    </div>

                    <dl class="space-y-2">
                        @foreach ($preview['lines'] as [$label, $value])
                            <div class="flex items-center justify-between text-slate-600">
                                <dt>{{ $label }}</dt>
                                <dd class="font-mono font-bold text-slate-900"><x-rupiah :value="$value" /></dd>
                            </div>
                        @endforeach
                    </dl>

                    @if ($preview['warning'])
                        <div class="rounded-xl border border-amber-200 bg-amber-50 px-3.5 py-2.5 text-xs font-medium text-amber-800">
                            {{ $preview['warning'] }}
                        </div>
                    @endif
                </div>
            @endif

            <p class="text-xs text-slate-400 leading-relaxed">
                Pembeli harus sudah memiliki akun. Invoice dari admin tidak mewajibkan email terverifikasi karena identitas sudah dikonfirmasi lewat WhatsApp.
            </p>

            {{-- Tombol Submit --}}
            <div class="pt-2">
                <button type="submit" 
                        wire:loading.attr="disabled" 
                        wire:target="submit" 
                        class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-5 py-2.5 text-xs font-bold text-white shadow hover:bg-slate-800 transition-all cursor-pointer disabled:opacity-50">
                    <span wire:loading.remove wire:target="submit">Buat tagihan</span>
                    <span wire:loading wire:target="submit" class="flex items-center gap-1.5">
                        <svg class="h-3.5 w-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        <span>Membuat...</span>
                    </span>
                </button>
            </div>
        </form>
    @endif

</div>