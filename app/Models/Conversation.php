<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Conversation extends Model
{
    protected $fillable = [
        'buyer_id', 'partner_id', 'context_key', 'property_id', 'architect_service_id',
        'buyer_last_read_id', 'partner_last_read_id', 'last_message_at',
    ];

    protected function casts(): array
    {
        return ['last_message_at' => 'datetime'];
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function architectService(): BelongsTo
    {
        return $this->belongsTo(ArchitectService::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    public function includes(User $user): bool
    {
        return in_array($user->id, [$this->buyer_id, $this->partner_id], true);
    }

    public function counterpartIdOf(int $userId): int
    {
        return $userId === $this->buyer_id ? $this->partner_id : $this->buyer_id;
    }

    public function lastReadColumnFor(int $userId): string
    {
        return $userId === $this->buyer_id ? 'buyer_last_read_id' : 'partner_last_read_id';
    }
}
