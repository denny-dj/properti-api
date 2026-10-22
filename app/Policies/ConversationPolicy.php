<?php

namespace App\Policies;

use App\Models\Conversation;
use App\Models\User;

class ConversationPolicy
{
    /** Hanya buyer dan partner di percakapan tersebut (admin tidak ikut membaca chat privat). */
    public function view(User $user, Conversation $conversation): bool
    {
        return $conversation->includes($user);
    }
}
