<?php

namespace App\Models;

use App\Enums\ListingType;
use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Property extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'partner_id', 'title', 'slug', 'description',
        'property_type', 'listing_type', 'price',
        'bedrooms', 'bathrooms', 'floors', 'land_area', 'building_area', 'certificate',
        'address', 'district', 'city', 'province', 'postal_code', 'latitude', 'longitude',
        'status', 'verified_by', 'verified_at', 'rejection_reason', 'published_at', 'views_count',
    ];

    protected function casts(): array
    {
        return [
            'property_type' => PropertyType::class,
            'listing_type' => ListingType::class,
            'status' => PropertyStatus::class,
            'price' => 'decimal:2',
            'land_area' => 'decimal:2',
            'building_area' => 'decimal:2',
            'latitude' => 'float',
            'longitude' => 'float',
            'verified_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Property $property) {
            if (empty($property->slug)) {
                $property->slug = Str::slug($property->title).'-'.Str::lower(Str::random(6));
            }
        });
    }

    // ---- Relationships ----
    public function partner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function images(): HasMany
    {
        return $this->hasMany(PropertyImage::class)->orderBy('sort_order');
    }

    public function coverImage(): HasOne
    {
        return $this->hasOne(PropertyImage::class)->where('is_cover', true);
    }

    public function facilities(): BelongsToMany
    {
        return $this->belongsToMany(Facility::class);
    }

    // ---- Scopes ----
    /** Tayang = status published DAN pemiliknya masih aktif (mitra disuspend/dihapus -> tersembunyi). */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', PropertyStatus::Published->value)
            ->whereHas('partner', fn (Builder $q) => $q->where('status', 'active'));
    }
}
