<?php

namespace App\Models;

use App\Enums\BookingStatus;
use App\Enums\MeetingType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArchitectBooking extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'client_id', 'architect_id', 'architect_service_id',
        'scheduled_start', 'scheduled_end', 'meeting_type', 'location', 'notes',
        'price', 'duration_minutes', 'status',
        'confirmed_at', 'completed_at', 'cancelled_at', 'cancelled_by', 'cancel_reason',
    ];

    protected function casts(): array
    {
        return [
            'status' => BookingStatus::class,
            'meeting_type' => MeetingType::class,
            'scheduled_start' => 'datetime',
            'scheduled_end' => 'datetime',
            'confirmed_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'price' => 'decimal:2',
            'duration_minutes' => 'integer',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function architect(): BelongsTo
    {
        return $this->belongsTo(User::class, 'architect_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(ArchitectService::class, 'architect_service_id')->withTrashed();
    }

    public function canceller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }
}
