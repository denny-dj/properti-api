<div class="space-y-6 pb-12">
    
    {{-- Header Editorial --}}
    <div class="flex flex-col justify-between gap-4 border-b border-slate-200/80 pb-6 sm:flex-row sm:items-end">
        <div>
            <nav class="mb-2 flex items-center gap-2 text-xs font-medium text-slate-400">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-blue-600 transition-colors">Pusat Kendali</a>
                <span>/</span>
                <span class="text-slate-700">Moderasi Properti</span>
            </nav>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">Moderasi Properti</h1>
            <p class="mt-1 text-xs sm:text-sm text-slate-500">Verifikasi kelayakan listing, akurasi koordinat peta, spesifikasi fisik, dan legalitas surat kepemilikan.</p>
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

                {{-- Filter Status Moderasi --}}
                <div class="relative w-full sm:w-auto">
                    <select wire:model.live="status" 
                            class="w-full sm:w-auto appearance-none rounded-xl border border-slate-200 bg-slate-50/50 py-2 pl-3.5 pr-8 text-xs font-semibold text-slate-700 shadow-sm focus:bg-white focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900 transition-all cursor-pointer">
                        <option value="pending_review">Menunggu verifikasi</option>
                        <option value="published">Tayang</option>
                        <option value="reserved">Dipesan</option>
                        <option value="rejected">Ditolak</option>
                        <option value="sold">Terjual</option>
                        <option value="draft">Draft</option>
                        <option value="">Semua status</option>
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
                <span>Memuat data kurasi...</span>
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

    {{-- Daftar Unit Menunggu Kurasi --}}
    <div class="divide-y divide-slate-100 overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm transition-opacity duration-200" 
         wire:loading.class="opacity-60">
        @forelse ($properties as $p)
            <div wire:key="pm-{{ $p->id }}" class="space-y-4 p-5 sm:p-6 transition-colors hover:bg-slate-50/50">
                
                {{-- Header Item Listing & Tombol Aksi Moderasi --}}
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="flex min-w-0 flex-1 items-start gap-4">
                        {{-- Thumbnail Foto Sampul --}}
                        <div class="relative h-20 w-28 shrink-0 overflow-hidden rounded-2xl bg-slate-100 border border-slate-200">
                            @if ($p->coverImage)
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($p->coverImage->path) }}" 
                                     alt="" 
                                     class="h-full w-full object-cover" 
                                     onerror="this.remove()">
                            @else
                                <div class="flex h-full w-full items-center justify-center text-slate-300">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            @endif
                        </div>

                        {{-- Metadata Listing --}}
                        <div class="min-w-0 flex-1 space-y-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <x-status-badge :status="$p->status" context="property" />
                                <h2 class="truncate text-sm font-bold text-slate-900">{{ $p->title }}</h2>
                            </div>

                            <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-slate-500">
                                <span class="font-medium text-slate-700">{{ $p->partner?->partnerProfile?->business_name ?? $p->partner?->name }}</span>
                                <span class="text-slate-300">·</span>
                                <span>{{ $p->city }}</span>
                                <span class="text-slate-300">·</span>
                                <span class="font-bold text-slate-900"><x-rupiah :value="$p->price" /></span>
                            </div>

                            <p class="font-mono text-[11px] text-slate-400">Diajukan: {{ $p->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>

                    {{-- Tombol Aksi Administrator --}}
                    <div class="flex flex-wrap items-center gap-2 pt-1 sm:pt-0 shrink-0 text-xs">
                        {{-- Tombol Tinjau / Sembunyikan Detail --}}
                        <button type="button" 
                                wire:click="toggle({{ $p->id }})" 
                                class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-1.5 font-semibold text-slate-700 shadow-sm hover:bg-slate-50 hover:border-slate-300 transition-all cursor-pointer">
                            <svg class="h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $expandedId === $p->id ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7' }}"/>
                            </svg>
                            <span>{{ $expandedId === $p->id ? 'Tutup Detail' : 'Tinjau Lengkap' }}</span>
                        </button>

                        {{-- Aksi Verifikasi: Setujui & Tolak --}}
                        @if ($p->status->value === 'pending_review')
                            <button type="button" 
                                    wire:click="approve({{ $p->id }})" 
                                    wire:confirm="Setujui dan tayangkan properti ini ke platform publik?" 
                                    class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-3.5 py-1.5 font-bold text-white shadow hover:bg-emerald-700 transition-all cursor-pointer">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>Setujui</span>
                            </button>

                            <button type="button" 
                                    wire:click="startReject({{ $p->id }})" 
                                    class="inline-flex items-center gap-1.5 rounded-xl border border-rose-200 bg-rose-50/70 px-3 py-1.5 font-semibold text-rose-700 hover:bg-rose-100 transition-all cursor-pointer">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                <span>Tolak</span>
                            </button>
                        @endif

                        {{-- Tautan Halaman Publik --}}
                        @if ($p->status->value === 'published')
                            <a href="{{ route('properties.show', $p->slug) }}" 
                               target="_blank" 
                               class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-1.5 font-semibold text-slate-700 shadow-sm hover:bg-slate-50 transition-all">
                                <span>Lihat Listing</span>
                                <svg class="h-3 w-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Catatan Penolakan Jika Berstatus Rejected --}}
                @if ($p->status->value === 'rejected' && $p->rejection_reason)
                    <div class="rounded-2xl border border-rose-200 bg-rose-50/70 px-4 py-3 text-xs text-rose-800 flex items-start gap-2.5">
                        <svg class="h-4 w-4 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <strong class="font-bold">Catatan Penolakan Sebelumnya:</strong> {{ $p->rejection_reason }}
                        </div>
                    </div>
                @endif

                {{-- DRAWER TINJAU DETAIL UNIT (ARCHITECTURAL INSPECTION SHEET) --}}
                @if ($detail && $expandedId === $p->id)
                    <div class="rounded-3xl border border-slate-200/90 bg-slate-50/60 p-5 sm:p-6 space-y-6">
                        
                        {{-- Galeri Foto Lengkap --}}
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Berkas Galeri Unit ({{ $detail->images->count() }} Foto)</h3>
                            </div>
                            @if ($detail->images->isNotEmpty())
                                <div class="flex gap-3 overflow-x-auto pb-2">
                                    @foreach ($detail->images as $img)
                                        <div class="relative h-32 w-48 shrink-0 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xs">
                                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($img->path) }}" 
                                                 alt="" 
                                                 class="h-full w-full object-cover transition-transform hover:scale-105" 
                                                 onerror="this.remove()">
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-xs font-medium text-amber-700 bg-amber-50 rounded-xl p-3 border border-amber-200/60">Mitra belum mengunggah foto pendukung untuk unit ini.</p>
                            @endif
                        </div>

                        {{-- Spesifikasi Teknis --}}
                        <div class="border-t border-slate-200/70 pt-5">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-4">Spesifikasi Arsitektur &amp; Legalitas</h3>
                            <dl class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 text-xs">
                                <div class="rounded-2xl border border-slate-200/80 bg-white p-3.5 shadow-2xs">
                                    <dt class="font-bold uppercase tracking-wider text-[10px] text-slate-400">Tipe &amp; Model Penawaran</dt>
                                    <dd class="mt-1 font-semibold text-slate-900">{{ $detail->property_type->value === 'house' ? 'Rumah Tapak' : 'Ruko Komersial' }} · {{ $detail->listing_type->value === 'sale' ? 'Dijual' : 'Disewa' }}</dd>
                                </div>

                                <div class="rounded-2xl border border-slate-200/80 bg-white p-3.5 shadow-2xs">
                                    <dt class="font-bold uppercase tracking-wider text-[10px] text-slate-400">Kamar Tidur / Kamar Mandi</dt>
                                    <dd class="mt-1 font-semibold text-slate-900">{{ $detail->bedrooms }} KT / {{ $detail->bathrooms }} KM</dd>
                                </div>

                                <div class="rounded-2xl border border-slate-200/80 bg-white p-3.5 shadow-2xs">
                                    <dt class="font-bold uppercase tracking-wider text-[10px] text-slate-400">Jumlah Lantai</dt>
                                    <dd class="mt-1 font-semibold text-slate-900">{{ $detail->floors }} Lantai</dd>
                                </div>

                                <div class="rounded-2xl border border-slate-200/80 bg-white p-3.5 shadow-2xs">
                                    <dt class="font-bold uppercase tracking-wider text-[10px] text-slate-400">Luas Tanah / Luas Bangunan</dt>
                                    <dd class="mt-1 font-semibold text-slate-900">{{ $detail->land_area ? number_format((float) $detail->land_area, 0, ',', '.') : '-' }} m² / {{ $detail->building_area ? number_format((float) $detail->building_area, 0, ',', '.') : '-' }} m²</dd>
                                </div>

                                <div class="rounded-2xl border border-slate-200/80 bg-white p-3.5 shadow-2xs">
                                    <dt class="font-bold uppercase tracking-wider text-[10px] text-slate-400">Dokumen Sertifikat</dt>
                                    <dd class="mt-1 font-semibold text-slate-900">{{ $detail->certificate ?: '-' }}</dd>
                                </div>

                                <div class="rounded-2xl border border-slate-200/80 bg-white p-3.5 shadow-2xs">
                                    <dt class="font-bold uppercase tracking-wider text-[10px] text-slate-400">Harga Tertera</dt>
                                    <dd class="mt-1 font-mono font-bold text-emerald-600"><x-rupiah :value="$detail->price" /></dd>
                                </div>

                                {{-- Alamat & Koordinat Peta --}}
                                <div class="sm:col-span-2 lg:col-span-3 rounded-2xl border border-slate-200/80 bg-white p-3.5 shadow-2xs">
                                    <dt class="font-bold uppercase tracking-wider text-[10px] text-slate-400">Alamat Lengkap &amp; Koordinat</dt>
                                    <dd class="mt-1 font-semibold text-slate-900 flex flex-wrap items-center gap-2">
                                        <span>{{ $detail->address }}, {{ $detail->district ? $detail->district.', ' : '' }}{{ $detail->city }}, {{ $detail->province }}</span>
                                        @if ($detail->latitude !== null && $detail->longitude !== null)
                                            <a href="https://www.google.com/maps/search/?api=1&query={{ $detail->latitude }},{{ $detail->longitude }}" 
                                               target="_blank" 
                                               rel="noopener" 
                                               class="inline-flex items-center gap-1 rounded-md bg-blue-50 px-2 py-0.5 text-[11px] font-bold text-blue-700 hover:bg-blue-100 transition-colors">
                                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                <span>{{ $detail->latitude }}, {{ $detail->longitude }}</span>
                                            </a>
                                        @else
                                            <span class="rounded-md bg-amber-50 px-2 py-0.5 text-[11px] font-bold text-amber-800 border border-amber-200/60">Titik koordinat belum ditentukan</span>
                                        @endif
                                    </dd>
                                </div>

                                {{-- Fasilitas --}}
                                @if ($detail->facilities->isNotEmpty())
                                    <div class="sm:col-span-2 lg:col-span-3 rounded-2xl border border-slate-200/80 bg-white p-3.5 shadow-2xs">
                                        <dt class="font-bold uppercase tracking-wider text-[10px] text-slate-400 mb-2">Fasilitas Unit</dt>
                                        <dd class="flex flex-wrap gap-1.5">
                                            @foreach ($detail->facilities as $f)
                                                <span class="rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">{{ $f->name }}</span>
                                            @endforeach
                                        </dd>
                                    </div>
                                @endif

                                {{-- Deskripsi --}}
                                <div class="sm:col-span-2 lg:col-span-3 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-2xs">
                                    <dt class="font-bold uppercase tracking-wider text-[10px] text-slate-400 mb-1">Deskripsi Iklan</dt>
                                    <dd class="whitespace-pre-line text-slate-700 leading-relaxed">{{ $detail->description ?: 'Tidak ada deskripsi.' }}</dd>
                                </div>

                                {{-- Profil Pengunggah / Mitra --}}
                                <div class="sm:col-span-2 lg:col-span-3 rounded-2xl border border-slate-200/80 bg-white p-3.5 shadow-2xs flex flex-wrap items-center justify-between gap-3">
                                    <div>
                                        <dt class="font-bold uppercase tracking-wider text-[10px] text-slate-400">Identitas Pengunggah</dt>
                                        <dd class="mt-0.5 font-bold text-slate-900">
                                            {{ $detail->partner?->partnerProfile?->business_name ?? $detail->partner?->name }} 
                                            <span class="font-normal text-slate-500">({{ $detail->partner?->email }})</span>
                                        </dd>
                                    </div>
                                    @if ($detail->partner?->partnerProfile)
                                        <div>
                                            <x-status-badge :status="$detail->partner->partnerProfile->verification_status" context="verification" />
                                        </div>
                                    @endif
                                </div>
                            </dl>
                        </div>

                    </div>
                @endif

                {{-- FORM ALASAN PENOLAKAN MODERASI --}}
                @if ($rejectingId === $p->id)
                    <form wire:submit="reject" class="space-y-3 rounded-3xl border border-rose-200 bg-rose-50/70 p-5 shadow-sm">
                        <div class="flex items-center gap-2">
                            <svg class="h-4 w-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <label class="block text-xs font-bold text-rose-950 uppercase tracking-wider">
                                Catatan Alasan Penolakan Unit (Akan Dilihat oleh Pengunggah)
                            </label>
                        </div>
                        <textarea rows="2.5" 
                                  wire:model="reason" 
                                  class="w-full rounded-xl border border-rose-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500 shadow-2xs" 
                                  placeholder="mis. Foto fasad buram, alamat belum menyertakan nomor unit cluster, sertifikat tidak sesuai data BPN..."></textarea>
                        @error('reason')<p class="text-xs font-semibold text-rose-700">{{ $message }}</p>@enderror
                        
                        <div class="flex items-center gap-2 pt-1">
                            <button type="submit" 
                                    class="inline-flex items-center gap-1.5 rounded-xl bg-rose-600 px-4 py-2 text-xs font-bold text-white shadow hover:bg-rose-700 transition-all cursor-pointer">
                                <span>Kirim Penolakan ke Mitra</span>
                            </button>
                            <button type="button" 
                                    wire:click="cancelReject" 
                                    class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 transition-all cursor-pointer">
                                <span>Batal</span>
                            </button>
                        </div>
                    </form>
                @endif

            </div>
        @empty
            <div class="p-12 text-center">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="mt-4 text-sm font-bold text-slate-900">Tidak ada properti pada filter ini.</h3>
                <p class="mt-1 text-xs text-slate-500 max-w-sm mx-auto">Semua pengajuan unit listing telah selesai dimoderasi atau tidak ditemukan unit dengan kriteria pencarian tersebut.</p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="pt-2">
        {{ $properties->links() }}
    </div>

</div>