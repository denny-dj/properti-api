<?php

namespace App\Livewire\Chat;

use App\Services\ChatService;
use Livewire\Component;
use Livewire\WithPagination;

class Inbox extends Component
{
    use WithPagination;

    public function render()
    {
        return view('livewire.chat.inbox', [
            'conversations' => app(ChatService::class)->inbox(auth()->user(), 15),
        ]);
    }
}
