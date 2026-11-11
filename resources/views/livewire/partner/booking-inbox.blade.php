{{-- ROOT ELEMENT TUNGGAL LIVEWIRE 3 --}}
<div class="space-y-6 pb-12">
    
    {{-- Header Editorial --}}
    <div class="flex flex-col justify-between gap-4 border-b border-slate-200/80 pb-6 sm:flex-row sm:items-end">
        <div>
            <nav class="mb-2 flex items-center gap-2 text-xs font-medium text-slate-400">
                <a href="{{ route('partner.dashboard') }}" class="hover:text-blue-600 transition-colors">Portal Mitra</a>
                <span>/</span>
                <span class="text-slate-700">Agenda Masuk</span>
            </nav>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">Pesanan masuk</h1>
            <p class="mt-1 text-xs sm:text-sm text-slate-500">Konfirmasi reservasi sesi konsultasi klien, peninjauan jadwal temu, dan status pembayaran.</p>
        </div>

        {{-- Filter Status --}}
        <div class="relative w-full sm:w-auto">
            <select wire:model.live="status" 
                    class="w-full sm:w-auto appearance-none rounded-xl border border-slate-200 bg-white py-2 pl-3.5 pr-8 text-xs font-semibold text-slate-700 shadow-sm focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900 transition-all cursor-pointer">
                <option value="">Semua status</option>
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
    </div>

    {{-- Feedback Notifikasi & Galat --}}
    @if ($notice)
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs font-semibold text-emerald-800 shadow-sm flex items-center gap-2">
            <svg class="h-4 w-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span>{{ $notice }}</span>
        </div>
    @endif

    @error('list')
        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-xs font-semibold text-rose-700 shadow-sm">
            {{ $message }}
        </div>
    @enderror

    {{-- DAFTAR PESANAN KONSULTASI MASUK --}}
    <div class="divide-y divide-slate-100 overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm transition-opacity duration-200"
         wire:loading.class="opacity-60">
        @forelse ($bookings as $b)
            @php
                $isPaid = in_array($b->id, $paidIds, true);
                $started = $b->scheduled_start->isPast();
                $open = in_array($b->status->value, ['pending', 'confirmed'], true);
            @endphp
            <div wire:key="bk-{{ $b->id }}" class="space-y-4 p-5 sm:p-6 transition-colors hover:bg-slate-50/50">
                
                {{-- Baris Atas: Informasi Layanan & Finansial --}}
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0 flex-1 space-y-1.5">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="font-mono rounded-md bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-700">
                                {{ $b->code }}
                            </span>
                            <span class="rounded-lg border px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ $b->meeting_type->value === 'online' ? 'border-indigo-200/80 bg-indigo-50 text-indigo-700' : 'border-emerald-200/80 bg-emerald-50 text-emerald-700' }}">
                                Sesi {{ $b->meeting_type->value === 'online' ? 'Online' : 'Di lokasi' }}
                            </span>
                            <x-status-badge :status="$b->status" context="booking" />
                        </div>

                        <h2 class="text-sm sm:text-base font-bold text-slate-900 tracking-tight">
                            {{ $b->service?->title ?? 'Layanan Konsultasi' }}
                        </h2>

                        <div class="flex flex-wrap items-center gap-1.5 text-xs text-slate-500 font-medium">
                            <svg class="h-3.5 w-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="text-slate-700 font-semibold">{{ $b->scheduled_start->format('d/m/Y H:i') }} – {{ $b->scheduled_end->format('H:i') }}</span>
                        </div>
                    </div>

                    {{-- Tagihan & Status Pembayaran --}}
                    <div class="flex items-center justify-between sm:flex-col sm:items-end sm:justify-center border-t border-slate-100 pt-3 sm:border-t-0 sm:pt-0 shrink-0">
                        <p class="font-mono text-base sm:text-lg font-extrabold tracking-tight text-slate-900">
                            <x-rupiah :value="$b->price" />
                        </p>
                        <div class="mt-1 flex items-center gap-1.5 rounded-lg border {{ $isPaid ? 'border-emerald-200/80 bg-emerald-50' : 'border-amber-200/80 bg-amber-50' }} px-2 py-0.5">
                            <span class="flex h-1.5 w-1.5 rounded-full {{ $isPaid ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                            <span class="text-[10px] font-bold uppercase tracking-wider {{ $isPaid ? 'text-emerald-700' : 'text-amber-800' }}">
                                {{ $isPaid ? 'Sudah dibayar' : 'Belum dibayar' }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Lembar Rincian Klien & Catatan Sesi --}}
                <div class="rounded-2xl border border-slate-200/80 bg-slate-50/70 p-4 text-xs space-y-2">
                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-slate-600">
                        <span class="text-slate-400 font-semibold uppercase text-[10px] tracking-wider">Klien:</span>
                        <span class="font-bold text-slate-900">{{ $b->client?->name ?? 'Anonim' }}</span>
                        @if ($b->client?->phone)
                            <span class="text-slate-300">·</span>
                            <span class="font-mono text-slate-600">{{ $b->client->phone }}</span>
                        @endif
                    </div>

                    @if ($b->location)
                        <div class="flex items-start gap-1.5 text-slate-600">
                            <span class="text-slate-400 font-semibold uppercase text-[10px] tracking-wider shrink-0 mt-0.5">Lokasi:</span>
                            <span class="text-slate-800 font-medium">{{ $b->location }}</span>
                        </div>
                    @endif

                    @if ($b->notes)
                        <div class="flex items-start gap-1.5 text-slate-600 pt-1 border-t border-slate-200/60">
                            <span class="text-slate-400 font-semibold uppercase text-[10px] tracking-wider shrink-0 mt-0.5">Catatan:</span>
                            <p class="whitespace-pre-line text-slate-700 font-medium leading-relaxed">{{ $b->notes }}</p>
                        </div>
                    @endif

                    @if ($b->cancel_reason)
                        <div class="flex items-start gap-1.5 text-rose-800 pt-1 border-t border-rose-200/60">
                            <span class="font-bold uppercase text-[10px] tracking-wider shrink-0 mt-0.5">Alasan:</span>
                            <p class="font-medium leading-relaxed">{{ $b->cancel_reason }}</p>
                        </div>
                    @endif
                </div>

                {{-- Tombol Aksi Status Pesanan --}}
                @if ($open)
                    <div class="flex flex-wrap items-center gap-2 pt-1 text-xs">
                        @if ($b->status->value === 'pending')
                            <button type="button" 
                                    wire:click="confirm({{ $b->id }})" 
                                    class="inline-flex items-center gap-1.5 rounded-xl bg-slate-900 px-4 py-2 font-bold text-white shadow hover:bg-slate-800 transition-all cursor-pointer">
                                <svg class="h-3.5 w-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>Konfirmasi</span>
                            </button>
                            <button type="button" 
                                    wire:click="startAction({{ $b->id }}, 'reject')" 
                                    class="inline-flex items-center gap-1.5 rounded-xl border border-rose-200 bg-rose-50/70 px-3.5 py-2 font-semibold text-rose-700 hover:bg-rose-100 transition-all cursor-pointer">
                                <span>Tolak</span>
                            </button>
                        @endif

                        @if ($b->status->value === 'confirmed')
                            @if ($started)
                                <button type="button" 
                                        wire:click="complete({{ $b->id }})" 
                                        wire:confirm="Tandai sesi ini selesai?" 
                                        class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-4 py-2 font-bold text-white shadow hover:bg-emerald-700 transition-all cursor-pointer">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    <span>Selesaikan</span>
                                </button>
                            @else
                                <span class="inline-flex items-center rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2 text-xs font-semibold text-slate-400">
                                    Bisa diselesaikan setelah sesi dimulai
                                </span>
                            @endif

                            <button type="button" 
                                    wire:click="startAction({{ $b->id }}, 'cancel')" 
                                    class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-3.5 py-2 font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 hover:border-slate-300 transition-all cursor-pointer">
                                <span>Batalkan</span>
                            </button>
                        @endif
                    </div>
                @endif

                {{-- FORMULIR TINDAKAN PENOLAKAN / PEMBATALAN --}}
                @if ($actionId === $b->id)
                    <form wire:submit="submitAction" 
                          class="space-y-3 rounded-2xl border border-rose-200 bg-rose-50/60 p-4 sm:p-5 shadow-2xs">
                        <div class="flex items-center gap-2">
                            <svg class="h-4 w-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <label class="block text-xs font-bold uppercase tracking-wider text-rose-950">
                                {{ $actionType === 'reject' ? 'Alasan penolakan' : 'Alasan pembatalan (opsional)' }}
                            </label>
                        </div>

                        <textarea rows="2.5" 
                                  wire:model="reason" 
                                  class="w-full rounded-xl border border-rose-200 bg-white px-3.5 py-2 text-xs text-slate-800 placeholder-slate-400 focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500 shadow-2xs"
                                  placeholder="Tuliskan alasan agar dapat dipahami oleh klien..."></textarea>
                        
                        @error('reason')<p class="text-xs font-semibold text-rose-700">{{ $message }}</p>@enderror

                        @if ($actionType === 'cancel' && $isPaid)
                            <p class="text-xs text-rose-800 leading-relaxed font-medium">
                                Pesanan ini sudah dibayar. Pengembalian dana diproses manual oleh administrator.
                            </p>
                        @endif

                        <div class="flex items-center gap-2 pt-1">
                            <button type="submit" 
                                    class="inline-flex items-center rounded-xl bg-rose-600 px-4 py-2 text-xs font-bold text-white shadow hover:bg-rose-700 transition-all cursor-pointer">
                                <span>{{ $actionType === 'reject' ? 'Tolak pesanan' : 'Batalkan pesanan' }}</span>
                            </button>
                            <button type="button" 
                                    wire:click="dismissAction" 
                                    class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 transition-all cursor-pointer">
                                <span>Tutup</span>
                            </button>
                        </div>
                    </form>
                @endif

            </div>
        @empty
            <div class="p-12 text-center">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h3 class="mt-4 text-sm font-bold text-slate-900">Belum ada pesanan masuk.</h3>
                <p class="mt-1 text-xs text-slate-500 max-w-sm mx-auto">Pesanan sesi konsultasi arsitektur dari klien akan muncul di sini untuk dikonfirmasi.</p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="pt-2">
        {{ $bookings->links() }}
    </div>

</div>