<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateUserStatusRequest;
use App\Http\Requests\Admin\UserIndexRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function __construct(private UserService $users)
    {
    }

    public function index(UserIndexRequest $request): JsonResponse
    {
        return ApiResponse::success(
            UserResource::collection($this->users->list($request->validated())),
            'Daftar pengguna.',
        );
    }

    public function updateStatus(UpdateUserStatusRequest $request, User $user): JsonResponse
    {
        return ApiResponse::success(
            UserResource::make($this->users->updateStatus($user, $request->user(), $request->validated('status'))),
            'Status pengguna diperbarui.',
        );
    }
}
