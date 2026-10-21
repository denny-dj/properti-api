<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArchitectAvailability extends Model
{
    use HasFactory;

    protected $fillable = ['architect_id', 'day_of_week', 'start_time', 'end_time'];

    protected function casts(): array
    {
        return ['day_of_week' => 'integer'];
    }

    public function architect(): BelongsTo
    {
        return $this->belongsTo(User::class, 'architect_id');
    }
}
