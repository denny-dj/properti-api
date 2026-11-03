<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PortfolioImage extends Model
{
    protected $fillable = ['portfolio_id', 'image_path', 'is_cover', 'caption'];

    protected function casts(): array
    {
        return ['is_cover' => 'boolean'];
    }

    public function portfolio(): BelongsTo
    {
        return $this->belongsTo(ArchitectPortfolio::class, 'portfolio_id');
    }

    public function url(): string
    {
        return Storage::disk('public')->url($this->image_path);
    }
}
