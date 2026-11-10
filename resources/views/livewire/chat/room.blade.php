<div class="mx-auto max-w-4xl px-4 py-6 sm:px-6" wire:poll.3s.visible>
    
    {{-- Header Ruang Chat --}}
    <div class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm space-y-3">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-3">
            <a href="{{ route('chat.index') }}" 
               class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-slate-900 transition-colors">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Semua Percakapan</span>
            </a>

            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-700 border border-emerald-100">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                    <span>Terenkripsi</span>
                </span>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-lg sm:text-xl font-extrabold tracking-tight text-slate-900">{{ $counterpartName }}</h1>
                @if ($subject)
                    <div class="mt-1 flex items-center gap-1.5 text-xs text-slate-500">
                        <span>Mengenai Unit:</span>
                        <a href="{{ $subject['url'] }}" target="_blank" class="font-bold text-blue-600 hover:text-blue-700 hover:underline inline-flex items-center gap-1">
                            <span>{{ $subject['title'] }}</span>
                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Kontainer Pesan Interaktif --}}
    <div class="mt-4 overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm flex flex-col">
        
        {{-- Area Riwayat Chat --}}
        <div class="h-[58vh] space-y-4 overflow-y-auto p-5 bg-slate-50/40"
             x-data="{ stick: true }"
             x-init="$nextTick(() => { $el.scrollTop = $el.scrollHeight }); new MutationObserver(() => { if (stick) { $el.scrollTop = $el.scrollHeight } }).observe($el, { childList: true, subtree: true })"
             x-on:scroll="stick = ($el.scrollTop + $el.clientHeight) >= ($el.scrollHeight - 40)">
            
            {{-- Tombol Muat Pesan Terdahulu --}}
            @if ($hasMore)
                <div class="text-center py-2">
                    <button type="button" 
                            wire:click="loadOlder" 
                            class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 shadow-2xs hover:bg-slate-50 hover:text-slate-900 transition-all cursor-pointer">
                        <svg class="h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span>Muat riwayat pesan terdahulu</span>
                    </button>
                </div>
            @endif

            @forelse ($messages as $msg)
                @php $mine = $msg->sender_id === auth()->id(); @endphp
                
                <div wire:key="msg-{{ $msg->id }}" class="flex {{ $mine ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-[82%] sm:max-w-[70%] space-y-1">
                        <div class="rounded-2xl px-4 py-2.5 text-xs sm:text-sm shadow-2xs {{ $mine ? 'bg-slate-900 text-white rounded-br-xs' : 'bg-white border border-slate-200/80 text-slate-800 rounded-bl-xs' }}">
                            <p class="whitespace-pre-line break-words leading-relaxed">{{ $msg->body }}</p>
                        </div>
                        <p class="text-[10px] font-mono text-slate-400 {{ $mine ? 'text-right' : 'text-left' }} px-1">
                            {{ $msg->created_at->format('d/m · H:i') }}
                        </p>
                    </div>
                </div>
            @empty
                <div class="flex h-full flex-col items-center justify-center py-12 text-center text-slate-400">
                    <svg class="h-10 w-10 text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    <p class="text-xs">Belum ada pertukaran pesan.</p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Ketik pertanyaan atau penawaran Anda di kolom bawah.</p>
                </div>
            @endforelse
        </div>

        {{-- Form Pengiriman Pesan --}}
        <div class="border-t border-slate-200/80 bg-white p-3.5 sm:p-4">
            <form wire:submit="send" class="flex items-center gap-2">
                <input type="text" 
                       wire:model="body" 
                       maxlength="2000" 
                       autocomplete="off" 
                       placeholder="Ketik pesan Anda di sini..."
                       class="flex-1 rounded-xl border border-slate-200 bg-slate-50/60 px-4 py-2.5 text-xs sm:text-sm text-slate-800 placeholder-slate-400 focus:bg-white focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900 transition-all">
                
                <button type="submit" 
                        wire:loading.attr="disabled"
                        wire:target="send"
                        class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-5 py-2.5 text-xs font-bold text-white shadow hover:bg-slate-800 transition-all cursor-pointer">
                    <svg wire:loading.remove wire:target="send" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    <svg wire:loading wire:target="send" class="h-4 w-4 animate-spin text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    <span class="hidden sm:inline" wire:loading.remove wire:target="send">Kirim</span>
                </button>
            </form>
            @error('body')
                <p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>
            @enderror
        </div>

    </div>

</div>