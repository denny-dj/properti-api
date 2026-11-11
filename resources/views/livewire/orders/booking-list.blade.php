{{-- ROOT ELEMENT TUNGGAL LIVEWIRE 3 --}}
<div class="mx-auto max-w-5xl px-4 sm:px-6 py-8 sm:py-10 space-y-8 pb-16">
    
    {{-- Header Editorial Pembeli --}}
    <div class="flex flex-col justify-between gap-4 border-b border-slate-200/80 pb-6 sm:flex-row sm:items-end">
        <div>
            <nav class="mb-2 flex items-center gap-2 text-xs font-medium text-slate-400">
                <a href="{{ url('/') }}" class="hover:text-blue-600 transition-colors">Beranda</a>
                <span>/</span>
                <span class="text-slate-700">Aktivitas Saya</span>
            </nav>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">Pesanan konsultasi</h1>
            <p class="mt-1 text-xs sm:text-sm text-slate-500">Pantau jadwal temu sesi konsultasi arsitek, detail pertemuan daring atau tatap muka, serta invoice resmi Anda.</p>
        </div>

        {{-- Filter Status Pesanan --}}
        <div class="relative w-full sm:w-auto">
            <select wire:model.live="status" 
                    class="w-full sm:w-auto appearance-none rounded-xl border border-slate-200 bg-white py-2.5 pl-4 pr-9 text-xs font-semibold text-slate-700 shadow-2xs focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900 transition-all cursor-pointer">
                <option value="">Semua status pesanan</option>
                <option value="pending">Menunggu konfirmasi</option>
                <option value="confirmed">Dikonfirmasi</option>
                <option value="completed">Selesai</option>
                <option value="cancelled">Dibatalkan</option>
                <option value="rejected">Ditolak</option>
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- DAFTAR PESANAN KONSULTASI PEMBELI --}}
    <div class="divide-y divide-slate-100 overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm transition-opacity duration-200"
         wire:loading.class="opacity-60">
        @forelse ($bookings as $booking)
            <a href="{{ route('orders.show', $booking->id) }}" 
               wire:key="booking-{{ $booking->id }}" 
               class="group flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6 transition-all hover:bg-slate-50/60">
                
                {{-- Detail Sesi & Arsitek --}}
                <div class="min-w-0 flex-1 space-y-2">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="font-mono rounded-md bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-700">
                            {{ $booking->code }}
                        </span>
                        <span class="rounded-lg border px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ $booking->meeting_type->value === 'online' ? 'border-indigo-200/80 bg-indigo-50 text-indigo-700' : 'border-emerald-200/80 bg-emerald-50 text-emerald-700' }}">
                            Sesi {{ $booking->meeting_type->value === 'online' ? 'Online' : 'Di lokasi' }}
                        </span>
                        <x-status-badge :status="$booking->status" context="booking" />
                    </div>

                    <div>
                        <h2 class="text-sm sm:text-base font-bold text-slate-900 group-hover:text-blue-600 transition-colors tracking-tight">
                            {{ $booking->service?->title ?? 'Layanan Konsultasi Arsitek' }}
                        </h2>
                        <p class="mt-0.5 text-xs text-slate-500">
                            Mitra Arsitek: <span class="font-semibold text-slate-700">{{ $booking->architect?->partnerProfile?->business_name ?? $booking->architect?->name }}</span>
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-1.5 text-xs text-slate-500 font-medium">
                        <svg class="h-3.5 w-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="text-slate-700 font-semibold">{{ $booking->scheduled_start->format('d/m/Y H:i') }} WIB</span>
                    </div>
                </div>

                {{-- Tagihan & Indikator Navigasi --}}
                <div class="flex items-center justify-between sm:flex-col sm:items-end sm:justify-center border-t border-slate-100 pt-3 sm:border-t-0 sm:pt-0 shrink-0">
                    <div class="text-left sm:text-right">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Tarif</span>
                        <p class="font-mono text-base sm:text-lg font-extrabold tracking-tight text-slate-900">
                            <x-rupiah :value="$booking->price" />
                        </p>
                    </div>

                    <div class="flex items-center gap-1.5 text-xs font-semibold text-blue-600 sm:mt-2 group-hover:translate-x-0.5 transition-transform">
                        <span>Lihat detail</span>
                        <svg class="h-3.5 w-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </div>
            </a>
        @empty
            <div class="p-12 text-center">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h3 class="mt-4 text-sm font-bold text-slate-900">Belum ada pesanan konsultasi.</h3>
                <p class="mt-1 text-xs text-slate-500 max-w-sm mx-auto">Mulai wujudkan hunian impian Anda dengan berkonsultasi langsung bersama mitra arsitek bersertifikat kami.</p>
                <div class="mt-5">
                    <a href="{{ route('architects.index') }}" 
                       class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-5 py-2.5 text-xs font-bold text-white shadow hover:bg-slate-800 transition-all">
                        <span>Cari layanan arsitek</span>
                        <svg class="h-3.5 w-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </a>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="pt-2">
        {{ $bookings->links() }}
    </div>

</div>