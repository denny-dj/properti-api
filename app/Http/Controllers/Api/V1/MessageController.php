<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Chat\ListMessagesRequest;
use App\Http\Requests\Chat\SendMessageRequest;
use App\Http\Resources\MessageResource;
use App\Models\Conversation;
use App\Services\ChatService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class MessageController extends Controller
{
    public function __construct(private ChatService $chat)
    {
    }

    /** Riwayat pesan (terbaru dulu), paginasi cursor: ?cursor=...&per_page=30 */
    public function index(ListMessagesRequest $request, Conversation $conversation): JsonResponse
    {
        Gate::authorize('view', $conversation);

        return ApiResponse::success(
            MessageResource::collection(
                $this->chat->messages($conversation, (int) ($request->validated('per_page') ?? 30))
            ),
            'Riwayat pesan.',
        );
    }

    public function store(SendMessageRequest $request, Conversation $conversation): JsonResponse
    {
        Gate::authorize('view', $conversation);

        return ApiResponse::success(
            MessageResource::make($this->chat->send($conversation, $request->user(), $request->validated('body'))),
            'Pesan terkirim.',
            201,
        );
    }
}
