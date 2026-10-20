<?php

namespace App\Models;

use App\Enums\PartnerType;
use App\Enums\VerificationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartnerProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'partner_type', 'business_name', 'description', 'business_phone',
        'address', 'city', 'license_number', 'portfolio_url',
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

    public function isApproved(): bool
    {
        return $this->verification_status === VerificationStatus::Approved;
    }
}
