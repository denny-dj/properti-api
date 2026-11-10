<div class="mx-auto max-w-4xl px-4 py-8 sm:px-6" wire:poll.10s.visible>
    
    {{-- Header Editorial --}}
    <div class="flex flex-col justify-between gap-4 border-b border-slate-200/80 pb-6 sm:flex-row sm:items-end">
        <div>
            <nav class="mb-2 flex items-center gap-2 text-xs font-medium text-slate-400">
                <a href="{{ route('home') }}" class="hover:text-blue-600 transition-colors">Beranda</a>
                <span>/</span>
                <span class="text-slate-700">Pesan Langsung</span>
            </nav>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">Kotak Pesan</h1>
            <p class="mt-1 text-xs sm:text-sm text-slate-500">Percakapan terenkripsi seputar unit properti, penawaran harga, dan konsultasi cetak biru arsitektur.</p>
        </div>

        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 shadow-2xs">
                <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Pembaruan otomatis</span>
            </span>
        </div>
    </div>

    {{-- Daftar Percakapan --}}
    <div class="mt-6 divide-y divide-slate-100 overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm">
        @forelse ($conversations as $c)
            @php
                $isBuyer = $c->buyer_id === auth()->id();
                $name = $isBuyer ? ($c->partner->partnerProfile?->business_name ?? $c->partner->name) : $c->buyer->name;
                $subject = $c->property?->title ?? $c->architectService?->title;
                $unread = (int) ($c->unread_count ?? 0);
                $initial = strtoupper(mb_substr($name, 0, 1));
            @endphp
            <a href="{{ route('chat.room', $c->id) }}" 
               wire:key="conv-{{ $c->id }}" 
               class="group flex items-start justify-between gap-4 p-5 transition-colors hover:bg-slate-50/70 {{ $unread > 0 ? 'bg-slate-50/40' : '' }}">
                
                <div class="flex min-w-0 flex-1 items-start gap-3.5">
                    {{-- Avatar Inisial --}}
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-slate-900 text-xs font-extrabold text-white shadow-2xs group-hover:bg-blue-600 transition-colors">
                        {{ $initial }}
                    </div>

                    {{-- Konten Chat --}}
                    <div class="min-w-0 flex-1 space-y-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <p class="truncate text-sm font-bold text-slate-900 group-hover:text-blue-600 transition-colors">{{ $name }}</p>
                            @if ($isBuyer)
                                <span class="rounded-md bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-600">Mitra</span>
                            @endif
                        </div>

                        @if ($subject)
                            <div class="flex items-center gap-1.5 text-xs text-slate-400">
                                <svg class="h-3 w-3 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                <span class="truncate font-medium text-slate-600">{{ $subject }}</span>
                            </div>
                        @endif

                        <p class="truncate text-xs {{ $unread > 0 ? 'font-bold text-slate-900' : 'text-slate-500' }}">
                            {{ $c->latestMessage ? \Illuminate\Support\Str::limit($c->latestMessage->body, 80) : 'Belum ada pesan' }}
                        </p>
                    </div>
                </div>

                {{-- Waktu & Badge Pesan Belum Dibaca --}}
                <div class="shrink-0 text-right space-y-1.5">
                    <p class="font-mono text-[11px] text-slate-400">{{ $c->last_message_at?->format('d/m H:i') }}</p>
                    @if ($unread > 0)
                        <span class="inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-slate-900 px-1.5 text-[10px] font-bold text-white shadow-2xs">
                            {{ $unread }}
                        </span>
                    @endif
                </div>
            </a>
        @empty
            <div class="p-12 text-center">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </div>
                <h3 class="mt-4 text-sm font-bold text-slate-900">Belum ada percakapan.</h3>
                <p class="mt-1 text-xs text-slate-500 max-w-sm mx-auto">Jelajahi listing properti atau katalog desain, lalu tekan tombol "Hubungi Penjual" untuk memulai konsultasi.</p>
                <a href="{{ route('properties.index') }}" 
                   class="mt-4 inline-flex items-center gap-1.5 rounded-xl bg-slate-900 px-4 py-2 text-xs font-semibold text-white shadow hover:bg-slate-800 transition-colors">
                    <span>Cari Properti</span> &rarr;
                </a>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="mt-6">
        {{ $conversations->links() }}
    </div>

</div>