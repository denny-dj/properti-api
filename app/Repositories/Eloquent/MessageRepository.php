<?php

namespace App\Repositories\Eloquent;

use App\Models\Message;
use App\Repositories\Contracts\MessageRepositoryInterface;
use Illuminate\Contracts\Pagination\CursorPaginator;

class MessageRepository extends BaseRepository implements MessageRepositoryInterface
{
    public function __construct(Message $model)
    {
        parent::__construct($model);
    }

    public function cursorByConversation(int $conversationId, int $perPage = 30): CursorPaginator
    {
        return $this->model->newQuery()
            ->where('conversation_id', $conversationId)
            ->orderByDesc('id')
            ->cursorPaginate($perPage);
    }

    public function latestIdFor(int $conversationId): ?int
    {
        $id = $this->model->newQuery()->where('conversation_id', $conversationId)->max('id');

        return $id !== null ? (int) $id : null;
    }
}
