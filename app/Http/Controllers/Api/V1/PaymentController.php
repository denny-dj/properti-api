<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\CheckoutBookingRequest;
use App\Http\Requests\Payment\CheckoutPropertyRequest;
use App\Http\Resources\TransactionResource;
use App\Services\PaymentService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(private PaymentService $payments)
    {
    }

    /** Buyer: bayar booking fee properti. Respons berisi snap_token / redirect_url. */
    public function propertyBookingFee(CheckoutPropertyRequest $request): JsonResponse
    {
        $transaction = $this->payments->checkoutProperty($request->user(), (int) $request->validated('property_id'));

        return ApiResponse::success(
            TransactionResource::make($transaction),
            'Tagihan siap dibayar.',
            $transaction->wasRecentlyCreated ? 201 : 200,
        );
    }

    /** Buyer: bayar jasa konsultasi arsitek (pesanan harus sudah dikonfirmasi). */
    public function architectBooking(CheckoutBookingRequest $request): JsonResponse
    {
        $transaction = $this->payments->checkoutBooking($request->user(), (int) $request->validated('architect_booking_id'));

        return ApiResponse::success(
            TransactionResource::make($transaction),
            'Tagihan siap dibayar.',
            $transaction->wasRecentlyCreated ? 201 : 200,
        );
    }

    /**
     * Webhook dari payment gateway (publik, tanpa token; keaslian dicek lewat signature).
     * Selalu 200 untuk order tidak dikenal agar gateway tidak mengulang tanpa henti.
     */
    public function notification(Request $request): JsonResponse
    {
        $transaction = $this->payments->handleNotification($request->all());

        return ApiResponse::success(null, $transaction ? 'OK' : 'Ignored');
    }
}
