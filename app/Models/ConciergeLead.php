<?php

namespace App\Models;

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Enums\PaymentPreference;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConciergeLead extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_code', 'source', 'reference_id', 'customer_name', 'customer_phone',
        'estimated_budget', 'payment_preference', 'status', 'notes', 'handled_by',
    ];

    protected function casts(): array
    {
        return [
            'source' => LeadSource::class,
            'payment_preference' => PaymentPreference::class,
            'status' => LeadStatus::class,
            'estimated_budget' => 'integer',
        ];
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'lead_id');
    }
}
