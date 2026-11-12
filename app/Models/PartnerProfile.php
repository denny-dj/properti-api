<?php

namespace App\Models;

use App\Enums\PartnerType;
use App\Enums\VerificationStatus;
use App\Support\AvatarGenerator;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PartnerProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'partner_type', 'business_name', 'description', 'business_phone',
        'address', 'city', 'license_number', 'portfolio_url', 'logo',
        'verification_status', 'verified_by', 'verified_at', 'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'partner_type' => PartnerType::class,
            'verification_status' => VerificationStatus::class,
            'verified_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /** URL logo studio/bisnis; tanpa logo dipakai avatar inisial dari nama usaha. */
    protected function logoUrl(): Attribute
    {
        return Attribute::get(function (): string {
            if (blank($this->logo)) {
                return AvatarGenerator::dataUri((string) $this->business_name);
            }

            return str_starts_with($this->logo, 'http') ? $this->logo : Storage::disk('public')->url($this->logo);
        });
    }

    public function hasCustomLogo(): bool
    {
        return filled($this->logo);
    }

    public function isApproved(): bool
    {
        return $this->verification_status === VerificationStatus::Approved;
    }
}
