<?php

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

// Hanya dua peserta percakapan yang boleh subscribe.
Broadcast::channel('conversations.{conversationId}', function (User $user, int $conversationId) {
    $conversation = Conversation::find($conversationId);

    return $conversation !== null && $conversation->includes($user);
});

// Channel pribadi per-pengguna (badge unread, notifikasi inbox).
Broadcast::channel('users.{id}', fn (User $user, int $id) => $user->id === $id);
