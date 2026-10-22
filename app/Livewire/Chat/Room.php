<?php

namespace App\Livewire\Chat;

use App\Exceptions\ApiException;
use App\Models\Conversation;
use App\Services\ChatService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;

/** Jendela chat. Pesan baru diambil lewat polling (wire:poll) setiap beberapa detik. */
class Room extends Component
{
    #[Locked]
    public int $conversationId = 0;

    public string $body = '';

    public int $limit = 30;

    public function mount(int $conversation): void
    {
        $this->conversationId = $conversation;
        $this->conversation(); // pastikan pengguna adalah peserta
    }

    private function conversation(): Conversation
    {
        $conversation = Conversation::findOrFail($this->conversationId);
        Gate::authorize('view', $conversation);

        return $conversation;
    }

    public function send(): void
    {
        $this->validate(
            ['body' => ['required', 'string', 'max:2000']],
            ['body.required' => 'Tulis pesan terlebih dahulu.', 'body.max' => 'Pesan terlalu panjang (maksimal 2000 karakter).'],
        );

        try {
            app(ChatService::class)->send($this->conversation(), auth()->user(), $this->body);
        } catch (ApiException $e) {
            $this->addError('body', $e->getMessage());

            return;
        }

        $this->reset('body');
    }

    public function loadOlder(): void
    {
        $this->limit = min($this->limit + 30, 300);
    }

    public function render()
    {
        $conversation = $this->conversation();
        $user = auth()->user();
        $chat = app(ChatService::class);

        $page = $chat->messages($conversation, min(max($this->limit, 1), 300));
        $messages = collect($page->items())->reverse()->values(); // lama -> baru

        $chat->markRead($conversation, $user);

        $conversation->load(['buyer', 'partner.partnerProfile', 'property', 'architectService']);

        $isBuyer = $user->id === $conversation->buyer_id;
        $counterpartName = $isBuyer
            ? ($conversation->partner->partnerProfile?->business_name ?? $conversation->partner->name)
            : $conversation->buyer->name;

        $subject = null;
        if ($conversation->property) {
            $subject = ['title' => $conversation->property->title, 'url' => route('properties.show', $conversation->property->slug)];
        } elseif ($conversation->architectService) {
            $subject = ['title' => $conversation->architectService->title, 'url' => route('architects.show', $conversation->architectService->slug)];
        }

        return view('livewire.chat.room', [
            'messages' => $messages,
            'hasMore' => $page->hasMorePages(),
            'counterpartName' => $counterpartName,
            'subject' => $subject,
        ]);
    }
}
