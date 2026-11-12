<?php

namespace App\Services;

use App\Enums\VerificationStatus;
use App\Exceptions\ApiException;
use App\Models\User;
use App\Support\ImageUploader;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Throwable;

/** Pengaturan profil akun, profil bisnis mitra, dan kata sandi. */
class ProfileService
{
    public function __construct(private ImageUploader $images)
    {
    }

    /** @return array{email_changed: bool} */
    public function updateAccount(User $user, array $data, ?UploadedFile $avatar = null): array
    {
        $email = mb_strtolower(trim($data['email']));
        $emailChanged = $email !== mb_strtolower((string) $user->email);
        $oldAvatar = $user->avatar;
        $newAvatar = null;

        try {
            DB::transaction(function () use ($user, $data, $email, $emailChanged, $avatar, &$newAvatar) {
                $payload = [
                    'name' => trim($data['name']),
                    'email' => $email,
                    'phone' => trim($data['phone']),
                ];

                if ($emailChanged) {
                    $payload['email_verified_at'] = null; // alamat baru wajib diverifikasi ulang
                }

                if ($avatar) {
                    $newAvatar = $this->store(fn () => $this->images->storeSquare($avatar, 'avatars', 512));
                    $payload['avatar'] = $newAvatar;
                }

                $user->forceFill($payload)->save();
            });
        } catch (Throwable $e) {
            $this->images->delete($newAvatar); // jangan tinggalkan berkas yatim bila penyimpanan gagal

            throw $e;
        }

        if ($newAvatar) {
            $this->images->delete($oldAvatar);
        }

        if ($emailChanged) {
            try {
                $user->sendEmailVerificationNotification();
            } catch (Throwable $e) {
                report($e);
            }
        }

        Log::info('profile.account_updated', ['user_id' => $user->id, 'email_changed' => $emailChanged, 'avatar_changed' => (bool) $newAvatar]);

        return ['email_changed' => $emailChanged];
    }

    public function removeAvatar(User $user): void
    {
        $old = $user->avatar;

        $user->forceFill(['avatar' => null])->save();
        $this->images->delete($old);
    }

    /**
     * @return array{reverify: bool}
     */
    public function updatePartnerProfile(User $user, array $data, ?UploadedFile $logo = null): array
    {
        $profile = $user->partnerProfile ?? throw new ApiException('Profil bisnis tidak ditemukan.', 403);

        $payload = [
            'business_name' => trim($data['business_name']),
            'business_phone' => $this->nullable($data['business_phone'] ?? null),
            'city' => $this->nullable($data['city'] ?? null),
            'address' => $this->nullable($data['address'] ?? null),
            'description' => $this->nullable($data['description'] ?? null),
            'license_number' => $this->nullable($data['license_number'] ?? null),
            'portfolio_url' => $this->nullable($data['portfolio_url'] ?? null),
        ];

        $before = $profile->only(['business_name', 'license_number']);
        $identityChanged = $before['business_name'] !== $payload['business_name'] || $before['license_number'] !== $payload['license_number'];
        $reverify = $identityChanged && $profile->isApproved() && config('concierge.partner_reverify_on_identity_change', false);

        $oldLogo = $profile->logo;
        $newLogo = null;

        try {
            DB::transaction(function () use ($profile, $payload, $logo, $reverify, &$newLogo) {
                if ($logo) {
                    $newLogo = $this->store(fn () => $this->images->storeContained($logo, 'logos', 600, 600));
                    $payload['logo'] = $newLogo;
                }

                if ($reverify) {
                    $payload['verification_status'] = VerificationStatus::Pending;
                    $payload['verified_at'] = null;
                }

                $profile->forceFill($payload)->save();
            });
        } catch (Throwable $e) {
            $this->images->delete($newLogo);

            throw $e;
        }

        if ($newLogo) {
            $this->images->delete($oldLogo);
        }

        // Jejak audit: nama usaha & nomor izin menentukan identitas mitra yang diverifikasi admin.
        Log::info('partner.profile_updated', [
            'user_id' => $user->id,
            'identity_changed' => $identityChanged,
            'previous' => $identityChanged ? $before : null,
            'reverify' => $reverify,
        ]);

        return ['reverify' => $reverify];
    }

    public function removeLogo(User $user): void
    {
        $profile = $user->partnerProfile ?? throw new ApiException('Profil bisnis tidak ditemukan.', 403);
        $old = $profile->logo;

        $profile->forceFill(['logo' => null])->save();
        $this->images->delete($old);
    }

    public function changePassword(User $user, string $current, string $new): void
    {
        if (! Hash::check($current, (string) $user->password)) {
            throw new ApiException('Kata sandi saat ini salah.', 422);
        }

        $user->forceFill(['password' => $new, 'remember_token' => Str::random(60)])->save(); // cast "hashed" menghash otomatis
        $user->tokens()->delete(); // token API lama dicabut

        Log::info('profile.password_changed', ['user_id' => $user->id]);
    }

    // ------------------------------------------------------------------

    private function store(callable $writer): string
    {
        try {
            return $writer();
        } catch (InvalidArgumentException $e) {
            throw new ApiException($e->getMessage(), 422);
        }
    }

    private function nullable(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
