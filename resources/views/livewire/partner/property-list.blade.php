<div class="space-y-6 pb-12">
    
    {{-- Header Editorial & Aksi Utama --}}
    <div class="flex flex-col justify-between gap-4 border-b border-slate-200/80 pb-6 sm:flex-row sm:items-end">
        <div>
            <nav class="mb-2 flex items-center gap-2 text-xs font-medium text-slate-400">
                <a href="{{ route('home') }}" class="hover:text-blue-600 transition-colors">Portal Mitra</a>
                <span>/</span>
                <span class="text-slate-700">Inventaris Properti</span>
            </nav>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">Properti saya</h1>
            <p class="mt-1 text-xs sm:text-sm text-slate-500">Kelola inventaris listing hunian, pantau status verifikasi, dan perbarui ketersediaan unit.</p>
        </div>

        <div>
            <a href="{{ route('partner.properties.create') }}" 
               class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-5 py-2.5 text-xs font-bold text-white shadow-md hover:bg-slate-800 transition-all">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Tambah properti</span>
            </a>
        </div>
    </div>

    {{-- Toolbar Filter & Pencarian Cepat --}}
    <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-wrap items-center gap-3">
                {{-- Input Pencarian --}}
                <div class="relative w-full sm:w-72">
                    <input type="text" 
                           wire:model.live.debounce.500ms="q" 
                           placeholder="Cari judul, alamat, kota" 
                           class="w-full rounded-xl border border-slate-200 bg-slate-50/50 pl-9 pr-3.5 py-2 text-xs text-slate-800 placeholder-slate-400 focus:bg-white focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900 transition-all">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </div>

                {{-- Filter Status --}}
                <div class="relative w-full sm:w-auto">
                    <select wire:model.live="status" 
                            class="w-full sm:w-auto appearance-none rounded-xl border border-slate-200 bg-slate-50/50 py-2 pl-3.5 pr-8 text-xs font-semibold text-slate-700 shadow-sm focus:bg-white focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900 transition-all cursor-pointer">
                        <option value="">Semua status</option>
                        <option value="draft">Draft</option>
                        <option value="pending_review">Menunggu verifikasi</option>
                        <option value="published">Tayang</option>
                        <option value="reserved">Dipesan</option>
                        <option value="rejected">Ditolak</option>
                        <option value="sold">Terjual</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-slate-400">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Indikator Reaktif Livewire --}}
            <div wire:loading class="text-xs font-medium text-slate-400 flex items-center gap-1.5 self-start sm:self-auto">
                <svg class="h-3.5 w-3.5 animate-spin text-slate-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                <span>Memperbarui data...</span>
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

    {{-- Daftar Inventaris Properti --}}
    <div class="divide-y divide-slate-100 overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm transition-opacity duration-200" wire:loading.class="opacity-60">
        @forelse ($properties as $p)
            @php $locked = in_array($p->status->value, ['reserved', 'sold'], true); @endphp
            
            <div wire:key="prop-{{ $p->id }}" class="flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between transition-colors hover:bg-slate-50/60">
                
                {{-- Data Visual & Spesifikasi Unit --}}
                <div class="flex min-w-0 flex-1 items-start gap-4">
                    {{-- Thumbnail Foto --}}
                    <div class="relative h-20 w-28 shrink-0 overflow-hidden rounded-2xl bg-slate-100 border border-slate-200">
                        @if ($p->coverImage)
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($p->coverImage->path) }}" 
                                 alt="{{ $p->title }}" 
                                 class="h-full w-full object-cover transition-transform duration-300 hover:scale-105" 
                                 onerror="this.remove()">
                        @else
                            <div class="flex h-full w-full items-center justify-center text-slate-300">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        @endif
                    </div>

                    {{-- Detail Teks --}}
                    <div class="min-w-0 flex-1 space-y-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <x-status-badge :status="$p->status" context="property" />
                            <h2 class="truncate text-sm font-bold text-slate-900">{{ $p->title }}</h2>
                        </div>

                        <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-slate-500">
                            <span class="inline-flex items-center gap-1">
                                <svg class="h-3 w-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                {{ $p->city }}
                            </span>
                            <span class="text-slate-300">·</span>
                            <span class="font-bold text-slate-800"><x-rupiah :value="$p->price" /></span>
                            <span class="text-slate-300">·</span>
                            <span class="inline-flex items-center gap-1 rounded-md bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-600">
                                <svg class="h-3 w-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                dilihat {{ $p->views_count }}×
                            </span>
                        </div>

                        {{-- Catatan Penolakan Admin --}}
                        @if ($p->status->value === 'rejected' && $p->rejection_reason)
                            <div class="mt-2 rounded-xl bg-rose-50 border border-rose-100 px-3 py-1.5 text-xs text-rose-700 flex items-center gap-1.5">
                                <svg class="h-3.5 w-3.5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Ditolak: {{ $p->rejection_reason }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Tombol Aksi Kontekstual --}}
                <div class="flex flex-wrap items-center gap-2 pt-2 sm:pt-0 sm:shrink-0 text-xs">
                    
                    {{-- Ubah Data --}}
                    @if (! $locked)
                        <a href="{{ route('partner.properties.edit', $p->id) }}" 
                           class="inline-flex items-center gap-1 rounded-xl border border-slate-200 bg-white px-3 py-1.5 font-semibold text-slate-700 shadow-sm hover:bg-slate-50 hover:border-slate-300 transition-all">
                            <svg class="h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            <span>Ubah</span>
                        </a>
                    @endif

                    {{-- Ajukan Ulang / Pertama Kali --}}
                    @if (in_array($p->status->value, ['draft', 'rejected'], true))
                        <button type="button" 
                                wire:click="submit({{ $p->id }})" 
                                class="inline-flex items-center gap-1 rounded-xl bg-blue-600 px-3 py-1.5 font-bold text-white shadow hover:bg-blue-700 transition-all cursor-pointer">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Ajukan</span>
                        </button>
                    @endif

                    {{-- Buka Halaman Publik --}}
                    @if ($p->status->value === 'published')
                        <a href="{{ route('properties.show', $p->slug) }}" 
                           target="_blank"
                           class="inline-flex items-center gap-1 rounded-xl border border-slate-200 bg-white px-3 py-1.5 font-semibold text-slate-700 shadow-sm hover:bg-slate-50 hover:border-slate-300 transition-all">
                            <span>Lihat</span>
                            <svg class="h-3 w-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </a>
                    @endif

                    {{-- Manajemen Status Reservasi Escrow --}}
                    @if ($p->status->value === 'reserved')
                        <button type="button" 
                                wire:click="markSold({{ $p->id }})" 
                                wire:confirm="Tandai properti ini terjual?" 
                                class="inline-flex items-center gap-1 rounded-xl bg-emerald-600 px-3 py-1.5 font-bold text-white shadow hover:bg-emerald-700 transition-all cursor-pointer">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Tandai terjual</span>
                        </button>
                        <button type="button" 
                                wire:click="release({{ $p->id }})" 
                                wire:confirm="Lepas reservasi? Properti akan tayang kembali dan booking fee perlu dikembalikan oleh administrator." 
                                class="inline-flex items-center gap-1 rounded-xl border border-slate-200 bg-white px-3 py-1.5 font-semibold text-slate-700 shadow-sm hover:bg-slate-50 transition-all cursor-pointer">
                            <span>Lepas reservasi</span>
                        </button>
                    @endif

                    {{-- Hapus Unit --}}
                    @if (! $locked)
                        <button type="button" 
                                wire:click="delete({{ $p->id }})" 
                                wire:confirm="Hapus properti ini?" 
                                class="inline-flex items-center gap-1 rounded-xl border border-rose-200 bg-rose-50/50 px-3 py-1.5 font-semibold text-rose-700 hover:bg-rose-100 hover:border-rose-300 transition-all cursor-pointer">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            <span>Hapus</span>
                        </button>
                    @endif

                </div>

            </div>
        @empty
            <div class="rounded-3xl p-12 text-center">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <h3 class="mt-4 text-sm font-bold text-slate-900">Belum ada properti.</h3>
                <p class="mt-1 text-xs text-slate-500 max-w-sm mx-auto">Mulai tambahkan unit properti pertama Anda untuk ditinjau oleh tim kurator platform.</p>
                <a href="{{ route('partner.properties.create') }}" 
                   class="mt-4 inline-flex items-center gap-1.5 rounded-xl bg-slate-900 px-4 py-2 text-xs font-semibold text-white shadow hover:bg-slate-800 transition-colors">
                    <span>Tambah yang pertama</span> &rarr;
                </a>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="pt-2">
        {{ $properties->links() }}
    </div>

</div>