<?php

namespace App\Models;

use App\Enums\RoleName;
use App\Support\AvatarGenerator;
use App\Notifications\ResetPasswordNotification;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable, SoftDeletes;

    protected string $guard_name = 'web';

    protected $fillable = [
        'name', 'email', 'password', 'phone', 'avatar', 'status',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function partnerProfile(): HasOne
    {
        return $this->hasOne(PartnerProfile::class);
    }

    /**
     * URL foto profil. Tanpa foto, dipakai avatar inisial berbentuk SVG lokal
     * (tidak mengirim nama pengguna ke layanan pihak ketiga).
     */
    protected function avatarUrl(): Attribute
    {
        return Attribute::get(function (): string {
            $path = $this->avatar;

            if (blank($path)) {
                return AvatarGenerator::dataUri((string) $this->name);
            }

            return str_starts_with($path, 'http') ? $path : Storage::disk('public')->url($path);
        });
    }

    public function hasCustomAvatar(): bool
    {
        return filled($this->avatar);
    }

    /** Properti yang diposting user ini (sebagai partner seller). */
    public function properties(): HasMany
    {
        return $this->hasMany(Property::class, 'partner_id');
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(RoleName::Admin->value);
    }

    public function isPartner(): bool
    {
        return $this->hasRole(RoleName::Partner->value);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /** Email reset password berbahasa Indonesia. */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /** Email verifikasi berbahasa Indonesia. */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmailNotification());
    }
}
