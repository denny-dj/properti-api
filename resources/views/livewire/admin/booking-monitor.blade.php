{{-- ROOT ELEMENT TUNGGAL LIVEWIRE 3 --}}
<div class="space-y-6 pb-12">
    
    {{-- Header Editorial --}}
    <div class="flex flex-col justify-between gap-4 border-b border-slate-200/80 pb-6 sm:flex-row sm:items-end">
        <div>
            <nav class="mb-2 flex items-center gap-2 text-xs font-medium text-slate-400">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-blue-600 transition-colors">Pusat Kendali</a>
                <span>/</span>
                <span class="text-slate-700">Agenda Arsitek</span>
            </nav>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">Pesanan Konsultasi</h1>
            <p class="mt-1 text-xs sm:text-sm text-slate-500">Pemantauan lintas penjadwalan sesi tatap muka dan daring antara klien dan mitra arsitek.</p>
        </div>
    </div>

    {{-- Toolbar Filter & Pencarian Cepat --}}
    <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex flex-wrap items-center gap-3">
                
                {{-- Filter Status --}}
                <div class="relative w-full sm:w-auto">
                    <select wire:model.live="status" class="w-full sm:w-auto appearance-none rounded-xl border border-slate-200 bg-slate-50/50 py-2 pl-3.5 pr-8 text-xs font-semibold text-slate-700 shadow-sm focus:bg-white focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900 transition-all cursor-pointer">
                        <option value="">Semua status sesi</option>
                        <option value="pending">Menunggu konfirmasi</option>
                        <option value="confirmed">Dikonfirmasi</option>
                        <option value="completed">Selesai</option>
                        <option value="cancelled">Dibatalkan</option>
                        <option value="rejected">Ditolak</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-slate-400">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>

                {{-- Rentang Tanggal --}}
                <div class="flex flex-wrap items-center gap-2 text-xs text-slate-600">
                    <label class="flex items-center gap-1.5 font-medium">
                        <span>Dari</span>
                        <input type="date" wire:model.live="from" class="rounded-xl border border-slate-200 bg-slate-50/50 px-2.5 py-1.5 text-xs text-slate-700 focus:bg-white focus:border-slate-900 focus:outline-none">
                    </label>
                    <label class="flex items-center gap-1.5 font-medium">
                        <span>Sampai</span>
                        <input type="date" wire:model.live="to" class="rounded-xl border border-slate-200 bg-slate-50/50 px-2.5 py-1.5 text-xs text-slate-700 focus:bg-white focus:border-slate-900 focus:outline-none">
                    </label>
                </div>
            </div>

            {{-- Indikator Reaktif --}}
            <div wire:loading class="text-xs font-medium text-slate-400 flex items-center gap-1.5 self-start lg:self-auto">
                <svg class="h-3.5 w-3.5 animate-spin text-slate-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                <span>Menyinkronkan jadwal...</span>
            </div>
        </div>
    </div>

    {{-- Daftar Agenda Sesi Arsitek --}}
    <div class="divide-y divide-slate-100 overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm transition-opacity duration-200" 
         wire:loading.class="opacity-60">
        @forelse ($bookings as $b)
            @php $isPaid = in_array($b->id, $paidIds, true); @endphp
            <div wire:key="abk-{{ $b->id }}" class="flex flex-col gap-4 p-5 sm:flex-row sm:items-start sm:justify-between sm:p-6 transition-colors hover:bg-slate-50/50">
                
                {{-- Detail Agenda --}}
                <div class="min-w-0 flex-1 space-y-2">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="font-mono rounded-md bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-700">
                            {{ $b->code }}
                        </span>
                        <span class="rounded-lg border {{ $b->meeting_type->value === 'online' ? 'border-indigo-200/80 bg-indigo-50 text-indigo-700' : 'border-emerald-200/80 bg-emerald-50 text-emerald-700' }} px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider">
                            Sesi {{ $b->meeting_type->value === 'online' ? 'Online' : 'Di lokasi' }}
                        </span>
                        <x-status-badge :status="$b->status" context="booking" />
                    </div>

                    <h2 class="text-sm font-bold text-slate-900">{{ $b->service?->title ?? 'Layanan Tidak Diketahui' }}</h2>

                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-semibold text-slate-600">
                        <span class="flex items-center gap-1 text-slate-500">
                            <svg class="h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>{{ $b->scheduled_start->format('d/m/Y H:i') }} – {{ $b->scheduled_end->format('H:i') }}</span>
                        </span>
                        <span class="text-slate-300">·</span>
                        <span class="flex items-center gap-1">
                            <span class="text-slate-500">Klien:</span> 
                            <span class="text-slate-900">{{ $b->client?->name ?? 'Anonim' }}</span>
                        </span>
                        <span class="text-slate-300">·</span>
                        <span class="flex items-center gap-1">
                            <span class="text-slate-500">Arsitek:</span> 
                            <span class="text-slate-900">{{ $b->architect?->partnerProfile?->business_name ?? $b->architect?->name }}</span>
                        </span>
                    </div>

                    @if ($b->cancel_reason)
                        <div class="rounded-2xl border border-rose-200 bg-rose-50/70 p-3 mt-2 text-xs text-rose-800 flex items-start gap-2.5">
                            <svg class="h-4 w-4 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span class="leading-relaxed"><strong>Alasan Pembatalan:</strong> {{ $b->cancel_reason }}</span>
                        </div>
                    @endif
                </div>

                {{-- Nominal & Status Pembayaran --}}
                <div class="flex items-center justify-between sm:flex-col sm:items-end sm:justify-center border-t border-slate-100 pt-3 sm:border-t-0 sm:pt-0 shrink-0">
                    <p class="font-mono text-base sm:text-lg font-extrabold tracking-tight text-blue-700">
                        <x-rupiah :value="$b->price" />
                    </p>
                    <div class="mt-1 flex items-center gap-1.5 rounded-lg border {{ $isPaid ? 'border-emerald-200/80 bg-emerald-50' : 'border-amber-200/80 bg-amber-50' }} px-2 py-0.5">
                        <span class="flex h-1.5 w-1.5 rounded-full {{ $isPaid ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                        <span class="text-[10px] font-bold uppercase tracking-wider {{ $isPaid ? 'text-emerald-700' : 'text-amber-800' }}">
                            {{ $isPaid ? 'Telah Dibayar' : 'Belum Dibayar' }}
                        </span>
                    </div>
                </div>

            </div>
        @empty
            <div class="p-12 text-center">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h3 class="mt-4 text-sm font-bold text-slate-900">Tidak ada jadwal sesi pada rentang waktu ini.</h3>
                <p class="mt-1 text-xs text-slate-500 max-w-sm mx-auto">Sesuaikan filter status atau perlebar rentang tanggal untuk melihat daftar pemesanan sesi konsultasi arsitek.</p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="pt-2">
        {{ $bookings->links() }}
    </div>

</div>