{{-- ROOT ELEMENT TUNGGAL LIVEWIRE 3 --}}
<div class="space-y-8 pb-12">
    
    {{-- Header Editorial --}}
    <div class="flex flex-col justify-between gap-4 border-b border-slate-200/80 pb-6 sm:flex-row sm:items-end">
        <div>
            <nav class="mb-2 flex items-center gap-2 text-xs font-medium text-slate-400">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-blue-600 transition-colors">Pusat Kendali</a>
                <span>/</span>
                <span class="text-slate-700">Analisis Pendapatan</span>
            </nav>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">Pendapatan platform</h1>
            <p class="mt-1 text-xs sm:text-sm text-slate-500">Hanya transaksi lunas, berdasarkan tanggal pembayaran.</p>
        </div>

        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-1.5 text-xs font-bold text-slate-700 shadow-2xs">
                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                <span>Audit Finansial Riil</span>
            </span>
        </div>
    </div>

    {{-- Toolbar Filter Rentang & Tombol Preset Periode --}}
    <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div class="flex flex-wrap items-center gap-3">
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Dari</label>
                    <input type="date" 
                           wire:model.live="from" 
                           class="rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs font-semibold text-slate-700 focus:bg-white focus:border-slate-900 focus:outline-none transition-all">
                </div>
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Sampai</label>
                    <input type="date" 
                           wire:model.live="to" 
                           class="rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs font-semibold text-slate-700 focus:bg-white focus:border-slate-900 focus:outline-none transition-all">
                </div>
            </div>

            {{-- Tombol Preset Cepat --}}
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" 
                        wire:click="preset('month')" 
                        class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 hover:border-slate-300 hover:text-slate-900 transition-all cursor-pointer">
                    Bulan ini
                </button>
                <button type="button" 
                        wire:click="preset('last_month')" 
                        class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 hover:border-slate-300 hover:text-slate-900 transition-all cursor-pointer">
                    Bulan lalu
                </button>
                <button type="button" 
                        wire:click="preset('year')" 
                        class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 hover:border-slate-300 hover:text-slate-900 transition-all cursor-pointer">
                    Tahun ini
                </button>
                <button type="button" 
                        wire:click="preset('all')" 
                        class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 hover:border-slate-300 hover:text-slate-900 transition-all cursor-pointer">
                    Semua
                </button>
            </div>
        </div>
    </div>

    {{-- KARTU METRIK UTAMA PENDAPATAN (COMMAND PANEL) --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4 transition-opacity duration-200" wire:loading.class="opacity-60">
        {{-- Pembayaran Lunas --}}
        <div class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-2xs space-y-1">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Pembayaran Lunas</span>
            <p class="font-mono text-2xl font-extrabold tracking-tight text-slate-900">
                <x-rupiah :value="$summary['totals']['gross']" />
            </p>
            <p class="text-xs text-slate-400 pt-1 font-mono">
                {{ number_format($summary['totals']['transactions'], 0, ',', '.') }} transaksi
            </p>
        </div>

        {{-- Komisi Platform --}}
        <div class="rounded-3xl border border-emerald-200/80 bg-emerald-50/50 p-5 shadow-2xs space-y-1">
            <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-800">Komisi Platform</span>
            <p class="font-mono text-2xl font-extrabold tracking-tight text-emerald-700">
                <x-rupiah :value="$summary['totals']['platform_fee']" />
            </p>
            <p class="text-xs text-emerald-800 pt-1">
                Net fee pendapatan platform
            </p>
        </div>

        {{-- Hak Bersih Mitra --}}
        <div class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-2xs space-y-1">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Hak Bersih Mitra</span>
            <p class="font-mono text-2xl font-extrabold tracking-tight text-slate-900">
                <x-rupiah :value="$summary['totals']['net_partner_amount']" />
            </p>
            <p class="text-xs text-slate-500 pt-1">
                Alokasi pencairan mitra
            </p>
        </div>

        {{-- Saldo Mitra Belum Dicairkan --}}
        @if ($outstanding !== null)
            <a href="{{ route('admin.payouts') }}" 
               class="group rounded-3xl border border-amber-200/80 bg-amber-50/60 p-5 shadow-2xs space-y-1 transition-all hover:bg-amber-50 hover:shadow-md">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-amber-800">Saldo mitra belum dicairkan</span>
                    <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-amber-200/70 text-amber-900">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </span>
                </div>
                <p class="font-mono text-2xl font-extrabold tracking-tight text-amber-700">
                    <x-rupiah :value="$outstanding" />
                </p>
                <p class="text-xs text-amber-700 pt-1">
                    Posisi saat ini, bukan per periode
                </p>
            </a>
        @endif
    </div>

    {{-- KARTU STATUS ESCROW ARSITEK --}}
    <div class="grid gap-4 sm:grid-cols-2">
        <div class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-2xs space-y-1">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Escrow Tertahan</span>
            <p class="font-mono text-xl font-extrabold tracking-tight text-amber-600">
                <x-rupiah :value="$summary['architect_escrow']['pending_release']" />
            </p>
            <p class="text-xs text-slate-500 pt-1">Dana desain menunggu konfirmasi pembeli</p>
        </div>

        <div class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-2xs space-y-1">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Escrow Terselesaikan</span>
            <p class="font-mono text-xl font-extrabold tracking-tight text-emerald-600">
                <x-rupiah :value="$summary['architect_escrow']['released']" />
            </p>
            <p class="text-xs text-slate-500 pt-1">Dana desain sudah dilepas ke mitra</p>
        </div>
    </div>

    {{-- TABEL RINCIAN PENDAPATAN BERDASARKAN JENIS TRANSAKSI --}}
    <div class="overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-6 py-4">
            <h2 class="text-sm font-bold text-slate-900">Rincian Berdasarkan Jenis Transaksi</h2>
            <p class="text-xs text-slate-400">Komparasi nilai bruto, kontribusi komisi, dan proporsi pencairan kemitraan.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-xs">
                <thead class="bg-slate-50/70 text-left text-[10px] font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-3.5">Jenis</th>
                        <th class="px-6 py-3.5 text-right">Transaksi</th>
                        <th class="px-6 py-3.5 text-right">Nilai</th>
                        <th class="px-6 py-3.5 text-right">Komisi Platform</th>
                        <th class="px-6 py-3.5 text-right">Hak Mitra</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($summary['by_type'] as $row)
                        <tr class="transition-colors hover:bg-slate-50/50">
                            <td class="px-6 py-4 font-semibold text-slate-900">{{ $row['label'] }}</td>
                            <td class="px-6 py-4 text-right font-mono font-bold text-slate-700">{{ $row['transactions'] }}</td>
                            <td class="px-6 py-4 text-right font-mono font-extrabold text-slate-900"><x-rupiah :value="$row['gross']" /></td>
                            <td class="px-6 py-4 text-right font-mono font-extrabold text-emerald-600"><x-rupiah :value="$row['platform_fee']" /></td>
                            <td class="px-6 py-4 text-right font-mono font-bold text-slate-700"><x-rupiah :value="$row['net_partner_amount']" /></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-slate-400">
                                Belum ada transaksi lunas pada periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- CATATAN KETENTUAN FINANSIAL --}}
    @if (!empty($summary['notes']))
        <div class="rounded-2xl border border-slate-200/70 bg-slate-50/50 p-4 sm:p-5">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Catatan Akuntansi &amp; Kebijakan Escrow</h3>
            <ul class="space-y-1.5 text-xs text-slate-500">
                @foreach ($summary['notes'] as $note)
                    <li class="flex items-start gap-2">
                        <span class="text-slate-300 font-bold leading-tight">•</span>
                        <span class="leading-relaxed">{{ $note }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

</div>