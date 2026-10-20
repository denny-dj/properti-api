<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterPartnerRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(private AuthService $auth)
    {
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->auth->registerBuyer($request->validated());

        return ApiResponse::success($this->payload($result), 'Registrasi berhasil.', 201);
    }

    public function registerPartner(RegisterPartnerRequest $request): JsonResponse
    {
        $result = $this->auth->registerPartner($request->validated());

        return ApiResponse::success(
            $this->payload($result),
            'Registrasi partner berhasil. Akun menunggu verifikasi administrator.',
            201,
        );
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->auth->login(
            $request->validated('email'),
            $request->validated('password'),
            $request->validated('device_name'),
        );

        return ApiResponse::success($this->payload($result), 'Login berhasil.');
    }

    public function logout(Request $request): JsonResponse
    {
        $this->auth->logout($request->user());

        return ApiResponse::success(null, 'Logout berhasil.');
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load(['roles', 'partnerProfile']);

        return ApiResponse::success(UserResource::make($user), 'Profil pengguna.');
    }

    private function payload(array $result): array
    {
        return [
            'user' => UserResource::make($result['user']),
            'token' => $result['token'],
            'token_type' => 'Bearer',
        ];
    }
}
