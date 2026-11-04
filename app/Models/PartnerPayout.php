<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Pencairan saldo mitra yang sudah ditransfer admin (dicatat manual). */
class PartnerPayout extends Model
{
    protected $fillable = ['partner_id', 'amount', 'reference', 'note', 'paid_at', 'recorded_by'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'paid_at' => 'datetime'];
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
