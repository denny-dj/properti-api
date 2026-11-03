<?php

namespace App\Models;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $fillable = [
        'code', 'user_id', 'payee_id', 'type', 'property_id', 'architect_booking_id',
        'description', 'amount', 'currency', 'status',
        'gateway', 'snap_token', 'redirect_url', 'payment_type', 'payment_channel',
        'installment_term', 'gateway_transaction_id', 'fraud_status',
        'needs_review', 'paid_at', 'expires_at', 'gateway_payload',
        'review_note', 'reviewed_by', 'reviewed_at',
        'platform_fee', 'net_partner_amount', 'deliverable_link', 'deliverable_submitted_at',
        'client_confirmed_at', 'portfolio_id', 'lead_id', 'issued_by',
    ];

    protected function casts(): array
    {
        return [
            'type' => TransactionType::class,
            'status' => TransactionStatus::class,
            'amount' => 'decimal:2',
            'needs_review' => 'boolean',
            'paid_at' => 'datetime',
            'expires_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'deliverable_submitted_at' => 'datetime',
            'client_confirmed_at' => 'datetime',
            'platform_fee' => 'decimal:2',
            'net_partner_amount' => 'decimal:2',
            'gateway_payload' => 'array',
        ];
    }

    /** Route model binding memakai kolom "code" (order_id). */
    public function getRouteKeyName(): string
    {
        return 'code';
    }

    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function payee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payee_id');
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class)->withTrashed();
    }

    public function architectBooking(): BelongsTo
    {
        return $this->belongsTo(ArchitectBooking::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function portfolio(): BelongsTo
    {
        return $this->belongsTo(ArchitectPortfolio::class, 'portfolio_id')->withTrashed();
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(ConciergeLead::class, 'lead_id');
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }
}
