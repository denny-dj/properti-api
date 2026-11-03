<?php

namespace App\Models;

use App\Enums\ArchitecturalStyle;
use App\Enums\PortfolioCategory;
use App\Enums\PortfolioStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ArchitectPortfolio extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'title', 'slug', 'category', 'architectural_style', 'estimated_rab', 'area_sqm',
        'cost_per_sqm', 'description', 'is_ready_to_build', 'blueprint_price', 'blueprint_deliverables', 'status',
    ];

    protected function casts(): array
    {
        return [
            'category' => PortfolioCategory::class,
            'architectural_style' => ArchitecturalStyle::class,
            'status' => PortfolioStatus::class,
            'estimated_rab' => 'integer',
            'area_sqm' => 'decimal:2',
            'cost_per_sqm' => 'integer',
            'is_ready_to_build' => 'boolean',
            'blueprint_price' => 'integer',
            'blueprint_deliverables' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (ArchitectPortfolio $portfolio) {
            if (empty($portfolio->slug)) {
                $portfolio->slug = Str::slug($portfolio->title).'-'.Str::lower(Str::random(6));
            }
        });
    }

    public function architect(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(PortfolioImage::class, 'portfolio_id')->orderByDesc('is_cover')->orderBy('id');
    }

    public function coverImage(): HasOne
    {
        return $this->hasOne(PortfolioImage::class, 'portfolio_id')->where('is_cover', true);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'portfolio_id');
    }

    /** Tayang = published DAN arsitek pemiliknya masih aktif. */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', PortfolioStatus::Published->value)
            ->whereHas('architect', fn (Builder $q) => $q->where('status', 'active'));
    }
}
