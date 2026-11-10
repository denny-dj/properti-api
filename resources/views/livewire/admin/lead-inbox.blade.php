@php
    $statusLabels = [
        'new'       => ['Baru', 'bg-emerald-50 text-emerald-800 border-emerald-200/80'],
        'contacted' => ['Dihubungi', 'bg-blue-50 text-blue-800 border-blue-200/80'],
        'surveying' => ['Survei', 'bg-indigo-50 text-indigo-800 border-indigo-200/80'],
        'committed' => ['Sepakat', 'bg-teal-50 text-teal-800 border-teal-200/80'],
        'lost'      => ['Batal', 'bg-slate-100 text-slate-600 border-slate-200/80'],
    ];
    $sourceLabels = [
        'property_detail'     => 'Detail properti',
        'architect_portfolio' => 'Portofolio arsitek',
        'kpr_calculator'      => 'Kalkulator KPR',
        'promotion'           => 'Banner promo',
    ];
    $input = 'w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900 shadow-2xs transition-all';
@endphp

{{-- ROOT ELEMENT TUNGGAL LIVEWIRE 3 --}}
<div class="space-y-6 pb-12">
    
    {{-- Header Editorial --}}
    <div class="flex flex-col justify-between gap-4 border-b border-slate-200/80 pb-6 sm:flex-row sm:items-end">
        <div>
            <nav class="mb-2 flex items-center gap-2 text-xs font-medium text-slate-400">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-blue-600 transition-colors">Pusat Kendali</a>
                <span>/</span>
                <span class="text-slate-700">Prospek Concierge</span>
            </nav>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">Prospek</h1>
            <p class="mt-1 text-xs sm:text-sm text-slate-500">Semua minat yang masuk lewat tombol WhatsApp dan form KPR. Hubungi, catat hasilnya, lalu buat invoice saat pembeli sepakat.</p>
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
                           placeholder="Cari kode, nama, nomor HP" 
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
                        @foreach ($statusLabels as $value => [$label])
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-slate-400">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>

                {{-- Filter Sumber --}}
                <div class="relative w-full sm:w-auto">
                    <select wire:model.live="source" 
                            class="w-full sm:w-auto appearance-none rounded-xl border border-slate-200 bg-slate-50/50 py-2 pl-3.5 pr-8 text-xs font-semibold text-slate-700 shadow-sm focus:bg-white focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900 transition-all cursor-pointer">
                        <option value="">Semua sumber</option>
                        @foreach ($sourceLabels as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-slate-400">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
            </div>

            {{-- Indikator Reaktif --}}
            <div wire:loading class="text-xs font-medium text-slate-400 flex items-center gap-1.5 self-start sm:self-auto">
                <svg class="h-3.5 w-3.5 animate-spin text-slate-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                <span>Memperbarui prospek...</span>
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

    {{-- Daftar Prospek Concierge --}}
    <div class="divide-y divide-slate-100 overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm transition-opacity duration-200" 
         wire:loading.class="opacity-60">
        @forelse ($leads as $lead)
            @php
                [$label, $classes] = $statusLabels[$lead->status->value] ?? [$lead->status->value, 'bg-slate-100 text-slate-700 border-slate-200'];
                $ref = $refs[\App\Services\ConciergeLeadService::referenceKey($lead) ?? ''] ?? null;
            @endphp
            <div wire:key="lead-{{ $lead->id }}" class="space-y-4 p-5 sm:p-6 transition-colors hover:bg-slate-50/50">
                
                {{-- Baris Utama Prospek & Aksi Cepat --}}
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0 flex-1 space-y-1.5">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="font-mono rounded-md bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-700">
                                {{ $lead->lead_code }}
                            </span>
                            <span class="rounded-lg border px-2.5 py-0.5 text-[10px] font-bold {{ $classes }}">
                                {{ $label }}
                            </span>
                            <span class="rounded-md bg-slate-50 px-2 py-0.5 text-[10px] font-semibold text-slate-500 border border-slate-200/60">
                                {{ $sourceLabels[$lead->source->value] ?? $lead->source->value }}
                            </span>
                        </div>

                        <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                            <span>{{ $lead->customer_name ?: 'Tanpa nama' }}</span>
                            @if ($lead->customer_phone)
                                <span class="font-mono font-normal text-xs text-slate-500">· {{ $lead->customer_phone }}</span>
                            @endif
                        </h2>

                        <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-slate-500">
                            @if ($ref)
                                @if ($ref['url'])
                                    <a href="{{ $ref['url'] }}" target="_blank" class="font-medium text-blue-600 hover:text-blue-700 hover:underline">
                                        {{ $ref['title'] }}
                                    </a>
                                @else
                                    <span class="text-slate-400">{{ $ref['title'] }} (sudah tidak tayang)</span>
                                @endif
                                <span class="text-slate-300">·</span>
                            @endif

                            @if ($lead->estimated_budget)
                                <span>Anggaran <span class="font-mono font-bold text-slate-900"><x-rupiah :value="$lead->estimated_budget" /></span></span>
                                <span class="text-slate-300">·</span>
                            @endif

                            @if ($lead->payment_preference)
                                <span class="rounded-md bg-slate-100 px-1.5 py-0.5 text-[11px] font-medium text-slate-600">
                                    {{ $lead->payment_preference->label() }}
                                </span>
                                <span class="text-slate-300">·</span>
                            @endif

                            <span class="font-mono text-slate-400">{{ $lead->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>

                    {{-- Tombol Aksi --}}
                    <div class="flex flex-wrap items-center gap-2 pt-2 sm:pt-0 shrink-0 border-t border-slate-100 sm:border-t-0 text-xs">
                        @if ($lead->customer_phone)
                            <a href="https://wa.me/{{ $lead->customer_phone }}" 
                               target="_blank" 
                               rel="noopener" 
                               class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-3.5 py-1.5 font-bold text-white shadow hover:bg-emerald-700 transition-all">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                <span>Chat WhatsApp</span>
                            </a>
                        @endif

                        @if ($lead->status->value !== 'lost')
                            <a href="{{ route('admin.invoices', ['lead' => $lead->lead_code]) }}" 
                               class="inline-flex items-center gap-1.5 rounded-xl border border-blue-200 bg-blue-50/70 px-3.5 py-1.5 font-semibold text-blue-700 hover:bg-blue-100 transition-all">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <span>Buat invoice</span>
                            </a>
                        @endif

                        <button type="button" 
                                wire:click="startEdit({{ $lead->id }}, '{{ $lead->status->value }}')" 
                                class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-1.5 font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 hover:border-slate-300 transition-all cursor-pointer">
                            <svg class="h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            <span>Ubah status</span>
                        </button>
                    </div>
                </div>

                {{-- Catatan Internal & Penangan --}}
                @if ($lead->notes)
                    <div class="rounded-2xl border border-slate-200/80 bg-slate-50/70 p-3.5 text-xs text-slate-700 leading-relaxed whitespace-pre-line">
                        {{ $lead->notes }}
                    </div>
                @endif

                @if ($lead->handler)
                    <p class="font-mono text-[11px] text-slate-400 flex items-center gap-1.5">
                        <svg class="h-3 w-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <span>Ditangani {{ $lead->handler->name }}</span>
                    </p>
                @endif

                {{-- FORMULIR INLINE UBAH STATUS PROSPEK --}}
                @if ($editing === $lead->id)
                    <div class="space-y-4 rounded-3xl border border-blue-200 bg-blue-50/60 p-5 shadow-sm">
                        <div class="border-b border-blue-100 pb-2">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-blue-950">Pembaruan Tahap Konsultasi Prospek</h4>
                        </div>
                        
                        <div class="grid gap-3 sm:grid-cols-3">
                            <div class="space-y-1.5">
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">Status baru</label>
                                <div class="relative">
                                    <select wire:model="newStatus" class="{{ $input }} appearance-none pr-8 cursor-pointer">
                                        <option value="contacted">Dihubungi</option>
                                        <option value="surveying">Survei</option>
                                        <option value="committed">Sepakat</option>
                                        <option value="lost">Batal</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </div>
                                </div>
                                @error('newStatus')<p class="text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                            </div>

                            <div class="sm:col-span-2 space-y-1.5">
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">Catatan (opsional)</label>
                                <textarea rows="2" wire:model="newNote" class="{{ $input }}" placeholder="mis. Survei Sabtu 10.00, minat paket custom"></textarea>
                                @error('newNote')<p class="text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="flex items-center gap-2 pt-1">
                            <button type="button" 
                                    wire:click="saveStatus" 
                                    class="inline-flex items-center rounded-xl bg-blue-600 px-4 py-2 text-xs font-bold text-white shadow hover:bg-blue-700 transition-all cursor-pointer">
                                <span>Simpan</span>
                            </button>
                            <button type="button" 
                                    wire:click="cancelEdit" 
                                    class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 transition-all cursor-pointer">
                                <span>Batal</span>
                            </button>
                        </div>
                    </div>
                @endif

            </div>
        @empty
            <div class="p-12 text-center">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"/>
                    </svg>
                </div>
                <h3 class="mt-4 text-sm font-bold text-slate-900">Belum ada prospek pada filter ini.</h3>
                <p class="mt-1 text-xs text-slate-500 max-w-sm mx-auto">Setiap pengajuan baru lewat tombol WhatsApp, formulir konsultasi, dan kalkulator simulasi KPR akan langsung masuk ke sini.</p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="pt-2">
        {{ $leads->links() }}
    </div>

</div>