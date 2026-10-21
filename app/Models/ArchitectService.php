<?php

namespace App\Models;

use App\Enums\ServiceType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ArchitectService extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'architect_id', 'title', 'slug', 'description',
        'service_type', 'price', 'duration_minutes', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'service_type' => ServiceType::class,
            'price' => 'decimal:2',
            'duration_minutes' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (ArchitectService $service) {
            if (empty($service->slug)) {
                $service->slug = Str::slug($service->title).'-'.Str::lower(Str::random(6));
            }
        });
    }

    public function architect(): BelongsTo
    {
        return $this->belongsTo(User::class, 'architect_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(ArchitectBooking::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
