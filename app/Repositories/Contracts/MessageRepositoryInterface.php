<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\CursorPaginator;

interface MessageRepositoryInterface extends BaseRepositoryInterface
{
    /** Terbaru dulu, paginasi cursor (cocok untuk infinite scroll ke atas). */
    public function cursorByConversation(int $conversationId, int $perPage = 30): CursorPaginator;

    public function latestIdFor(int $conversationId): ?int;
}
