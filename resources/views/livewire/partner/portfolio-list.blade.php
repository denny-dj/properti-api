@php
    $statusLabel = [
        'draft'     => ['Draft', 'bg-slate-100 text-slate-600 border-slate-200'],
        'published' => ['Tayang', 'bg-emerald-50 text-emerald-800 border-emerald-200/80'],
        'archived'  => ['Diarsipkan', 'bg-amber-50 text-amber-800 border-amber-200/80'],
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
                <span class="text-slate-700">Studio &amp; Desain</span>
            </nav>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">Portofolio saya</h1>
            <p class="mt-1 text-xs sm:text-sm text-slate-500">Tampilkan karya lengkap dengan estimasi RAB. Admin kami yang mendampingi calon klien.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('partner.portfolios.create') }}" 
               class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-xs font-bold text-white shadow hover:bg-slate-800 transition-all cursor-pointer">
                <svg class="h-4 w-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Tambah portofolio</span>
            </a>
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

    {{-- Daftar Portofolio Arsitek --}}
    <div class="divide-y divide-slate-100 overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm transition-opacity duration-200"
         wire:loading.class="opacity-60">
        @forelse ($portfolios as $p)
            @php 
                [$text, $classes] = $statusLabel[$p->status->value] ?? [$p->status->value, 'bg-slate-100 text-slate-700 border-slate-200']; 
            @endphp
            <div wire:key="pf-{{ $p->id }}" class="flex flex-col gap-4 p-5 sm:flex-row sm:items-start sm:p-6 transition-colors hover:bg-slate-50/50">
                
                {{-- Foto Sampul Portofolio: Dimensi Terkunci Pasti --}}
                <div class="relative shrink-0 overflow-hidden rounded-2xl bg-slate-100 border border-slate-200/80 shadow-2xs"
                     style="width: 140px; height: 96px; min-width: 140px; max-width: 140px; max-height: 96px;">
                    @if ($p->coverImage)
                        <img src="{{ $p->coverImage->url() }}" 
                             alt="{{ $p->title }}" 
                             class="h-full w-full object-cover transition-transform duration-300 hover:scale-105" 
                             style="width: 100%; height: 100%; object-fit: cover;"
                             onerror="this.remove()">
                    @else
                        <div class="flex h-full w-full items-center justify-center bg-slate-100 text-slate-300">
                            <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    @endif
                </div>

                {{-- Kolom Konten & Detail Portofolio --}}
                <div class="min-w-0 flex-1 space-y-2">
                    <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
                        <span class="rounded-lg border px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ $classes }}">
                            {{ $text }}
                        </span>
                        <span class="rounded-lg border border-blue-200/80 bg-blue-50 px-2 py-0.5 text-[10px] font-semibold text-blue-700">
                            {{ $p->category->label() }}
                        </span>
                        <span class="rounded-lg border border-slate-200 bg-slate-50 px-2 py-0.5 text-[10px] font-semibold text-slate-600">
                            {{ $p->architectural_style->label() }}
                        </span>
                        @if ($p->is_ready_to_build)
                            <span class="inline-flex items-center gap-1 rounded-lg border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-800">
                                <svg class="h-3 w-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span>Siap bangun</span>
                            </span>
                        @endif
                    </div>

                    <h2 class="text-sm sm:text-base font-bold text-slate-900 tracking-tight leading-snug">
                        {{ $p->title }}
                    </h2>

                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-slate-500">
                        <span>RAB <span class="font-mono font-bold text-slate-900"><x-rupiah :value="$p->estimated_rab" /></span></span>
                        <span class="text-slate-300">·</span>
                        <span class="font-mono font-semibold text-slate-700">{{ number_format((float) $p->area_sqm, 0, ',', '.') }} m²</span>
                        @if ($p->is_ready_to_build && $p->blueprint_price)
                            <span class="text-slate-300">·</span>
                            <span>Paket <span class="font-mono font-bold text-emerald-600"><x-rupiah :value="$p->blueprint_price" /></span></span>
                        @endif
                    </div>
                </div>

                {{-- Kolom Tombol Aksi Mandiri --}}
                <div class="flex flex-wrap items-center gap-1.5 pt-2 sm:pt-0 shrink-0 border-t border-slate-100 sm:border-t-0 text-xs">
                    @if ($p->status->value === 'published')
                        <a href="{{ route('portfolios.show', $p->slug) }}" 
                           target="_blank" 
                           class="inline-flex items-center gap-1 rounded-xl border border-slate-200 bg-white px-3 py-1.5 font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 hover:border-slate-300 transition-all">
                            <span>Lihat</span>
                            <svg class="h-3 w-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </a>
                    @endif

                    <a href="{{ route('partner.portfolios.edit', $p->id) }}" 
                       class="inline-flex items-center gap-1 rounded-xl border border-slate-200 bg-white px-3 py-1.5 font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 hover:border-slate-300 transition-all">
                        <svg class="h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        <span>Ubah</span>
                    </a>

                    @if ($p->status->value !== 'published')
                        <button type="button" 
                                wire:click="setStatus({{ $p->id }}, 'published')" 
                                class="inline-flex items-center gap-1 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-1.5 font-bold text-emerald-700 hover:bg-emerald-100 transition-all cursor-pointer">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Tayangkan</span>
                        </button>
                    @else
                        <button type="button" 
                                wire:click="setStatus({{ $p->id }}, 'archived')" 
                                wire:confirm="Arsipkan portofolio ini? Tidak akan tampil di etalase." 
                                class="inline-flex items-center gap-1 rounded-xl border border-amber-200 bg-amber-50 px-3 py-1.5 font-bold text-amber-800 hover:bg-amber-100 transition-all cursor-pointer">
                            <span>Arsipkan</span>
                        </button>
                    @endif

                    <button type="button" 
                            wire:click="delete({{ $p->id }})" 
                            wire:confirm="Hapus portofolio ini?" 
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h3 class="mt-4 text-sm font-bold text-slate-900">Belum ada portofolio.</h3>
                <p class="mt-1 text-xs text-slate-500 max-w-sm mx-auto">Tambahkan karya pertama Anda agar calon klien dapat menemukannya.</p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="pt-2">
        {{ $portfolios->links() }}
    </div>

</div>