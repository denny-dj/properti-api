@php
    $input = 'w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:bg-white focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900 transition-all';
@endphp

{{-- ROOT ELEMENT TUNGGAL LIVEWIRE 3 --}}
<div class="space-y-6 pb-12">
    
    {{-- Header Editorial --}}
    <div class="flex flex-col justify-between gap-4 border-b border-slate-200/80 pb-6 sm:flex-row sm:items-end">
        <div>
            <nav class="mb-2 flex items-center gap-2 text-xs font-medium text-slate-400">
                <a href="{{ route('partner.dashboard') }}" class="hover:text-blue-600 transition-colors">Portal Mitra</a>
                <span>/</span>
                <span class="text-slate-700">Manajemen Pesanan</span>
            </nav>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">Pesanan desain</h1>
            <p class="mt-1 text-xs sm:text-sm text-slate-500">Desain siap bangun dan tanda jadi custom yang dibuat admin untuk klien Anda.</p>
        </div>

        {{-- Filter Tahap Pesanan --}}
        <div class="relative w-full sm:w-auto">
            <select wire:model.live="stage" 
                    class="w-full sm:w-auto appearance-none rounded-xl border border-slate-200 bg-white py-2 pl-3.5 pr-8 text-xs font-semibold text-slate-700 shadow-sm focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900 transition-all cursor-pointer">
                <option value="">Semua pesanan</option>
                <option value="awaiting_link">Perlu kirim berkas</option>
                <option value="awaiting_confirmation">Menunggu konfirmasi pembeli</option>
                <option value="released">Dana dilepas</option>
                <option value="unpaid">Menunggu pembayaran</option>
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-slate-400">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </div>
        </div>
    </div>

    {{-- KARTU RINGKASAN SALDO & ANTRIAN BERKAS --}}
    <div class="grid gap-4 sm:grid-cols-3">
        {{-- Antrean Kirim Berkas --}}
        <div class="rounded-3xl border border-amber-200/80 bg-amber-50/60 p-5 shadow-2xs space-y-1">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-amber-800">Perlu kirim berkas</span>
                <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-amber-200/70 text-amber-900">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <p class="font-mono text-2xl font-extrabold tracking-tight text-amber-900">{{ $awaiting }}</p>
            <p class="text-xs text-amber-700 pt-1">Pesanan lunas menunggu Google Drive</p>
        </div>

        {{-- Menunggu Dilepas --}}
        <div class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-2xs space-y-1">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Menunggu dilepas</span>
            <p class="font-mono text-2xl font-extrabold tracking-tight text-slate-900">
                <x-rupiah :value="$balance['pending_release']" />
            </p>
            <p class="text-xs text-slate-500 pt-1">Menunggu konfirmasi penerimaan pembeli</p>
        </div>

        {{-- Siap Dicairkan --}}
        <div class="rounded-3xl border border-emerald-200/80 bg-emerald-50/50 p-5 shadow-2xs space-y-1">
            <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-800">Siap dicairkan</span>
            <p class="font-mono text-2xl font-extrabold tracking-tight text-emerald-700">
                <x-rupiah :value="$balance['available']" />
            </p>
            <p class="text-xs text-emerald-800 pt-1">Saldo bersih siap ditransfer admin</p>
        </div>
    </div>

    <p class="text-xs text-slate-400 leading-relaxed -mt-2">
        Nilai di atas adalah hak bersih Anda setelah komisi platform. "Siap dicairkan" = dana yang sudah dikonfirmasi pembeli dikurangi pencairan yang telah ditransfer administrator.
    </p>

    {{-- KARTU LOG PENCAIRAN TERAKHIR (JIKA ADA) --}}
    @if ($payouts->isNotEmpty())
        <div class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm space-y-3">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <svg class="h-4 w-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-700">Pencairan terakhir</h2>
                </div>
                <span class="text-xs text-slate-500">Total tercairkan: <strong class="font-mono font-bold text-slate-900"><x-rupiah :value="$balance['paid_out']" /></strong></span>
            </div>
            
            <ul class="divide-y divide-slate-100 text-xs">
                @foreach ($payouts as $payout)
                    <li class="flex flex-wrap items-center justify-between gap-2 py-2.5">
                        <div class="flex items-center gap-2 text-slate-600">
                            <span class="font-mono text-slate-400">{{ $payout->paid_at->format('d/m/Y H:i') }}</span>
                            @if ($payout->reference)
                                <span class="text-slate-300">·</span>
                                <span class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[10px] text-slate-700">ref {{ $payout->reference }}</span>
                            @endif
                        </div>
                        <span class="font-mono font-bold text-emerald-600"><x-rupiah :value="$payout->amount" /></span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- PANDUAN PENYERAHAN BERKAS --}}
    <div class="rounded-3xl border border-blue-200/80 bg-blue-50/60 p-5 text-xs text-blue-950 space-y-2.5">
        <div class="flex items-center gap-2 text-blue-900 font-bold uppercase tracking-wider text-[11px]">
            <svg class="h-4 w-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>Cara menyerahkan berkas</span>
        </div>
        <ol class="list-decimal space-y-1 pl-4 text-blue-900/90 leading-relaxed font-medium">
            <li>Unggah berkas final (DED, 3D, RAB) ke folder Google Drive Anda.</li>
            <li>Bagikan folder dengan akses "Siapa saja yang memiliki link: Pelihat"[cite: 18].</li>
            <li>Tempel tautan folder di pesanan yang sudah lunas, lalu kirim. Dana dilepas setelah pembeli mengonfirmasi[cite: 18].</li>
        </ol>
    </div>

    {{-- Feedback Notifikasi & Galat --}}
    @if ($notice)
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs font-semibold text-emerald-800 shadow-sm flex items-center gap-2">
            <svg class="h-4 w-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>{{ $notice }}</span>
        </div>
    @endif

    @error('action')
        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-xs font-semibold text-rose-700 shadow-sm">
            {{ $message }}
        </div>
    @enderror

    {{-- DAFTAR PESANAN DESAIN --}}
    <div class="divide-y divide-slate-100 overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm transition-opacity duration-200"
         wire:loading.class="opacity-60">
        @forelse ($orders as $t)
            @php
                $status = $t->status->value;

                if ($status === 'paid' && $t->client_confirmed_at) {
                    [$label, $classes] = ['Dana dilepas', 'border-emerald-200/80 bg-emerald-50 text-emerald-800'];
                } elseif ($status === 'paid' && $t->deliverable_link) {
                    [$label, $classes] = ['Menunggu konfirmasi pembeli', 'border-blue-200/80 bg-blue-50 text-blue-800'];
                } elseif ($status === 'paid') {
                    [$label, $classes] = ['Perlu kirim berkas', 'border-amber-200/80 bg-amber-50 text-amber-800'];
                } elseif ($status === 'pending') {
                    [$label, $classes] = ['Menunggu pembayaran', 'border-slate-200 bg-slate-100 text-slate-700'];
                } else {
                    [$label, $classes] = [ucfirst($status), 'border-slate-200 bg-slate-100 text-slate-700'];
                }
            @endphp
            <div wire:key="order-{{ $t->id }}" class="space-y-4 p-5 sm:p-6 transition-colors hover:bg-slate-50/50">
                
                {{-- Baris Header Pesanan --}}
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0 flex-1 space-y-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="font-mono rounded-md bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-700">
                                {{ $t->code }}
                            </span>
                            <span class="rounded-lg border px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ $classes }}">
                                {{ $label }}
                            </span>
                        </div>
                        <h2 class="text-sm font-bold text-slate-900 tracking-tight">{{ $t->description }}</h2>
                        <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-slate-500">
                            <span>Klien: <strong class="font-medium text-slate-700">{{ $t->payer?->name ?? 'Anonim' }}</strong></span>
                            <span class="text-slate-300">·</span>
                            <span class="font-mono text-slate-400">{{ $t->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>

                    {{-- Rincian Nilai Finansial --}}
                    <div class="text-left sm:text-right shrink-0 border-t border-slate-100 pt-2 sm:border-t-0 sm:pt-0">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Hak Bersih Anda</span>
                        <p class="font-mono text-base sm:text-lg font-extrabold tracking-tight text-emerald-600">
                            <x-rupiah :value="$t->net_partner_amount" />
                        </p>
                        <p class="text-[11px] text-slate-400">
                            Nilai: <x-rupiah :value="$t->amount" /> · Komisi: <x-rupiah :value="$t->platform_fee" />
                        </p>
                    </div>
                </div>

                {{-- Status Tindakan & Pengiriman Berkas --}}
                @if ($status === 'pending')
                    <div class="rounded-2xl border border-slate-200/80 bg-slate-50 p-3.5 text-xs text-slate-600 flex items-start gap-2">
                        <svg class="h-4 w-4 text-slate-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Admin sudah mengirim tautan pembayaran ke klien{{ $t->expires_at ? ', berlaku hingga '.$t->expires_at->format('d/m/Y H:i') : '' }}. Berkas dapat dikirim setelah lunas[cite: 18].</span>
                    </div>
                @elseif ($status === 'paid' && $t->client_confirmed_at)
                    <div class="rounded-2xl border border-emerald-200/80 bg-emerald-50/60 p-4 text-xs space-y-2">
                        <div class="flex items-center gap-2 text-emerald-800 font-semibold">
                            <svg class="h-4 w-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Pembeli mengonfirmasi penerimaan pada {{ $t->client_confirmed_at->format('d/m/Y H:i') }}. Dana masuk ke saldo siap dicairkan[cite: 18].</span>
                        </div>
                        <div class="flex items-center gap-1.5 pt-1">
                            <span class="text-slate-400">Tautan Berkas:</span>
                            <a href="{{ $t->deliverable_link }}" target="_blank" rel="noopener noreferrer nofollow" class="inline-flex items-center gap-1 font-mono font-medium text-blue-600 hover:text-blue-700 hover:underline break-all">
                                <span>{{ $t->deliverable_link }}</span>
                                <svg class="h-3 w-3 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                        </div>
                    </div>
                @elseif ($status === 'paid')
                    <div class="rounded-2xl border border-slate-200/80 bg-slate-50/70 p-4 space-y-3">
                        @if ($t->deliverable_link)
                            <div class="flex flex-wrap items-center gap-1.5 text-xs text-slate-600 border-b border-slate-200/60 pb-2.5">
                                <span class="font-medium">Tautan terkirim {{ $t->deliverable_submitted_at?->format('d/m/Y H:i') }}:</span>
                                <a href="{{ $t->deliverable_link }}" target="_blank" rel="noopener noreferrer nofollow" class="inline-flex items-center gap-1 font-mono font-medium text-blue-600 hover:text-blue-700 hover:underline break-all">
                                    <span>{{ $t->deliverable_link }}</span>
                                    <svg class="h-3 w-3 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                </a>
                            </div>
                        @endif

                        <div class="flex flex-col sm:flex-row sm:items-start gap-2">
                            <div class="min-w-0 flex-1 sm:max-w-xl">
                                <input type="url" 
                                       wire:model="links.{{ $t->id }}" 
                                       placeholder="https://drive.google.com/drive/folders/..." 
                                       class="{{ $input }}">
                                @error('links.'.$t->id)<p class="mt-1 text-[11px] font-semibold text-rose-600">{{ $message }}</p>@enderror
                            </div>
                            <button type="button" 
                                    wire:click="submitLink({{ $t->id }})" 
                                    wire:loading.attr="disabled" 
                                    wire:target="submitLink({{ $t->id }})"
                                    class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-slate-900 px-4 py-2 text-xs font-bold text-white shadow hover:bg-slate-800 transition-all cursor-pointer shrink-0 disabled:opacity-50">
                                <svg class="h-3.5 w-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                <span>{{ $t->deliverable_link ? 'Ganti tautan' : 'Kirim tautan' }}</span>
                            </button>
                        </div>
                    </div>
                @endif

            </div>
        @empty
            <div class="p-12 text-center">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <h3 class="mt-4 text-sm font-bold text-slate-900">Belum ada pesanan desain pada kategori ini.</h3>
                <p class="mt-1 text-xs text-slate-500 max-w-sm mx-auto">Pesanan desain siap bangun atau pesanan custom dari klien yang diproses admin akan muncul di sini.</p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="pt-2">
        {{ $orders->links() }}
    </div>

</div>