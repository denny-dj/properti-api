<?php

namespace App\Services;

use App\Enums\RoleName;
use App\Enums\VerificationStatus;
use App\Exceptions\ApiException;
use App\Models\User;
use App\Repositories\Contracts\PartnerProfileRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

class AuthService
{
    public function __construct(
        private UserRepositoryInterface $users,
        private PartnerProfileRepositoryInterface $profiles,
    ) {
    }

    /** @return array{user: User, token: string} */
    public function registerBuyer(array $data): array
    {
        $user = DB::transaction(function () use ($data) {
            /** @var User $user */
            $user = $this->users->create(Arr::only($data, ['name', 'email', 'phone', 'password']));
            $user->assignRole(RoleName::User->value);

            return $user;
        });

        return $this->issueToken($user, $data['device_name'] ?? null);
    }

    /** @return array{user: User, token: string} */
    public function registerPartner(array $data): array
    {
        $user = DB::transaction(function () use ($data) {
            /** @var User $user */
            $user = $this->users->create(Arr::only($data, ['name', 'email', 'phone', 'password']));
            $user->assignRole(RoleName::Partner->value);

            $this->profiles->create(array_merge(
                Arr::only($data, [
                    'partner_type', 'business_name', 'description', 'business_phone',
                    'address', 'city', 'license_number', 'portfolio_url',
                ]),
                ['user_id' => $user->id, 'verification_status' => VerificationStatus::Pending],
            ));

            return $user;
        });

        return $this->issueToken($user, $data['device_name'] ?? null);
    }

    /** @return array{user: User, token: string} */
    public function login(string $email, string $password, ?string $deviceName = null): array
    {
        $user = $this->users->findByEmail($email);

        if (! $user || ! Hash::check($password, $user->password)) {
            throw new ApiException('Email atau password salah.', 401);
        }

        if (! $user->isActive()) {
            throw new ApiException('Akun Anda dinonaktifkan. Hubungi administrator.', 403);
        }

        return $this->issueToken($user, $deviceName);
    }

    public function logout(User $user): void
    {
        $token = $user->currentAccessToken();

        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }
    }

    /** @return array{user: User, token: string} */
    private function issueToken(User $user, ?string $deviceName): array
    {
        $user->load(['roles', 'partnerProfile']);

        return [
            'user' => $user,
            'token' => $user->createToken($deviceName ?: 'api')->plainTextToken,
        ];
    }
}
