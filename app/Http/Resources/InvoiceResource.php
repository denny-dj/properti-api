<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Invoice cepat untuk admin (memuat komisi dan tautan pembayaran). */
class InvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'code' => $this->code,
            'type' => $this->type->value,
            'description' => $this->description,
            'amount' => (int) round((float) $this->amount),
            'platform_fee' => (int) round((float) $this->platform_fee),
            'net_partner_amount' => (int) round((float) $this->net_partner_amount),
            'status' => $this->status->value,
            'payment_url' => $this->redirect_url,
            'snap_token' => $this->snap_token,
            'expires_at' => $this->expires_at?->toIso8601String(),
            'buyer' => $this->whenLoaded('payer', fn () => [
                'id' => $this->payer->id,
                'name' => $this->payer->name,
                'email' => $this->payer->email,
                'phone' => $this->payer->phone,
            ]),
            'partner' => $this->whenLoaded('payee', fn () => $this->payee ? [
                'id' => $this->payee->id,
                'name' => $this->payee->name,
                'business_name' => $this->payee->partnerProfile?->business_name,
            ] : null),
            'lead_code' => $this->whenLoaded('lead', fn () => $this->lead?->lead_code),
        ];
    }
}
