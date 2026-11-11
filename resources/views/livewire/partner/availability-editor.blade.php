@php
    $days = [
        1 => 'Senin', 
        2 => 'Selasa', 
        3 => 'Rabu', 
        4 => 'Kamis', 
        5 => 'Jumat', 
        6 => 'Sabtu', 
        7 => 'Minggu'
    ];
@endphp

{{-- ROOT ELEMENT TUNGGAL LIVEWIRE 3 --}}
<div class="space-y-6 pb-12">
    
    {{-- Header Editorial --}}
    <div class="flex flex-col justify-between gap-4 border-b border-slate-200/80 pb-6 sm:flex-row sm:items-end">
        <div>
            <nav class="mb-2 flex items-center gap-2 text-xs font-medium text-slate-400">
                <a href="{{ route('partner.dashboard') }}" class="hover:text-blue-600 transition-colors">Portal Mitra</a>
                <span>/</span>
                <span class="text-slate-700">Jadwal Kerja</span>
            </nav>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">Jadwal kerja</h1>
            <p class="mt-1 text-xs sm:text-sm text-slate-500">Atur jam praktik mingguan Anda. Klien hanya dapat memesan slot konsultasi di dalam rentang waktu yang aktif.</p>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" 
                    wire:click="weekdays" 
                    class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 hover:border-slate-300 transition-all cursor-pointer">
                <svg class="h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Isi Senin–Jumat 09:00–16:00</span>
            </button>
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

    @error('rows')
        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-xs font-semibold text-rose-700 shadow-sm">
            {{ $message }}
        </div>
    @enderror

    {{-- FORMULIR JADWAL HARIAN --}}
    <form wire:submit="save" class="space-y-4">
        <div class="grid gap-3 sm:gap-4">
            @foreach ($days as $dayNumber => $dayName)
                @php
                    $dayRows = collect($rows)->filter(fn($r) => (int) $r['day'] === $dayNumber);
                    $hasRows = $dayRows->isNotEmpty();
                @endphp

                <div class="rounded-3xl border border-slate-200/80 bg-white p-5 sm:p-6 shadow-sm transition-all hover:border-slate-300/80">
                    
                    {{-- Baris Header Hari --}}
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2.5">
                            <span class="text-sm font-bold text-slate-900">{{ $dayName }}</span>
                            <span class="rounded-lg border px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ $hasRows ? 'border-emerald-200/80 bg-emerald-50 text-emerald-700' : 'border-slate-200 bg-slate-100 text-slate-500' }}">
                                {{ $hasRows ? $dayRows->count() . ' Rentang Waktu' : 'Libur' }}
                            </span>
                        </div>

                        <button type="button" 
                                wire:click="addRange({{ $dayNumber }})" 
                                class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-slate-50/70 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100 hover:border-slate-300 transition-all cursor-pointer">
                            <svg class="h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            <span>Tambah jam</span>
                        </button>
                    </div>

                    {{-- Daftar Slot Waktu pada Hari Ini --}}
                    @if ($hasRows)
                        <div class="mt-3.5 space-y-2.5">
                            @foreach ($rows as $i => $row)
                                @if ((int) $row['day'] === $dayNumber)
                                    <div wire:key="row-{{ $i }}" class="flex flex-wrap items-center gap-2.5 rounded-2xl border border-slate-200/80 bg-slate-50/60 p-2.5 sm:w-fit">
                                        <div class="flex items-center gap-2">
                                            <input type="time" 
                                                   wire:model="rows.{{ $i }}.start" 
                                                   class="rounded-xl border border-slate-200 bg-white px-3 py-1.5 font-mono text-xs font-semibold text-slate-800 shadow-2xs focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900">
                                            
                                            <span class="text-xs font-medium text-slate-400">sampai</span>
                                            
                                            <input type="time" 
                                                   wire:model="rows.{{ $i }}.end" 
                                                   class="rounded-xl border border-slate-200 bg-white px-3 py-1.5 font-mono text-xs font-semibold text-slate-800 shadow-2xs focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900">
                                        </div>

                                        <button type="button" 
                                                wire:click="removeRange({{ $i }})" 
                                                class="inline-flex items-center gap-1 rounded-xl p-1.5 text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer"
                                                title="Hapus jam">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <p class="mt-3 text-xs text-slate-400 italic">Tidak ada jam operasional yang dibuka untuk hari ini.</p>
                    @endif

                </div>
            @endforeach
        </div>

        {{-- Tombol Simpan Jadwal --}}
        <div class="pt-2">
            <button type="submit" 
                    wire:loading.attr="disabled" 
                    wire:target="save"
                    class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-6 py-2.5 text-xs font-bold text-white shadow hover:bg-slate-800 transition-all cursor-pointer disabled:opacity-50">
                <span wire:loading.remove wire:target="save">Simpan jadwal</span>
                <span wire:loading wire:target="save" class="flex items-center gap-1.5">
                    <svg class="h-3.5 w-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <span>Menyimpan...</span>
                </span>
            </button>
        </div>
    </form>

</div>