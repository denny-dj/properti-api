<?php

namespace App\Repositories\Eloquent;

use App\Models\Conversation;
use App\Repositories\Contracts\ConversationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ConversationRepository extends BaseRepository implements ConversationRepositoryInterface
{
    private const RELATIONS = ['buyer', 'partner.partnerProfile', 'property', 'architectService', 'latestMessage'];

    public function __construct(Conversation $model)
    {
        parent::__construct($model);
    }

    public function findByContext(int $buyerId, int $partnerId, string $contextKey): ?Conversation
    {
        return $this->model->newQuery()
            ->where('buyer_id', $buyerId)
            ->where('partner_id', $partnerId)
            ->where('context_key', $contextKey)
            ->first();
    }

    public function paginateForUser(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        $lastRead = 'CASE WHEN conversations.buyer_id = ? THEN conversations.buyer_last_read_id ELSE conversations.partner_last_read_id END';

        // Pesan dari lawan bicara dengan id lebih besar dari penanda baca = belum dibaca.
        $unread = DB::table('messages')
            ->selectRaw('count(*)')
            ->whereColumn('messages.conversation_id', 'conversations.id')
            ->where('messages.sender_id', '!=', $userId)
            ->whereRaw("messages.id > COALESCE({$lastRead}, 0)", [$userId]);

        return $this->model->newQuery()
            ->select('conversations.*')
            ->selectSub($unread, 'unread_count')
            ->with(self::RELATIONS)
            ->where(fn (Builder $q) => $q->where('buyer_id', $userId)->orWhere('partner_id', $userId))
            ->orderByRaw('COALESCE(conversations.last_message_at, conversations.created_at) DESC')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function loadDetail(Conversation $conversation): Conversation
    {
        return $conversation->load(self::RELATIONS);
    }
}
