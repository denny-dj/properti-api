<?php

namespace App\Models;

use App\Enums\RoleName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
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
}
