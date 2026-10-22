<div class="mx-auto max-w-3xl px-4 py-8" wire:poll.10s.visible>
    <h1 class="text-2xl font-bold">Chat</h1>

    <div class="mt-5 divide-y divide-gray-100 overflow-hidden rounded-2xl border border-gray-200 bg-white">
        @forelse ($conversations as $c)
            @php
                $isBuyer = $c->buyer_id === auth()->id();
                $name = $isBuyer ? ($c->partner->partnerProfile?->business_name ?? $c->partner->name) : $c->buyer->name;
                $subject = $c->property?->title ?? $c->architectService?->title;
                $unread = (int) ($c->unread_count ?? 0);
            @endphp
            <a href="{{ route('chat.room', $c->id) }}" wire:key="conv-{{ $c->id }}" class="flex items-start justify-between gap-3 px-5 py-4 hover:bg-gray-50">
                <div class="min-w-0">
                    <p class="truncate font-medium text-gray-900">{{ $name }}</p>
                    @if ($subject)<p class="truncate text-xs text-gray-400">{{ $subject }}</p>@endif
                    <p class="mt-1 truncate text-sm {{ $unread > 0 ? 'font-medium text-gray-900' : 'text-gray-500' }}">
                        {{ $c->latestMessage ? \Illuminate\Support\Str::limit($c->latestMessage->body, 70) : 'Belum ada pesan' }}
                    </p>
                </div>
                <div class="shrink-0 text-right">
                    <p class="text-xs text-gray-400">{{ $c->last_message_at?->format('d/m H:i') }}</p>
                    @if ($unread > 0)
                        <span class="mt-1 inline-flex min-w-[1.25rem] justify-center rounded-full bg-blue-600 px-1.5 py-0.5 text-xs font-medium text-white">{{ $unread }}</span>
                    @endif
                </div>
            </a>
        @empty
            <div class="p-10 text-center text-gray-500">
                Belum ada percakapan.
                <a href="{{ route('properties.index') }}" class="font-medium text-blue-700 hover:underline">Cari properti</a> lalu tekan "Chat penjual".
            </div>
        @endforelse
    </div>

    <div class="mt-6">{{ $conversations->links() }}</div>
</div>
