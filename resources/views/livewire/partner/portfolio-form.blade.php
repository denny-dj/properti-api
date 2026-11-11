@php
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
                <a href="{{ route('partner.portfolios.index') }}" class="hover:text-blue-600 transition-colors">Portofolio</a>
                <span>/</span>
                <span class="text-slate-700">{{ $portfolioId ? 'Ubah Karya' : 'Karya Baru' }}</span>
            </nav>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">
                {{ $portfolioId ? 'Ubah portofolio' : 'Portofolio baru' }}
            </h1>
            <p class="mt-1 text-xs sm:text-sm text-slate-500">Lengkapi spesifikasi teknis, taksiran anggaran konstruksi, dan berkas visual desain arsitektur Anda.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('partner.portfolios.index') }}" 
               class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 hover:border-slate-300 transition-all cursor-pointer">
                <svg class="h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span>Kembali ke daftar</span>
            </a>
        </div>
    </div>

    {{-- Feedback Notifikasi & Galat --}}
    @if ($notice)
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs font-semibold text-emerald-800 shadow-sm flex items-center gap-2">
            <svg class="h-4 w-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>{{ $notice }}</span>
        </div>
    @endif

    @error('form')
        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-xs font-semibold text-rose-700 shadow-sm">
            {{ $message }}
        </div>
    @enderror

    {{-- FORMULIR UTAMA --}}
    <form wire:submit="save" class="space-y-6 rounded-3xl border border-slate-200/80 bg-white p-6 sm:p-8 shadow-sm">
        
        {{-- Section 1: Identitas & Klasifikasi --}}
        <div class="space-y-4">
            <div class="border-b border-slate-100 pb-2">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Identitas &amp; Tipologi Desain</h3>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Judul Karya</label>
                <input type="text" wire:model="title" class="{{ $input }}" placeholder="mis. Rumah Japandi 2 Lantai 120 m²">
                @error('title')<p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Kategori</label>
                    <div class="relative">
                        <select wire:model="category" class="{{ $input }} appearance-none pr-8 cursor-pointer">
                            @foreach ($categories as $option)
                                <option value="{{ $option->value }}">{{ $option->label() }}</option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                    @error('category')<p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Gaya Desain</label>
                    <div class="relative">
                        <select wire:model="architectural_style" class="{{ $input }} appearance-none pr-8 cursor-pointer">
                            @foreach ($styles as $option)
                                <option value="{{ $option->value }}">{{ $option->label() }}</option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                    @error('architectural_style')<p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- Section 2: Dimensi & Biaya --}}
        <div class="space-y-4 pt-2">
            <div class="border-b border-slate-100 pb-2">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Dimensi &amp; Estimasi Konstruksi</h3>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Estimasi RAB Konstruksi (Rp)</label>
                    <input type="text" inputmode="numeric" wire:model="estimated_rab" class="{{ $input }}" placeholder="mis. 600000000">
                    @error('estimated_rab')<p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Luas Bangunan (m²)</label>
                    <input type="text" inputmode="decimal" wire:model="area_sqm" class="{{ $input }}" placeholder="mis. 120">
                    @error('area_sqm')<p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>
            <p class="text-[11px] text-slate-400">Biaya estimasi per m² akan dihitung otomatis oleh platform dari RAB dibagi total luas.</p>
        </div>

        {{-- Section 3: Uraian Konsep --}}
        <div class="space-y-2 pt-2">
            <div class="border-b border-slate-100 pb-2">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Konsep Desain &amp; Spesifikasi</h3>
            </div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Deskripsi Naratif</label>
            <textarea rows="5" wire:model="description" class="{{ $input }}" placeholder="Jelaskan konsep tata ruang, pencahayaan alami, material utama, dan keunggulan desain arsitektur ini..."></textarea>
            @error('description')<p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
        </div>

        {{-- Section 4: Monetisasi Desain Siap Bangun (Jalur A) --}}
        <div class="rounded-2xl border border-emerald-200/80 bg-emerald-50/50 p-5 space-y-4 shadow-2xs">
            <label class="flex items-center gap-2.5 cursor-pointer">
                <input type="checkbox" wire:model.live="is_ready_to_build" class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                <span class="text-xs font-bold uppercase tracking-wider text-emerald-950">Jual sebagai desain siap bangun (Jalur A)</span>
            </label>

            @if ($is_ready_to_build)
                <div class="space-y-4 border-t border-emerald-200/60 pt-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-emerald-950 mb-1.5">Harga Paket Desain (Rp)</label>
                        <input type="text" inputmode="numeric" wire:model="blueprint_price" class="w-full rounded-xl border border-emerald-200 bg-white px-3.5 py-2.5 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600 shadow-2xs" placeholder="mis. 5000000">
                        @error('blueprint_price')<p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-emerald-950 mb-1.5">Berkas yang Diserahkan (Satu per baris)</label>
                        <textarea rows="4" wire:model="deliverables" class="w-full rounded-xl border border-emerald-200 bg-white px-3.5 py-2.5 font-mono text-xs text-slate-800 placeholder-slate-400 focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600 shadow-2xs" placeholder="DED Lengkap (AutoCAD / PDF)&#10;Model 3D (SketchUp / Lumion)&#10;Rincian Anggaran Biaya (Excel)&#10;Render Visual Fotorealistik"></textarea>
                        @error('deliverables')<p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="rounded-xl bg-emerald-100/60 p-3 text-xs text-emerald-900 leading-relaxed flex items-start gap-2">
                        <svg class="h-4 w-4 text-emerald-700 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Berkas final diserahkan lewat tautan aman Google Drive setelah pembeli melunasi pembayaran. Dana Anda akan dilepas dari rekening escrow setelah pembeli mengonfirmasi penerimaan berkas.</span>
                    </div>
                </div>
            @endif
        </div>

        {{-- Section 5: Status Publikasi --}}
        <div class="space-y-1.5 pt-2">
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">Status Publikasi</label>
            <div class="relative sm:max-w-xs">
                <select wire:model="status" class="{{ $input }} appearance-none pr-8 cursor-pointer">
                    <option value="draft">Draft (belum tampil)</option>
                    <option value="published">Tayang (minimal 1 foto)</option>
                    <option value="archived">Diarsipkan</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </div>
            </div>
            @error('status')<p class="text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
        </div>

        {{-- Section 6: Galeri Foto Portofolio --}}
        <div class="space-y-4 border-t border-slate-100 pt-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Galeri Foto Portofolio</h3>
                    <p class="text-xs text-slate-400">Format JPG, PNG, atau WebP. Maksimal 10 foto resolusi tinggi.</p>
                </div>
                <span class="font-mono text-xs font-bold rounded-lg bg-slate-100 px-2.5 py-1 text-slate-700">
                    {{ $images->count() }}/10 Foto
                </span>
            </div>

            @error('photos')
                <div class="rounded-xl border border-rose-200 bg-rose-50 px-3.5 py-2 text-xs font-semibold text-rose-700">
                    {{ $message }}
                </div>
            @enderror

            {{-- Grid Foto Tersimpan --}}
            @if ($images->isNotEmpty())
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4">
                    @foreach ($images as $image)
                        <div wire:key="pimg-{{ $image->id }}" class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xs">
                            <div class="relative aspect-[4/3] bg-slate-100 overflow-hidden">
                                <img src="{{ $image->url() }}" alt="" class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105" onerror="this.remove()">
                                @if ($image->is_cover)
                                    <span class="absolute left-2.5 top-2.5 rounded-lg bg-slate-900/90 backdrop-blur-xs px-2 py-0.5 text-[10px] font-bold text-white shadow-sm">
                                        Sampul
                                    </span>
                                @endif
                            </div>
                            <div class="flex items-center justify-between gap-1 p-2 bg-white text-xs border-t border-slate-100">
                                @if (! $image->is_cover)
                                    <button type="button" 
                                            wire:click="makeCover({{ $image->id }})" 
                                            class="font-semibold text-blue-600 hover:text-blue-700 hover:underline cursor-pointer">
                                        Jadikan sampul
                                    </button>
                                @else
                                    <span class="text-[10px] text-slate-400 italic">Sampul aktif</span>
                                @endif
                                <button type="button" 
                                        wire:click="deleteImage({{ $image->id }})" 
                                        wire:confirm="Hapus foto ini dari galeri?" 
                                        class="font-semibold text-rose-600 hover:text-rose-700 hover:underline cursor-pointer">
                                    Hapus
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Input Unggah File Baru --}}
            <div class="rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50/50 p-5 text-center transition-colors hover:border-slate-300">
                <input type="file" 
                       wire:model="photos" 
                       multiple 
                       accept="image/jpeg,image/png,image/webp" 
                       class="block w-full text-xs text-slate-500 file:mr-3 file:rounded-xl file:border-0 file:bg-slate-900 file:px-4 file:py-2 file:text-xs file:font-bold file:text-white hover:file:bg-slate-800 file:cursor-pointer cursor-pointer">
                
                <div wire:loading wire:target="photos" class="mt-3 text-xs font-semibold text-slate-600 flex items-center justify-center gap-1.5">
                    <svg class="h-4 w-4 animate-spin text-slate-700" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    <span>Mengunggah dan memproses berkas...</span>
                </div>
                
                @error('photos.*')<p class="mt-2 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror

                {{-- Pratinjau Foto Baru yang Dipilih --}}
                @if (count($photos) > 0)
                    <div class="mt-4 flex flex-wrap gap-3 border-t border-slate-200/80 pt-3">
                        @foreach ($photos as $i => $photo)
                            @if ($photo->isPreviewable())
                                <div class="relative rounded-xl border border-slate-200 bg-white p-1 shadow-2xs" wire:key="new-{{ $i }}">
                                    <img src="{{ $photo->temporaryUrl() }}" alt="" class="h-16 w-24 rounded-lg object-cover">
                                    <button type="button" 
                                            wire:click="removeNewPhoto({{ $i }})" 
                                            class="absolute -right-1.5 -top-1.5 flex h-5 w-5 items-center justify-center rounded-full bg-rose-600 text-xs font-bold text-white shadow hover:bg-rose-700 cursor-pointer">&times;</button>
                                </div>
                            @endif
                        @endforeach
                    </div>
                    <p class="mt-2 text-[11px] text-slate-400">Foto baru diunggah saat Anda menekan tombol Simpan. Foto pertama otomatis dijadikan sampul bila belum ada sampul.</p>
                @endif
            </div>
        </div>

        {{-- Tombol Aksi Simpan & Batal --}}
        <div class="flex items-center gap-3 border-t border-slate-100 pt-6">
            <button type="submit" 
                    wire:loading.attr="disabled" 
                    wire:target="save" 
                    class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-6 py-2.5 text-xs font-bold text-white shadow hover:bg-slate-800 transition-all cursor-pointer disabled:opacity-50">
                <span wire:loading.remove wire:target="save">{{ $portfolioId ? 'Perbarui Portofolio' : 'Simpan Portofolio' }}</span>
                <span wire:loading wire:target="save" class="flex items-center gap-1.5">
                    <svg class="h-3.5 w-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    <span>Menyimpan...</span>
                </span>
            </button>
            <a href="{{ route('partner.portfolios.index') }}" 
               class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 transition-all">
                Batal
            </a>
        </div>
    </form>

</div>