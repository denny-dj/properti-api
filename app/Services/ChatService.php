<?php

namespace App\Services;

use App\Enums\PropertyStatus;
use App\Events\MessageSent;
use App\Exceptions\ApiException;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Repositories\Contracts\ArchitectServiceRepositoryInterface;
use App\Repositories\Contracts\ConversationRepositoryInterface;
use App\Repositories\Contracts\MessageRepositoryInterface;
use App\Repositories\Contracts\PropertyRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

class ChatService
{
    public function __construct(
        private ConversationRepositoryInterface $conversations,
        private MessageRepositoryInterface $messages,
        private PropertyRepositoryInterface $properties,
        private ArchitectServiceRepositoryInterface $services,
        private UserRepositoryInterface $users,
    ) {
    }

    public function inbox(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return $this->conversations->paginateForUser($user->id, $perPage);
    }

    /** Memulai (atau membuka kembali) percakapan buyer -> partner, opsional dengan pesan pertama. */
    public function start(User $buyer, array $data): Conversation
    {
        [$partnerId, $contextKey, $propertyId, $serviceId] = $this->resolveContext($data);

        if ($partnerId === $buyer->id) {
            throw new ApiException('Anda tidak dapat memulai percakapan dengan diri sendiri.', 422);
        }

        $conversation = $this->conversations->findByContext($buyer->id, $partnerId, $contextKey);

        if (! $conversation) {
            try {
                /** @var Conversation $conversation */
                $conversation = $this->conversations->create([
                    'buyer_id' => $buyer->id,
                    'partner_id' => $partnerId,
                    'context_key' => $contextKey,
                    'property_id' => $propertyId,
                    'architect_service_id' => $serviceId,
                ]);
            } catch (UniqueConstraintViolationException) {
                // Dua request bersamaan: ambil yang sudah dibuat pihak lain.
                $conversation = $this->conversations->findByContext($buyer->id, $partnerId, $contextKey);
            }
        }

        if (! empty($data['message'])) {
            $this->send($conversation, $buyer, $data['message']);
        }

        return $this->conversations->loadDetail($conversation);
    }

    public function messages(Conversation $conversation, int $perPage = 30): CursorPaginator
    {
        return $this->messages->cursorByConversation($conversation->id, $perPage);
    }

    public function send(Conversation $conversation, User $sender, string $body): Message
    {
        $body = trim($body);

        if ($body === '') {
            throw new ApiException('Pesan tidak boleh kosong.', 422);
        }

        /** @var Message $message */
        $message = DB::transaction(function () use ($conversation, $sender, $body) {
            $message = $this->messages->create([
                'conversation_id' => $conversation->id,
                'sender_id' => $sender->id,
                'body' => $body,
            ]);

            $this->conversations->update($conversation, [
                'last_message_at' => now(),
                // pengirim otomatis dianggap sudah membaca sampai pesannya sendiri
                $conversation->lastReadColumnFor($sender->id) => $message->id,
            ]);

            return $message;
        });

        // toOthers(): perangkat pengirim yang sama (X-Socket-ID) tidak menerima gema.
        broadcast(new MessageSent($message))->toOthers();

        return $message;
    }

    public function markRead(Conversation $conversation, User $user): void
    {
        $latestId = $this->messages->latestIdFor($conversation->id);

        if ($latestId !== null) {
            $this->conversations->update($conversation, [
                $conversation->lastReadColumnFor($user->id) => $latestId,
            ]);
        }
    }

    /** @return array{0:int,1:string,2:?int,3:?int} [partnerId, contextKey, propertyId, serviceId] */
    private function resolveContext(array $data): array
    {
        if (! empty($data['property_id'])) {
            $property = $this->properties->find((int) $data['property_id']);

            if (! $property || $property->status !== PropertyStatus::Published) {
                throw new ApiException('Properti tidak tersedia.', 404);
            }

            return [$property->partner_id, 'property:'.$property->id, $property->id, null];
        }

        if (! empty($data['architect_service_id'])) {
            $service = $this->services->findActiveById((int) $data['architect_service_id'])
                ?? throw new ApiException('Layanan arsitek tidak tersedia.', 404);

            return [$service->architect_id, 'service:'.$service->id, null, $service->id];
        }

        $partner = $this->users->find((int) ($data['partner_id'] ?? 0), ['partnerProfile']);

        if (! $partner || ! $partner->isActive() || ! $partner->isPartner() || ! $partner->partnerProfile?->isApproved()) {
            throw new ApiException('Partner tidak ditemukan.', 404);
        }

        return [$partner->id, 'direct', null, null];
    }
}
