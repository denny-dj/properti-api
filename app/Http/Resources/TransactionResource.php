<?php

namespace App\Http\Resources;

use App\Enums\TransactionStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'code' => $this->code,
            'type' => $this->type->value,
            'status' => $this->status->value,
            'description' => $this->description,
            'amount' => (float) $this->amount,
            'amount_formatted' => 'Rp '.number_format((float) $this->amount, 0, ',', '.'),
            'currency' => $this->currency,
            'payment' => [
                'type' => $this->payment_type,
                'channel' => $this->payment_channel,
                'installment_term' => $this->installment_term,
            ],
            // Data checkout hanya relevan selama tagihan masih menunggu pembayaran
            'checkout' => $this->when($this->status === TransactionStatus::Pending, fn () => [
                'snap_token' => $this->snap_token,
                'redirect_url' => $this->redirect_url,
            ]),
            'property' => $this->whenLoaded('property', fn () => $this->property ? [
                'id' => $this->property->id,
                'slug' => $this->property->slug,
                'title' => $this->property->title,
            ] : null),
            'architect_booking' => $this->whenLoaded('architectBooking', fn () => $this->architectBooking ? [
                'id' => $this->architectBooking->id,
                'code' => $this->architectBooking->code,
            ] : null),
            'payer' => $this->whenLoaded('payer', fn () => ['id' => $this->payer->id, 'name' => $this->payer->name]),
            'payee' => $this->whenLoaded('payee', fn () => $this->payee ? ['id' => $this->payee->id, 'name' => $this->payee->name] : null),
            'needs_review' => $this->when((bool) $request->user()?->can('transactions.view'), $this->needs_review),
            'paid_at' => $this->paid_at?->toIso8601String(),
            'expires_at' => $this->expires_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
