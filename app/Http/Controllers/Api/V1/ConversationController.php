<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Chat\StartConversationRequest;
use App\Http\Resources\ConversationResource;
use App\Models\Conversation;
use App\Services\ChatService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ConversationController extends Controller
{
    public function __construct(private ChatService $chat)
    {
    }

    /** Inbox: percakapan saya + jumlah pesan belum dibaca. */
    public function index(Request $request): JsonResponse
    {
        return ApiResponse::success(
            ConversationResource::collection($this->chat->inbox($request->user())),
            'Daftar percakapan.',
        );
    }

    /** Buyer memulai/membuka percakapan (201 bila baru, 200 bila sudah ada). */
    public function store(StartConversationRequest $request): JsonResponse
    {
        $conversation = $this->chat->start($request->user(), $request->validated());

        return ApiResponse::success(
            ConversationResource::make($conversation),
            'Percakapan siap.',
            $conversation->wasRecentlyCreated ? 201 : 200,
        );
    }

    public function read(Request $request, Conversation $conversation): JsonResponse
    {
        Gate::authorize('view', $conversation);

        $this->chat->markRead($conversation, $request->user());

        return ApiResponse::success(null, 'Ditandai sudah dibaca.');
    }
}
