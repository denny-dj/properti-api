@php
    $roleLabels = [
        'user'    => 'Pembeli',
        'partner' => 'Mitra',
        'admin'   => 'Admin',
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
                <span class="text-slate-700">Manajemen Pengguna</span>
            </nav>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">Pengguna</h1>
            <p class="mt-1 text-xs sm:text-sm text-slate-500">Direktori basis akun terdaftar, klasifikasi hak akses multi-peran, dan penangguhan akses akun.</p>
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
                           placeholder="Cari nama, email, HP" 
                           class="w-full rounded-xl border border-slate-200 bg-slate-50/50 pl-9 pr-3.5 py-2 text-xs text-slate-800 placeholder-slate-400 focus:bg-white focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900 transition-all">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </div>

                {{-- Filter Peran Akun --}}
                <div class="relative w-full sm:w-auto">
                    <select wire:model.live="role" 
                            class="w-full sm:w-auto appearance-none rounded-xl border border-slate-200 bg-slate-50/50 py-2 pl-3.5 pr-8 text-xs font-semibold text-slate-700 shadow-sm focus:bg-white focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900 transition-all cursor-pointer">
                        <option value="">Semua peran</option>
                        <option value="user">Pembeli</option>
                        <option value="partner">Mitra</option>
                        <option value="admin">Admin</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-slate-400">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>

                {{-- Filter Status Akun --}}
                <div class="relative w-full sm:w-auto">
                    <select wire:model.live="status" 
                            class="w-full sm:w-auto appearance-none rounded-xl border border-slate-200 bg-slate-50/50 py-2 pl-3.5 pr-8 text-xs font-semibold text-slate-700 shadow-sm focus:bg-white focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900 transition-all cursor-pointer">
                        <option value="">Semua status</option>
                        <option value="active">Aktif</option>
                        <option value="suspended">Disuspend</option>
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
                <span>Menyinkronkan pengguna...</span>
            </div>
        </div>
    </div>

    {{-- Feedback Notifikasi & Error --}}
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

    {{-- Daftar Pengguna Terdaftar --}}
    <div class="divide-y divide-slate-100 overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm transition-opacity duration-200" 
         wire:loading.class="opacity-60">
        @forelse ($users as $u)
            @php 
                $isAdminUser = $u->roles->contains('name', 'admin'); 
                $initial = strtoupper(mb_substr($u->name, 0, 1));
            @endphp
            <div wire:key="usr-{{ $u->id }}" class="flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6 transition-colors hover:bg-slate-50/50">
                <div class="flex min-w-0 flex-1 items-start gap-3.5">
                    
                    {{-- Avatar Inisial --}}
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-slate-900 text-xs font-extrabold text-white shadow-2xs">
                        {{ $initial }}
                    </div>

                    <div class="min-w-0 flex-1 space-y-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="truncate text-sm font-bold text-slate-900">{{ $u->name }}</h2>
                            <x-status-badge :status="$u->status" context="user" />
                            @foreach ($u->roles as $r)
                                <span class="rounded-lg border border-slate-200 bg-slate-50 px-2 py-0.5 text-[10px] font-bold text-slate-700">
                                    {{ $roleLabels[$r->name] ?? $r->name }}
                                </span>
                            @endforeach
                            @if ($u->partnerProfile)
                                <x-status-badge :status="$u->partnerProfile->verification_status" context="verification" />
                            @endif
                        </div>

                        <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-slate-500">
                            <span>{{ $u->email }}</span>
                            @if ($u->phone)
                                <span class="text-slate-300">·</span>
                                <span class="font-mono text-slate-600">{{ $u->phone }}</span>
                            @endif
                        </div>

                        <p class="font-mono text-[11px] text-slate-400">Bergabung {{ $u->created_at->format('d/m/Y') }}</p>
                    </div>
                </div>

                {{-- Tombol Aksi Suspend / Aktifkan --}}
                <div class="flex items-center gap-2 pt-2 sm:pt-0 shrink-0 border-t border-slate-100 sm:border-t-0">
                    @if ($isAdminUser || $u->id === auth()->id())
                        <span class="inline-flex items-center rounded-xl border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-400">
                            Tidak dapat diubah
                        </span>
                    @elseif ($u->status === 'active')
                        <button type="button" 
                                wire:click="suspend({{ $u->id }})" 
                                wire:confirm="Suspend akun {{ $u->name }}? Seluruh sesinya akan diakhiri dan layanannya disembunyikan." 
                                class="inline-flex items-center gap-1.5 rounded-xl border border-rose-200 bg-rose-50/70 px-3.5 py-1.5 text-xs font-semibold text-rose-700 shadow-2xs hover:bg-rose-100 transition-all cursor-pointer">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                            <span>Suspend</span>
                        </button>
                    @else
                        <button type="button" 
                                wire:click="activate({{ $u->id }})" 
                                class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-3.5 py-1.5 text-xs font-bold text-white shadow hover:bg-emerald-700 transition-all cursor-pointer">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Aktifkan</span>
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <div class="p-12 text-center">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <h3 class="mt-4 text-sm font-bold text-slate-900">Tidak ada pengguna pada filter ini.</h3>
                <p class="mt-1 text-xs text-slate-500 max-w-sm mx-auto">Sesuaikan kata kunci pencarian atau reset filter peran untuk melihat daftar pengguna lainnya.</p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="pt-2">
        {{ $users->links() }}
    </div>

</div>