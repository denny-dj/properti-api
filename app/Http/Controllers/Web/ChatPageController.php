<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;

class ChatPageController extends Controller
{
    public function show(Conversation $conversation): View
    {
        Gate::authorize('view', $conversation);

        return view('pages.chat-room', ['conversationId' => $conversation->id]);
    }
}
