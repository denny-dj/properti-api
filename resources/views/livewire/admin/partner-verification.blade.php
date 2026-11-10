@php
    $typeLabels = [
        'seller'    => 'Developer / Agen',
        'architect' => 'Studio Arsitek',
    ];
@endphp

{{-- ROOT ELEMENT TUNGGAL LIVEWIRE 3 --}}
<div class="space-y-6 pb-12">
    
    {{-- Header Editorial --}}
    <div class="flex flex-col justify-between gap-4 border-b border-slate-200/80 pb-6 sm:flex-row sm:items-end">
        <div>
            <nav class="mb-2 flex items-center gap-2 text-xs font-medium text-slate-400">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-blue-600 transition-colors">Pusat Kendali</a>
                <span>/</span>
                <span class="text-slate-700">Verifikasi Mitra</span>
            </nav>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">Verifikasi Mitra</h1>
            <p class="mt-1 text-xs sm:text-sm text-slate-500">Penyaringan legalitas entitas bisnis, sertifikasi studio arsitektur, dan persetujuan hak publikasi listing.</p>
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
                           placeholder="Cari nama usaha, PIC, atau email..." 
                           class="w-full rounded-xl border border-slate-200 bg-slate-50/50 pl-9 pr-3.5 py-2 text-xs text-slate-800 placeholder-slate-400 focus:bg-white focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900 transition-all">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </div>

                {{-- Filter Status Verifikasi --}}
                <div class="relative w-full sm:w-auto">
                    <select wire:model.live="status" 
                            class="w-full sm:w-auto appearance-none rounded-xl border border-slate-200 bg-slate-50/50 py-2 pl-3.5 pr-8 text-xs font-semibold text-slate-700 shadow-sm focus:bg-white focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900 transition-all cursor-pointer">
                        <option value="pending">Menunggu verifikasi</option>
                        <option value="approved">Terverifikasi</option>
                        <option value="rejected">Ditolak</option>
                        <option value="">Semua status</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-slate-400">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>

                {{-- Filter Jenis Mitra --}}
                <div class="relative w-full sm:w-auto">
                    <select wire:model.live="type" 
                            class="w-full sm:w-auto appearance-none rounded-xl border border-slate-200 bg-slate-50/50 py-2 pl-3.5 pr-8 text-xs font-semibold text-slate-700 shadow-sm focus:bg-white focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900 transition-all cursor-pointer">
                        <option value="">Semua jenis mitra</option>
                        <option value="seller">Developer / Agen</option>
                        <option value="architect">Arsitek</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-slate-400">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Indikator Reaktif --}}
            <div wire:loading class="text-xs font-medium text-slate-400 flex items-center gap-1.5 self-start sm:self-auto">
                <svg class="h-3.5 w-3.5 animate-spin text-slate-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                <span>Menyinkronkan data...</span>
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

    {{-- Daftar Pengajuan Kemitraan --}}
    <div class="divide-y divide-slate-100 overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm transition-opacity duration-200" 
         wire:loading.class="opacity-60">
        @forelse ($profiles as $p)
            @php
                $initial = strtoupper(mb_substr($p->business_name, 0, 1));
                $isArch = $p->partner_type->value === 'architect';
            @endphp
            <div wire:key="pp-{{ $p->id }}" class="space-y-4 p-5 sm:p-6 transition-colors hover:bg-slate-50/50">
                
                {{-- Header Pengajuan & Aksi Cepat --}}
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="flex min-w-0 flex-1 items-start gap-3.5">
                        
                        {{-- Avatar Inisial Usaha --}}
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-slate-900 text-sm font-extrabold text-white shadow-2xs">
                            {{ $initial }}
                        </div>

                        {{-- Metadata Entitas Bisnis --}}
                        <div class="min-w-0 flex-1 space-y-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <x-status-badge :status="$p->verification_status" context="verification" />
                                <span class="rounded-lg border px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ $isArch ? 'border-purple-200 bg-purple-50 text-purple-700' : 'border-blue-200 bg-blue-50 text-blue-700' }}">
                                    {{ $typeLabels[$p->partner_type->value] ?? $p->partner_type->value }}
                                </span>
                                <h2 class="truncate text-sm font-bold text-slate-900">{{ $p->business_name }}</h2>
                            </div>

                            <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-slate-500">
                                <span class="font-medium text-slate-700">PIC: {{ $p->user?->name ?? 'Anonim' }}</span>
                                <span class="text-slate-300">·</span>
                                <span>{{ $p->user?->email }}</span>
                                @if ($p->user?->phone)
                                    <span class="text-slate-300">·</span>
                                    <span class="font-mono">{{ $p->user->phone }}</span>
                                @endif
                            </div>

                            <p class="font-mono text-[11px] text-slate-400">Pendaftaran: {{ $p->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>

                    {{-- Tombol Aksi Administrator --}}
                    <div class="flex flex-wrap items-center gap-2 pt-1 sm:pt-0 shrink-0 text-xs">
                        {{-- Tombol Detail / Sembunyikan --}}
                        <button type="button" 
                                wire:click="toggle({{ $p->id }})" 
                                class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-1.5 font-semibold text-slate-700 shadow-sm hover:bg-slate-50 hover:border-slate-300 transition-all cursor-pointer">
                            <svg class="h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $expandedId === $p->id ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7' }}"/>
                            </svg>
                            <span>{{ $expandedId === $p->id ? 'Tutup Detail' : 'Tinjau Berkas' }}</span>
                        </button>

                        {{-- Tombol Setujui --}}
                        @if ($p->verification_status->value !== 'approved')
                            <button type="button" 
                                    wire:click="approve({{ $p->id }})" 
                                    wire:confirm="Verifikasi mitra ini dan aktifkan hak terbit listing?" 
                                    class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-3.5 py-1.5 font-bold text-white shadow hover:bg-emerald-700 transition-all cursor-pointer">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>Setujui</span>
                            </button>
                        @endif

                        {{-- Tombol Tolak --}}
                        @if ($p->verification_status->value !== 'rejected')
                            <button type="button" 
                                    wire:click="startReject({{ $p->id }})" 
                                    class="inline-flex items-center gap-1.5 rounded-xl border border-rose-200 bg-rose-50/70 px-3 py-1.5 font-semibold text-rose-700 hover:bg-rose-100 transition-all cursor-pointer">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                <span>Tolak</span>
                            </button>
                        @endif
                    </div>
                </div>

                {{-- Catatan Penolakan Sebelumnya --}}
                @if ($p->verification_status->value === 'rejected' && $p->rejection_reason)
                    <div class="rounded-2xl border border-rose-200 bg-rose-50/70 px-4 py-3 text-xs text-rose-800 flex items-start gap-2.5">
                        <svg class="h-4 w-4 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <strong class="font-bold">Catatan Penolakan Sebelumnya:</strong> {{ $p->rejection_reason }}
                        </div>
                    </div>
                @endif

                {{-- LEMBAR TINJAU DOKUMEN & LEGALITAS MITRA --}}
                @if ($expandedId === $p->id)
                    <div class="rounded-3xl border border-slate-200/90 bg-slate-50/60 p-5 sm:p-6 space-y-5">
                        <div class="border-b border-slate-200/70 pb-3">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Berkas Legalitas &amp; Portofolio Usaha</h3>
                        </div>

                        <dl class="grid gap-4 sm:grid-cols-2 text-xs">
                            <div class="rounded-2xl border border-slate-200/80 bg-white p-3.5 shadow-2xs">
                                <dt class="font-bold uppercase tracking-wider text-[10px] text-slate-400">Nomor Izin Usaha / Keanggotaan Asosiasi</dt>
                                <dd class="mt-1 font-mono font-semibold text-slate-900">{{ $p->license_number ?: 'Tidak dilampirkan' }}</dd>
                            </div>

                            <div class="rounded-2xl border border-slate-200/80 bg-white p-3.5 shadow-2xs">
                                <dt class="font-bold uppercase tracking-wider text-[10px] text-slate-400">Telepon Operasional Kantor</dt>
                                <dd class="mt-1 font-mono font-semibold text-slate-900">{{ $p->business_phone ?: '-' }}</dd>
                            </div>

                            <div class="rounded-2xl border border-slate-200/80 bg-white p-3.5 shadow-2xs">
                                <dt class="font-bold uppercase tracking-wider text-[10px] text-slate-400">Kota Domisili</dt>
                                <dd class="mt-1 font-semibold text-slate-900">{{ $p->city ?: '-' }}</dd>
                            </div>

                            <div class="rounded-2xl border border-slate-200/80 bg-white p-3.5 shadow-2xs">
                                <dt class="font-bold uppercase tracking-wider text-[10px] text-slate-400">Alamat Kantor / Studio</dt>
                                <dd class="mt-1 font-semibold text-slate-900 leading-relaxed">{{ $p->address ?: '-' }}</dd>
                            </div>

                            <div class="sm:col-span-2 rounded-2xl border border-slate-200/80 bg-white p-3.5 shadow-2xs">
                                <dt class="font-bold uppercase tracking-wider text-[10px] text-slate-400">Tautan Portofolio / Situs Resmi</dt>
                                <dd class="mt-1 font-semibold">
                                    @if ($p->portfolio_url)
                                        <a href="{{ $p->portfolio_url }}" 
                                           target="_blank" 
                                           rel="noopener nofollow" 
                                           class="inline-flex items-center gap-1.5 text-blue-600 hover:text-blue-700 hover:underline">
                                            <span>{{ $p->portfolio_url }}</span>
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                        </a>
                                    @else
                                        <span class="text-slate-400 font-normal">Tidak ada tautan yang dilampirkan.</span>
                                    @endif
                                </dd>
                            </div>

                            <div class="sm:col-span-2 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-2xs">
                                <dt class="font-bold uppercase tracking-wider text-[10px] text-slate-400 mb-1">Profil &amp; Rekam Jejak Usaha</dt>
                                <dd class="whitespace-pre-line text-slate-700 leading-relaxed">{{ $p->description ?: 'Tidak ada deskripsi singkat usaha.' }}</dd>
                            </div>
                        </dl>
                    </div>
                @endif

                {{-- FORM ALASAN PENOLAKAN MITRA --}}
                @if ($rejectingId === $p->id)
                    <form wire:submit="reject" class="space-y-3 rounded-3xl border border-rose-200 bg-rose-50/70 p-5 shadow-sm">
                        <div class="flex items-center gap-2">
                            <svg class="h-4 w-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <label class="block text-xs font-bold text-rose-950 uppercase tracking-wider">
                                Catatan Alasan Penolakan Akun Mitra (Akan Dilihat oleh Mitra)
                            </label>
                        </div>
                        <textarea rows="2.5" 
                                  wire:model="reason" 
                                  class="w-full rounded-xl border border-rose-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500 shadow-2xs" 
                                  placeholder="mis. Nomor izin asosiasi arsitek tidak valid, portofolio tidak dapat diakses, dokumen identitas belum lengkap..."></textarea>
                        @error('reason')<p class="text-xs font-semibold text-rose-700">{{ $message }}</p>@enderror
                        
                        <div class="flex items-center gap-2 pt-1">
                            <button type="submit" 
                                    class="inline-flex items-center gap-1.5 rounded-xl bg-rose-600 px-4 py-2 text-xs font-bold text-white shadow hover:bg-rose-700 transition-all cursor-pointer">
                                <span>Kirim Penolakan</span>
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <h3 class="mt-4 text-sm font-bold text-slate-900">Tidak ada pendaftaran mitra pada filter ini.</h3>
                <p class="mt-1 text-xs text-slate-500 max-w-sm mx-auto">Semua pengajuan akun kemitraan telah selesai diverifikasi atau tidak ada data yang cocok dengan kriteria pencarian.</p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="pt-2">
        {{ $profiles->links() }}
    </div>

</div>