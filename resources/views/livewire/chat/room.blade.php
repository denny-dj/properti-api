<div class="mx-auto max-w-3xl px-4 py-6" wire:poll.3s.visible>
    <a href="{{ route('chat.index') }}" class="text-sm text-blue-700 hover:underline">&larr; Semua percakapan</a>

    <div class="mt-2">
        <h1 class="text-xl font-bold">{{ $counterpartName }}</h1>
        @if ($subject)
            <p class="text-sm text-gray-500">Mengenai: <a href="{{ $subject['url'] }}" class="font-medium text-blue-700 hover:underline">{{ $subject['title'] }}</a></p>
        @endif
    </div>

    <div class="mt-4 overflow-hidden rounded-2xl border border-gray-200 bg-white">
        <div class="h-[60vh] space-y-3 overflow-y-auto p-4"
             x-data="{ stick: true }"
             x-init="$nextTick(() => { $el.scrollTop = $el.scrollHeight }); new MutationObserver(() => { if (stick) { $el.scrollTop = $el.scrollHeight } }).observe($el, { childList: true, subtree: true })"
             x-on:scroll="stick = ($el.scrollTop + $el.clientHeight) >= ($el.scrollHeight - 40)">
            @if ($hasMore)
                <div class="text-center">
                    <button type="button" wire:click="loadOlder" class="text-sm text-blue-700 hover:underline">Muat pesan lebih lama</button>
                </div>
            @endif

            @forelse ($messages as $msg)
                @php $mine = $msg->sender_id === auth()->id(); @endphp
                <div wire:key="msg-{{ $msg->id }}" class="flex {{ $mine ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-[80%] rounded-2xl px-4 py-2 text-sm {{ $mine ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-800' }}">
                        <p class="whitespace-pre-line break-words">{{ $msg->body }}</p>
                        <p class="mt-1 text-[10px] {{ $mine ? 'text-blue-100' : 'text-gray-400' }}">{{ $msg->created_at->format('d/m H:i') }}</p>
                    </div>
                </div>
            @empty
                <p class="py-10 text-center text-sm text-gray-400">Belum ada pesan. Mulai percakapan di bawah.</p>
            @endforelse
        </div>

        <form wire:submit="send" class="flex gap-2 border-t border-gray-100 p-3">
            <input type="text" wire:model="body" maxlength="2000" autocomplete="off" placeholder="Tulis pesan..."
                   class="flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
            <button type="submit" class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-medium text-white hover:bg-blue-700">Kirim</button>
        </form>
        @error('body')
            <p class="px-4 pb-3 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>
