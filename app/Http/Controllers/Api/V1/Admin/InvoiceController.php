<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Concierge\QuickBookingRequest;
use App\Http\Resources\InvoiceResource;
use App\Services\AdminInvoiceService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class InvoiceController extends Controller
{
    public function __construct(private AdminInvoiceService $invoices)
    {
    }

    /** Buat tautan Midtrans Snap instan untuk dikirim ke pembeli lewat WhatsApp. */
    public function quickBooking(QuickBookingRequest $request): JsonResponse
    {
        $result = $this->invoices->createQuickBooking($request->user(), $request->validated());
        $transaction = $result['transaction']->load(['payer', 'payee.partnerProfile', 'lead']);

        $data = InvoiceResource::make($transaction)->resolve($request) + [
            'commission_percent' => $result['commission_percent'],
            'fee_basis' => $result['fee_basis'],
            'wa_message' => $result['wa_message'],
        ];

        return ApiResponse::success(
            $data,
            $result['reused'] ? 'Tagihan aktif sebelumnya digunakan kembali.' : 'Tagihan dibuat. Kirim tautan ke pembeli lewat WhatsApp.',
            $result['reused'] ? 200 : 201,
        );
    }
}
