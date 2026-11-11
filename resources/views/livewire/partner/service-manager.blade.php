@php
    $types = [
        'consultation' => 'Konsultasi',
        'design'       => 'Desain',
        'supervision'  => 'Pengawasan',
    ];
    $input = 'w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2.5 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:bg-white focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900 transition-all';
@endphp

{{-- ROOT ELEMENT TUNGGAL LIVEWIRE 3 --}}
<div class="space-y-6 pb-12">
    
    {{-- Header Editorial --}}
    <div class="flex flex-col justify-between gap-4 border-b border-slate-200/80 pb-6 sm:flex-row sm:items-end">
        <div>
            <nav class="mb-2 flex items-center gap-2 text-xs font-medium text-slate-400">
                <a href="{{ route('partner.dashboard') }}" class="hover:text-blue-600 transition-colors">Portal Mitra</a>
                <span>/</span>
                <span class="text-slate-700">Manajemen Layanan</span>
            </nav>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">Layanan saya</h1>
            <p class="mt-1 text-xs sm:text-sm text-slate-500">Daftar tarif sesi konsultasi, jasa desain arsitektur, dan supervisi pembangunan proyek.</p>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" 
                    wire:click="create" 
                    class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-xs font-bold text-white shadow hover:bg-slate-800 transition-all cursor-pointer">
                <svg class="h-4 w-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Tambah layanan</span>
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

    @error('list')
        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-xs font-semibold text-rose-700 shadow-sm">
            {{ $message }}
        </div>
    @enderror

    {{-- FORMULIR INLINE TAMBAH / UBAH LAYANAN --}}
    @if ($showForm)
        <form wire:submit="save" class="space-y-5 rounded-3xl border border-slate-200/80 bg-white p-6 sm:p-7 shadow-sm">
            <div class="border-b border-slate-100 pb-3">
                <h2 class="text-sm font-bold text-slate-900">
                    {{ $editingId ? 'Ubah Layanan' : 'Layanan Baru' }}
                </h2>
                <p class="text-xs text-slate-400 mt-0.5">Tentukan spesifikasi nama paket, durasi waktu pertemuan, dan tarif dasar profesional.</p>
            </div>

            @error('form')
                <div class="rounded-xl border border-rose-200 bg-rose-50 px-3.5 py-2 text-xs font-semibold text-rose-700">
                    {{ $message }}
                </div>
            @enderror

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Nama Layanan</label>
                <input type="text" wire:model="title" class="{{ $input }}" placeholder="mis. Konsultasi Desain Rumah (1 jam)">
                @error('title')<p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Jenis</label>
                    <div class="relative">
                        <select wire:model="service_type" class="{{ $input }} appearance-none pr-8 cursor-pointer">
                            @foreach ($types as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                    @error('service_type')<p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Harga (Rp)</label>
                    <input type="number" min="1" step="any" wire:model="price" class="{{ $input }}" placeholder="mis. 500000">
                    @error('price')<p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Durasi Sesi</label>
                    <div class="relative">
                        <select wire:model="duration_minutes" class="{{ $input }} appearance-none pr-8 cursor-pointer">
                            @foreach ([30, 45, 60, 90, 120, 180, 240] as $m)
                                <option value="{{ $m }}">{{ $m }} menit</option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                    @error('duration_minutes')<p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Deskripsi Layanan</label>
                <textarea rows="4" wire:model="description" class="{{ $input }}" placeholder="Apa yang didapat klien dari layanan ini? Uraikan deliverables, output diskusi, atau lembar rekomendasi desain..."></textarea>
                @error('description')<p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
            </div>

            <label class="inline-flex items-center gap-2.5 cursor-pointer rounded-xl border border-slate-200 bg-slate-50/60 p-3">
                <input type="checkbox" wire:model="is_active" class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900">
                <span class="text-xs font-bold text-slate-700">Aktif (tampil di katalog platform)</span>
            </label>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" 
                        wire:loading.attr="disabled" 
                        wire:target="save" 
                        class="inline-flex items-center gap-1.5 rounded-xl bg-slate-900 px-5 py-2.5 text-xs font-bold text-white shadow hover:bg-slate-800 transition-all cursor-pointer disabled:opacity-50">
                    <span wire:loading.remove wire:target="save">{{ $editingId ? 'Perbarui Layanan' : 'Simpan Layanan' }}</span>
                    <span wire:loading wire:target="save" class="flex items-center gap-1.5">
                        <svg class="h-3.5 w-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        <span>Menyimpan...</span>
                    </span>
                </button>
                <button type="button" 
                        wire:click="cancelForm" 
                        class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 transition-all cursor-pointer">
                    Batal
                </button>
            </div>
        </form>
    @endif

    {{-- DAFTAR LAYANAN MITRA --}}
    <div class="divide-y divide-slate-100 overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm transition-opacity duration-200"
         wire:loading.class="opacity-60">
        @forelse ($services as $s)
            <div wire:key="svc-{{ $s->id }}" class="flex flex-col gap-4 p-5 sm:flex-row sm:items-start sm:justify-between sm:p-6 transition-colors hover:bg-slate-50/50">
                
                {{-- Metadata Layanan --}}
                <div class="min-w-0 flex-1 space-y-1.5">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded-lg border border-blue-200/80 bg-blue-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-blue-700">
                            {{ $types[$s->service_type->value] ?? $s->service_type->value }}
                        </span>
                        <span class="rounded-lg border px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ $s->is_active ? 'border-emerald-200/80 bg-emerald-50 text-emerald-800' : 'border-slate-200 bg-slate-100 text-slate-600' }}">
                            {{ $s->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </div>

                    <h2 class="text-sm sm:text-base font-bold text-slate-900 tracking-tight">
                        {{ $s->title }}
                    </h2>

                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-slate-500">
                        <span class="font-mono font-bold text-slate-900"><x-rupiah :value="$s->price" /></span>
                        <span class="text-slate-300">·</span>
                        <span class="flex items-center gap-1 text-slate-600 font-medium">
                            <svg class="h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>{{ $s->duration_minutes }} menit per sesi</span>
                        </span>
                    </div>

                    @if ($s->description)
                        <p class="text-xs text-slate-500 line-clamp-2 pt-0.5 leading-relaxed">
                            {{ $s->description }}
                        </p>
                    @endif
                </div>

                {{-- Tombol Aksi --}}
                <div class="flex flex-wrap items-center gap-1.5 pt-2 sm:pt-0 shrink-0 border-t border-slate-100 sm:border-t-0 text-xs">
                    <button type="button" 
                            wire:click="edit({{ $s->id }})" 
                            class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-1.5 font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 hover:border-slate-300 transition-all cursor-pointer">
                        <svg class="h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        <span>Ubah</span>
                    </button>

                    <button type="button" 
                            wire:click="toggle({{ $s->id }})" 
                            class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-3 py-1.5 font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 hover:border-slate-300 transition-all cursor-pointer">
                        <span>{{ $s->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</span>
                    </button>

                    <button type="button" 
                            wire:click="delete({{ $s->id }})" 
                            wire:confirm="Hapus layanan ini?" 
                            class="inline-flex items-center gap-1 rounded-xl border border-rose-200 bg-rose-50/70 px-3 py-1.5 font-bold text-rose-700 hover:bg-rose-100 transition-all cursor-pointer">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        <span>Hapus</span>
                    </button>
                </div>

            </div>
        @empty
            <div class="p-12 text-center">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h3 class="mt-4 text-sm font-bold text-slate-900">Belum ada layanan.</h3>
                <p class="mt-1 text-xs text-slate-500 max-w-sm mx-auto">Buat layanan konsultasi, paket desain, atau supervisi pertama Anda agar tampil di katalog arsitek.</p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="pt-2">
        {{ $services->links() }}
    </div>

</div>