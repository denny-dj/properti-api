<?php

namespace App\Models;

use App\Enums\BannerPlacement;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class AdBanner extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'placement', 'banner_image', 'target_url',
        'impression_count', 'click_count', 'is_active', 'start_date', 'end_date',
    ];

    protected function casts(): array
    {
        return [
            'placement' => BannerPlacement::class,
            'impression_count' => 'integer',
            'click_count' => 'integer',
            'is_active' => 'boolean',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    /** Aktif dan hari ini berada di dalam masa tayang. */
    public function scopeLive(Builder $query): Builder
    {
        $today = now()->toDateString();

        return $query->where('is_active', true)
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today);
    }

    public function imageUrl(): string
    {
        return Storage::disk('public')->url($this->banner_image);
    }
}
