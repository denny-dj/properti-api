<?php

namespace App\Repositories\Contracts;

use App\Models\Conversation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ConversationRepositoryInterface extends BaseRepositoryInterface
{
    public function findByContext(int $buyerId, int $partnerId, string $contextKey): ?Conversation;

    /** Inbox: percakapan milik user + kolom `unread_count`. */
    public function paginateForUser(int $userId, int $perPage = 15): LengthAwarePaginator;

    public function loadDetail(Conversation $conversation): Conversation;
}
